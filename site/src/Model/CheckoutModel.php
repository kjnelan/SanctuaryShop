<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Mail\MailHelper;

class CheckoutModel extends BaseDatabaseModel
{
    /**
     * Create a pending order from cart data + billing info, return order ID.
     */
    public function processOrder(array $data): int
    {
        $app    = Factory::getApplication();
        $params = ComponentHelper::getParams('com_sanctuaryshop');

        /** @var CartModel $cartModel */
        $cartModel = $this->getMVCFactory()->createModel('Cart', 'Site', ['ignore_request' => true]);
        $cartItems = $cartModel->getItems();

        if (empty($cartItems)) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $subtotal = $cartModel->getSubtotal();
        $taxRate  = (float) $params->get('tax_rate', 0) / 100;
        $tax      = round($subtotal * $taxRate, 2);
        $total    = $subtotal + $tax;

        $db   = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $now  = Factory::getDate()->toSql();

        // Insert order
        $order = (object) [
            'user_id'          => $user->id,
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'shipping'         => 0.00,
            'total'            => $total,
            'currency'         => $params->get('currency', 'USD'),
            'billing_name'     => trim(($data['billing_firstname'] ?? '') . ' ' . ($data['billing_lastname'] ?? '')),
            'billing_email'    => $data['billing_email'] ?? '',
            'billing_address'  => json_encode($data['billing'] ?? []),
            'shipping_address' => json_encode($data['shipping'] ?? []),
            'payment_method'   => 'square',
            'created'          => $now,
            'modified'         => $now,
        ];

        $db->insertObject('#__sanctuaryshop_orders', $order);
        $orderId = (int) $db->insertid();

        // Insert order items
        foreach ($cartItems as $item) {
            $lineItem = (object) [
                'order_id'    => $orderId,
                'product_id'  => $item->product_id,
                'title'       => $item->title,
                'sku'         => $item->sku,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'total_price' => $item->total_price,
            ];
            $db->insertObject('#__sanctuaryshop_order_items', $lineItem);
        }

        return $orderId;
    }

    /**
     * Charge the order via Square Payments API using a payment nonce/sourceId.
     * Returns the Square payment ID on success.
     */
    public function chargeWithSquare(int $orderId, string $sourceId): string
    {
        $params      = ComponentHelper::getParams('com_sanctuaryshop');
        $environment = $params->get('square_environment', 'sandbox');
        $accessToken = $params->get('square_access_token', '');
        $locationId  = $params->get('square_location_id', '');

        if (empty($accessToken) || empty($locationId)) {
            throw new \RuntimeException('Square is not configured. Please set your access token and location ID in component settings.');
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'total', 'currency', 'billing_email', 'billing_name']))
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->where($db->quoteName('id') . ' = ' . $orderId);
        $order = $db->setQuery($query)->loadObject();

        if (!$order) {
            throw new \RuntimeException('Order not found.');
        }

        $baseUrl = $environment === 'production'
            ? 'https://connect.squareup.com'
            : 'https://connect.squareupsandbox.com';

        $amountMoney = (int) round((float) $order->total * 100);
        $idempotencyKey = 'sanctuaryshop-' . $orderId . '-' . time();

        $payload = json_encode([
            'idempotency_key'  => $idempotencyKey,
            'source_id'        => $sourceId,
            'amount_money'     => [
                'amount'   => $amountMoney,
                'currency' => $order->currency,
            ],
            'location_id'      => $locationId,
            'buyer_email_address' => $order->billing_email,
            'note'             => 'Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT),
            'reference_id'     => (string) $orderId,
        ]);

        $ch = curl_init($baseUrl . '/v2/payments');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Square-Version: 2024-01-18',
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode !== 200 || empty($result['payment']['id'])) {
            $errorDetail = $result['errors'][0]['detail'] ?? 'Unknown error';
            throw new \RuntimeException('Payment failed: ' . $errorDetail);
        }

        $paymentId    = $result['payment']['id'];
        $squareOrderId = $result['payment']['order_id'] ?? null;

        // Update order to completed
        $update = (object) [
            'id'             => $orderId,
            'status'         => 'completed',
            'payment_id'     => $paymentId,
            'square_order_id' => $squareOrderId,
            'modified'       => Factory::getDate()->toSql(),
        ];
        $db->updateObject('#__sanctuaryshop_orders', $update, 'id');

        // Clear cart
        $cartModel = $this->getMVCFactory()->createModel('Cart', 'Site', ['ignore_request' => true]);
        $cartModel->clear();

        $this->sendOrderConfirmation($orderId);

        return $paymentId;
    }

    private function sendOrderConfirmation(int $orderId): void
    {
        try {
            $params    = ComponentHelper::getParams('com_sanctuaryshop');
            $notifyTo  = $params->get('notify_email', '');
            $config    = Factory::getApplication()->get('config');
            $fromEmail = $config->get('mailfrom');
            $fromName  = $config->get('fromname');

            $mailer = Factory::getMailer();
            $mailer->setSender([$fromEmail, $fromName]);
            $mailer->setSubject('New Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT));
            $mailer->setBody('A new order has been placed. Order ID: ' . $orderId);

            if (!empty($notifyTo) && MailHelper::isEmailAddress($notifyTo)) {
                $mailer->addRecipient($notifyTo);
                $mailer->Send();
            }
        } catch (\Exception $e) {
            // Non-fatal — log but don't break the flow
            Factory::getApplication()->getLogger()->warning('Order email failed: ' . $e->getMessage());
        }
    }
}
