<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class CheckoutModel extends BaseDatabaseModel
{
    public function processOrder(array $data): int
    {
        $params    = ComponentHelper::getParams('com_sanctuaryshop');
        $cartModel = new CartModel(['ignore_request' => true]);
        $cartItems = $cartModel->getItems();

        if (empty($cartItems)) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $subtotal = $cartModel->getSubtotal();
        $taxRate  = (float) $params->get('tax_rate', 0) / 100;
        $tax      = round($subtotal * $taxRate, 2);
        $total    = round($subtotal + $tax, 2);

        $db   = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $now  = Factory::getDate()->toSql();

        $billingName = trim(($data['billing_firstname'] ?? '') . ' ' . ($data['billing_lastname'] ?? ''));

        $order = (object) [
            'user_id'          => (int) $user->id,
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'shipping'         => 0.00,
            'total'            => $total,
            'currency'         => strtoupper($params->get('currency', 'USD')),
            'billing_name'     => $billingName,
            'billing_email'    => trim($data['billing_email'] ?? ''),
            'billing_address'  => json_encode($data['billing'] ?? []),
            'shipping_address' => json_encode($data['shipping'] ?? $data['billing'] ?? []),
            'payment_method'   => 'square',
            'created'          => $now,
            'modified'         => $now,
        ];

        $db->insertObject('#__sanctuaryshop_orders', $order);
        $orderId = (int) $db->insertid();

        foreach ($cartItems as $item) {
            $line = (object) [
                'order_id'    => $orderId,
                'product_id'  => (int) $item->product_id,
                'title'       => $item->title,
                'sku'         => $item->sku,
                'quantity'    => (int) $item->quantity,
                'unit_price'  => $item->unit_price,
                'total_price' => $item->total_price,
            ];
            $db->insertObject('#__sanctuaryshop_order_items', $line);
        }

        return $orderId;
    }

    public function chargeWithSquare(int $orderId, string $sourceId): string
    {
        $params      = ComponentHelper::getParams('com_sanctuaryshop');
        $environment = $params->get('square_environment', 'sandbox');
        $accessToken = $params->get('square_access_token', '');
        $locationId  = $params->get('square_location_id', '');

        if (empty($accessToken) || empty($locationId)) {
            throw new \RuntimeException('Square is not configured. Set your credentials in Components → SanctuaryShop → Options.');
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->where($db->quoteName('id') . ' = ' . (int) $orderId);
        $order = $db->setQuery($query)->loadObject();

        if (!$order || $order->status !== 'pending') {
            throw new \RuntimeException('Order not found or already processed.');
        }

        $baseUrl     = $environment === 'production'
            ? 'https://connect.squareup.com'
            : 'https://connect.squareupsandbox.com';
        $amountCents = (int) round((float) $order->total * 100);

        $payload = json_encode([
            'idempotency_key' => 'ss-' . $orderId . '-' . time(),
            'source_id'       => $sourceId,
            'amount_money'    => ['amount' => $amountCents, 'currency' => $order->currency],
            'location_id'     => $locationId,
            'buyer_email_address' => $order->billing_email,
            'note'            => 'SanctuaryShop Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT),
            'reference_id'    => (string) $orderId,
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
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new \RuntimeException('Network error contacting Square: ' . $curlErr);
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200 || empty($result['payment']['id'])) {
            $detail = $result['errors'][0]['detail'] ?? ('HTTP ' . $httpCode . ': ' . $response);
            throw new \RuntimeException('Payment declined: ' . $detail);
        }

        $paymentId     = $result['payment']['id'];
        $squareOrderId = $result['payment']['order_id'] ?? null;

        $update = (object) [
            'id'              => $orderId,
            'status'          => 'completed',
            'payment_id'      => $paymentId,
            'square_order_id' => $squareOrderId,
            'modified'        => Factory::getDate()->toSql(),
        ];
        $db->updateObject('#__sanctuaryshop_orders', $update, 'id');

        // Clear cart
        (new CartModel(['ignore_request' => true]))->clear();

        // Generate download tokens for digital products
        $this->generateDownloadTokens($orderId);

        $this->sendOrderConfirmation($orderId, $order);

        return $paymentId;
    }

    private function generateDownloadTokens(int $orderId): void
    {
        try {
            $params    = ComponentHelper::getParams('com_sanctuaryshop');
            $expiryDays = (int) $params->get('download_expiry_days', 30);
            $maxDl      = (int) $params->get('download_max_per_token', 5);

            $db    = $this->getDatabase();
            $user  = Factory::getApplication()->getIdentity();
            $now   = Factory::getDate()->toSql();
            $expires = $expiryDays > 0
                ? Factory::getDate('+' . $expiryDays . ' days')->toSql()
                : null;

            // Load order items with their product type
            $query = $db->getQuery(true)
                ->select('oi.id AS order_item_id, oi.product_id, oi.quantity, p.product_type')
                ->from($db->quoteName('#__sanctuaryshop_order_items', 'oi'))
                ->leftJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.id = oi.product_id')
                ->where('oi.order_id = ' . $orderId)
                ->where("p.product_type = 'digital'");
            $items = $db->setQuery($query)->loadObjectList() ?: [];

            foreach ($items as $item) {
                // Load all files for this product
                $fQuery = $db->getQuery(true)
                    ->select('id')
                    ->from($db->quoteName('#__sanctuaryshop_product_files'))
                    ->where('product_id = ' . (int) $item->product_id);
                $fileIds = $db->setQuery($fQuery)->loadColumn() ?: [];

                foreach ($fileIds as $fileId) {
                    $token = bin2hex(random_bytes(32)); // 64-char hex
                    $row   = (object) [
                        'token'          => $token,
                        'order_id'       => $orderId,
                        'order_item_id'  => (int) $item->order_item_id,
                        'product_id'     => (int) $item->product_id,
                        'file_id'        => (int) $fileId,
                        'user_id'        => (int) $user->id,
                        'download_count' => 0,
                        'max_downloads'  => $maxDl,
                        'expires'        => $expires,
                        'revoked'        => 0,
                        'created'        => $now,
                    ];
                    $db->insertObject('#__sanctuaryshop_download_tokens', $row);
                }
            }
        } catch (\Exception $e) {
            // Non-fatal — don't block order completion
        }
    }

    private function sendOrderConfirmation(int $orderId, object $order): void
    {
        try {
            $params   = ComponentHelper::getParams('com_sanctuaryshop');
            $notifyTo = $params->get('notify_email', '');
            $config   = Factory::getApplication()->get('config');

            $mailer = Factory::getMailer();
            $mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
            $mailer->setSubject('New Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) . ' — SanctuaryShop');
            $mailer->setBody(
                'A new order has been placed.' . "\n\n" .
                'Order: #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) . "\n" .
                'Customer: ' . $order->billing_name . ' <' . $order->billing_email . ">\n" .
                'Total: ' . $order->currency . ' $' . number_format($order->total, 2) . "\n"
            );

            if (!empty($notifyTo) && MailHelper::isEmailAddress($notifyTo)) {
                $mailer->addRecipient($notifyTo);
            }
            if (MailHelper::isEmailAddress($order->billing_email)) {
                $mailer->addRecipient($order->billing_email);
            }
            $mailer->Send();
        } catch (\Exception $e) {
            // Non-fatal
        }
    }
}
