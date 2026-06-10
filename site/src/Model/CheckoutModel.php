<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Mail\MailerFactoryInterface;
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
        $discount = $cartModel->getCouponDiscount($subtotal);
        $afterDiscount = max(0, $subtotal - $discount);
        $taxRate  = (float) $params->get('tax_rate', 0) / 100;
        $tax      = round($afterDiscount * $taxRate, 2);

        $flatRate      = (float) $params->get('shipping_flat_rate', 0);
        $freeThreshold = (float) $params->get('shipping_free_threshold', 0);
        $shipping      = ($freeThreshold > 0 && $subtotal >= $freeThreshold) ? 0.00 : $flatRate;
        $total         = round($afterDiscount + $tax + $shipping, 2);

        $db   = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $now  = Factory::getDate()->toSql();

        $billingName = trim(($data['billing_firstname'] ?? '') . ' ' . ($data['billing_lastname'] ?? ''));

        $order = (object) [
            'user_id'          => (int) $user->id,
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'discount'         => $discount,
            'coupon_code'      => $discount > 0 ? $cartModel->getCouponCode() : null,
            'tax'              => $tax,
            'shipping'         => $shipping,
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

        // Decrement stock for physical products
        $this->decrementStock($orderId);

        // Decrement coupon usage count
        $this->decrementCouponUsage($orderId);

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

    private function decrementStock(int $orderId): void
    {
        try {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('oi.product_id, oi.quantity')
                ->from($db->quoteName('#__sanctuaryshop_order_items', 'oi'))
                ->leftJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.id = oi.product_id')
                ->where('oi.order_id = ' . $orderId)
                ->where("(p.product_type IS NULL OR p.product_type = '' OR p.product_type = 'physical')");
            $items = $db->setQuery($query)->loadObjectList() ?: [];

            foreach ($items as $item) {
                $update = $db->getQuery(true)
                    ->update($db->quoteName('#__sanctuaryshop_products'))
                    ->set($db->quoteName('stock') . ' = ' . $db->quoteName('stock') . ' - ' . (int) $item->quantity)
                    ->where($db->quoteName('id') . ' = ' . (int) $item->product_id)
                    ->where($db->quoteName('stock') . ' >= ' . (int) $item->quantity);
                $db->setQuery($update)->execute();
            }
        } catch (\Exception $e) {
            // Non-fatal
        }
    }

    private function decrementCouponUsage(int $orderId): void
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('coupon_code')
                ->from($db->quoteName('#__sanctuaryshop_orders'))
                ->where($db->quoteName('id') . ' = ' . (int) $orderId);
            $couponCode = $db->setQuery($query)->loadResult();

            if (!empty($couponCode)) {
                $update = $db->getQuery(true)
                    ->update($db->quoteName('#__sanctuaryshop_coupons'))
                    ->set($db->quoteName('used_count') . ' = ' . $db->quoteName('used_count') . ' + 1')
                    ->where($db->quoteName('code') . ' = ' . $db->quote($couponCode));
                $db->setQuery($update)->execute();
            }
        } catch (\Exception $e) {
            // Non-fatal
        }
    }

    private function sendOrderConfirmation(int $orderId, object $order): void
    {
        try {
            $params   = ComponentHelper::getParams('com_sanctuaryshop');
            $notifyTo = $params->get('notify_email', '');
            $shopName = $params->get('shop_name', 'SanctuaryShop');
            $config   = Factory::getApplication()->getConfig();
            $db       = $this->getDatabase();

            // Load order items
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__sanctuaryshop_order_items'))
                ->where($db->quoteName('order_id') . ' = ' . (int) $orderId);
            $items = $db->setQuery($query)->loadObjectList() ?: [];

            // Load download tokens
            $dlQuery = $db->getQuery(true)
                ->select([
                    't.token', 't.download_count', 't.max_downloads', 't.expires', 't.revoked',
                    'p.title AS product_title',
                    'pf.label AS file_label', 'pf.filename', 'pf.filesize',
                ])
                ->from($db->quoteName('#__sanctuaryshop_download_tokens', 't'))
                ->leftJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.id = t.product_id')
                ->leftJoin($db->quoteName('#__sanctuaryshop_product_files', 'pf') . ' ON pf.id = t.file_id')
                ->where('t.order_id = ' . (int) $orderId)
                ->where('t.revoked = 0');
            $downloads = $db->setQuery($dlQuery)->loadObjectList() ?: [];

            // Currency symbol
            $currencyMap = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'CAD' => 'CA$', 'AUD' => 'A$'];
            $currencySym = $currencyMap[$order->currency] ?? $order->currency . ' ';

            // Render HTML email body
            $emailData = compact('orderId', 'order', 'items', 'downloads', 'currencySym', 'shopName');
            $htmlBody  = $this->renderEmailTemplate('order_confirmation', $emailData);

            // Plain text fallback
            $textBody  = 'Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) . "\n\n";
            $textBody .= 'Customer: ' . $order->billing_name . ' <' . $order->billing_email . ">\n";
            $textBody .= 'Total: ' . $order->currency . ' ' . number_format($order->total, 2) . "\n\n";
            $textBody .= "Items:\n";
            foreach ($items as $item) {
                $textBody .= '  ' . $item->title . ' × ' . (int) $item->quantity . ' — ' . $currencySym . number_format($item->total_price, 2) . "\n";
            }
            $textBody .= "\nThank you for your order!";

            $subject = 'Order Confirmation #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) . ' — ' . $shopName;

            // Build recipients (admin + customer)
            $recipients = [];
            if (!empty($notifyTo) && MailHelper::isEmailAddress($notifyTo)) {
                $recipients[] = $notifyTo;
            }
            if (MailHelper::isEmailAddress($order->billing_email)) {
                $recipients[] = $order->billing_email;
            }
            $recipients = array_unique($recipients);

            $mailerFactory = Factory::getContainer()->get(MailerFactoryInterface::class);
            $mailer = $mailerFactory->createMailer();
            $mailer->setSender([$config->get('mailfrom'), $shopName]);
            $mailer->setSubject($subject);
            $mailer->isHtml(true);
            $mailer->setBody($htmlBody);
            $mailer->addRecipient(array_shift($recipients));
            foreach ($recipients as $r) {
                $mailer->addBcc($r);
            }
            $mailer->Send();
        } catch (\Exception $e) {
            // Non-fatal
        }
    }

    private function renderEmailTemplate(string $template, array $data): string
    {
        extract($data, EXTR_OVERWRITE);
        ob_start();
        include JPATH_SITE . '/components/com_sanctuaryshop/tmpl/email/' . $template . '.php';
        return ob_get_clean();
    }
}
