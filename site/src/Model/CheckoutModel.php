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
        $this->expireStaleOrders($params);
        $this->enforceCheckoutRateLimit();
        $cartModel = new CartModel(['ignore_request' => true]);
        $cartItems = $cartModel->getItems();

        if (empty($cartItems)) {
            throw new \RuntimeException('Your cart is empty.');
        }
        if ((int) $params->get('require_terms', 1) === 1 && empty($data['accept_terms'])) {
            throw new \RuntimeException('Please accept the terms and conditions before placing your order.');
        }
        if (array_filter($cartItems, static fn($item) => ($item->product_type ?? '') === 'subscription')) {
            throw new \RuntimeException('Subscription products are not available until recurring billing is configured.');
        }

        $firstName = trim((string) ($data['billing_firstname'] ?? ''));
        $lastName  = trim((string) ($data['billing_lastname'] ?? ''));
        $email     = trim((string) ($data['billing_email'] ?? ''));
        if ($firstName === '' || $lastName === '' || !MailHelper::isEmailAddress($email)) {
            throw new \RuntimeException('Please provide a valid name and email address.');
        }

        foreach (['address_line_1', 'locality', 'postal_code'] as $field) {
            if (trim((string) (($data['billing'] ?? [])[$field] ?? '')) === '') {
                throw new \RuntimeException('Please complete the billing address.');
            }
        }

        $this->validateInventory($cartItems);

        $subtotal = $cartModel->getSubtotal();
        $discount = $cartModel->getCouponDiscount($subtotal);
        $afterDiscount = max(0, $subtotal - $discount);
        $billingAddress = (array) ($data['billing'] ?? []);
        $shippingAddress = (array) ($data['shipping'] ?? $billingAddress);
        $taxRate  = self::locationRate($params->get('tax_rules', ''), $billingAddress, (float) $params->get('tax_rate', 0)) / 100;
        $tax      = round($afterDiscount * $taxRate, 2);

        $flatRate      = (float) $params->get('shipping_flat_rate', 0);
        $freeThreshold = (float) $params->get('shipping_free_threshold', 0);
        $requiresShipping = (bool) array_filter($cartItems, static fn($item) => ($item->product_type ?? 'physical') === 'physical');
        $shippingRate = self::locationRate($params->get('shipping_rules', ''), $shippingAddress, $flatRate);
        $shipping      = !$requiresShipping ? 0.00 : (($freeThreshold > 0 && $subtotal >= $freeThreshold) ? 0.00 : $shippingRate);
        $total         = round($afterDiscount + $tax + $shipping, 2);

        $db   = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $now  = Factory::getDate()->toSql();

        $billingName = $firstName . ' ' . $lastName;

        $order = (object) [
            'user_id'          => (int) $user->id,
            'guest_token'      => bin2hex(random_bytes(32)),
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'discount'         => $discount,
            'coupon_code'      => $discount > 0 ? $cartModel->getCouponCode() : null,
            'tax'              => $tax,
            'shipping'         => $shipping,
            'total'            => $total,
            'currency'         => strtoupper($params->get('currency', 'USD')),
            'billing_name'     => $billingName,
            'billing_email'    => $email,
            'billing_address'  => json_encode($data['billing'] ?? []),
            'shipping_address' => json_encode($data['shipping'] ?? $data['billing'] ?? []),
            'payment_method'   => 'square',
            'created'          => $now,
            'modified'         => $now,
        ];

        $db->insertObject('#__sanctuaryshop_orders', $order);
        $orderId = (int) $db->insertid();

        Factory::getApplication()->getSession()->set('sanctuaryshop.pending_order_id', $orderId);

        foreach ($cartItems as $item) {
            $variantInfoJson = null;
            if (!empty($item->variant_info)) {
                $variantInfoJson = json_encode($item->variant_info);
            }
            $line = (object) [
                'order_id'    => $orderId,
                'product_id'  => (int) $item->product_id,
                'title'       => $item->title,
                'sku'         => $item->sku,
                'variant_info' => $variantInfoJson,
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

        if ($orderId <= 0 || $sourceId === '' || strlen($sourceId) > 255) {
            throw new \RuntimeException('Invalid payment request.');
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->where($db->quoteName('id') . ' = ' . (int) $orderId);
        $order = $db->setQuery($query)->loadObject();

        if (!$order || $order->status !== 'pending') {
            if ($order && $order->status === 'completed' && !empty($order->payment_id)) {
                return (string) $order->payment_id;
            }
            throw new \RuntimeException('Order not found or already processed.');
        }

        $user = Factory::getApplication()->getIdentity();
        $pendingOrderId = (int) Factory::getApplication()->getSession()->get('sanctuaryshop.pending_order_id', 0);
        if ($pendingOrderId !== $orderId && ((int) $user->id === 0 || (int) $order->user_id !== (int) $user->id)) {
            throw new \RuntimeException('This checkout session is no longer valid.');
        }

        $this->validateInventory($this->getOrderItemsForCheckout($orderId));

        $baseUrl     = $environment === 'production'
            ? 'https://connect.squareup.com'
            : 'https://connect.squareupsandbox.com';
        $amountCents = (int) round((float) $order->total * 100);

        $payload = json_encode([
            // Stable across retries, including a timeout after Square accepted the payment.
            'idempotency_key' => 'ss-' . hash('sha256', (string) $orderId),
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

        $this->finalizePaidOrder($orderId, $paymentId, $squareOrderId, $order);

        return $paymentId;
    }

    public function refundWithSquare(int $orderId, ?float $requestedAmount = null): string
    {
        $params = ComponentHelper::getParams('com_sanctuaryshop');
        $token = $params->get('square_access_token', '');
        if (!$token) {
            throw new \RuntimeException('Square is not configured.');
        }

        $db = $this->getDatabase();
        $order = $db->setQuery(
            $db->getQuery(true)->select('*')->from($db->quoteName('#__sanctuaryshop_orders'))->where('id = ' . (int) $orderId)
        )->loadObject();
        if (!$order || empty($order->payment_id)) {
            throw new \RuntimeException('This order has no Square payment to refund.');
        }
        if ($order->status !== 'completed') {
            throw new \RuntimeException('Only a completed order can be refunded.');
        }

        $refunded = (float) $db->setQuery(
            $db->getQuery(true)->select('COALESCE(SUM(amount), 0)')->from($db->quoteName('#__sanctuaryshop_refunds'))
                ->where('order_id = ' . (int) $orderId)->where('status = ' . $db->quote('COMPLETED'))
        )->loadResult();
        $remaining = round((float) $order->total - $refunded, 2);
        $amount = $requestedAmount === null ? $remaining : round($requestedAmount, 2);
        if ($amount <= 0 || $amount > $remaining) {
            throw new \RuntimeException('Refund amount must be greater than zero and no more than the remaining balance of ' . $order->currency . ' ' . number_format($remaining, 2) . '.');
        }

        $baseUrl = $params->get('square_environment', 'sandbox') === 'production'
            ? 'https://connect.squareup.com' : 'https://connect.squareupsandbox.com';
        $payload = json_encode([
            'idempotency_key' => 'ss-refund-' . hash('sha256', $orderId . '|' . number_format($amount, 2, '.', '')),
            'payment_id'      => $order->payment_id,
            'amount_money'    => ['amount' => (int) round($amount * 100), 'currency' => $order->currency],
            'reason'          => 'SanctuaryShop order #' . $orderId,
        ]);
        $ch = curl_init($baseUrl . '/v2/refunds');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Square-Version: 2024-01-18', 'Authorization: Bearer ' . $token, 'Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $curlErr = curl_error($ch); curl_close($ch);
        if ($curlErr) {
            throw new \RuntimeException('Network error contacting Square: ' . $curlErr);
        }
        $result = json_decode($response, true);
        $refundId = $result['refund']['id'] ?? '';
        if ($httpCode < 200 || $httpCode >= 300 || !$refundId) {
            throw new \RuntimeException($result['errors'][0]['detail'] ?? ('HTTP ' . $httpCode));
        }
        $db->insertObject('#__sanctuaryshop_refunds', (object) [
            'order_id' => $orderId, 'square_refund_id' => $refundId, 'amount' => $amount,
            'currency' => $order->currency, 'status' => 'COMPLETED', 'created' => Factory::getDate()->toSql(),
        ]);
        if ($amount >= $remaining) {
            $db->setQuery(
                $db->getQuery(true)->update($db->quoteName('#__sanctuaryshop_orders'))
                    ->set('status = ' . $db->quote('refunded'))
                    ->set('square_refund_id = ' . $db->quote($refundId))
                    ->set('modified = ' . $db->quote(Factory::getDate()->toSql()))
                    ->where('id = ' . (int) $orderId)
            )->execute();
        }
        return (string) $refundId;
    }

    public function reissueDownloads(int $orderId): void
    {
        $db = $this->getDatabase();
        $order = $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__sanctuaryshop_orders'))->where('id = ' . (int) $orderId))->loadObject();
        if (!$order || $order->status !== 'completed') {
            throw new \RuntimeException('Downloads can only be reissued for a completed order.');
        }
        $this->generateDownloadTokens($orderId, (int) $order->user_id);
        $this->sendOrderConfirmation($orderId, $order);
    }

    public function finalizePaidOrder(int $orderId, string $paymentId, ?string $squareOrderId = null, ?object $knownOrder = null): void
    {
        $db = $this->getDatabase();
        $order = $knownOrder ?: $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__sanctuaryshop_orders'))->where('id = ' . (int) $orderId))->loadObject();
        if (!$order) {
            throw new \RuntimeException('Order not found.');
        }
        if ($order->status === 'completed' || $order->status === 'refunded') {
            return;
        }
        // Make finalization idempotent even if the browser response and Square's
        // webhook arrive at nearly the same time.
        $update = $db->getQuery(true)
            ->update($db->quoteName('#__sanctuaryshop_orders'))
            ->set('status = ' . $db->quote('completed'))
            ->set('payment_id = ' . $db->quote($paymentId))
            ->set('square_order_id = ' . ($squareOrderId ? $db->quote($squareOrderId) : 'NULL'))
            ->set('modified = ' . $db->quote(Factory::getDate()->toSql()))
            ->where('id = ' . (int) $orderId)
            ->where('status = ' . $db->quote('pending'));
        $db->setQuery($update)->execute();
        if ($db->getAffectedRows() !== 1) {
            return;
        }
        Factory::getApplication()->getSession()->set('sanctuaryshop.confirmation_order_id', $orderId);
        (new CartModel(['ignore_request' => true]))->clear();
        $this->generateDownloadTokens($orderId);
        $this->decrementStock($orderId);
        $this->decrementCouponUsage($orderId);
        $this->sendOrderConfirmation($orderId, $order);
    }

    private function generateDownloadTokens(int $orderId, ?int $tokenUserId = null): void
    {
        try {
            $params    = ComponentHelper::getParams('com_sanctuaryshop');
            $expiryDays = (int) $params->get('download_expiry_days', 30);
            $maxDl      = (int) $params->get('download_max_per_token', 5);

            $db    = $this->getDatabase();
            $user  = Factory::getApplication()->getIdentity();
            $tokenUserId = $tokenUserId ?? (int) $user->id;
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
                        'user_id'        => $tokenUserId,
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
                ->select(['oi.product_id', 'oi.quantity', 'oi.variant_info'])
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
                if ($db->getAffectedRows() !== 1) {
                    throw new \RuntimeException('Inventory changed while processing the order.');
                }

                $variantInfo = json_decode((string) ($item->variant_info ?? ''), true);
                foreach ((array) $variantInfo as $selection) {
                    $optionId = (int) ($selection['option_id'] ?? 0);
                    if ($optionId <= 0) {
                        continue;
                    }
                    $update = $db->getQuery(true)
                        ->update($db->quoteName('#__sanctuaryshop_product_variant_options'))
                        ->set($db->quoteName('stock') . ' = ' . $db->quoteName('stock') . ' - ' . (int) $item->quantity)
                        ->where($db->quoteName('id') . ' = ' . $optionId)
                        ->where($db->quoteName('product_id') . ' = ' . (int) $item->product_id)
                        ->where('(' . $db->quoteName('stock') . ' < 0 OR ' . $db->quoteName('stock') . ' >= ' . (int) $item->quantity . ')');
                    $db->setQuery($update)->execute();
                    if ($db->getAffectedRows() !== 1) {
                        throw new \RuntimeException('A selected product option is no longer available.');
                    }
                }
            }
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Inventory update failed for order #' . $orderId . ': ' . $e->getMessage(), 'error');
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
            $mailer->setAltBody($textBody);
            if (!empty($recipients)) {
                $mailer->addRecipient(array_shift($recipients));
                foreach ($recipients as $r) {
                    $mailer->addBcc($r);
                }
                $mailer->Send();
            }
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

    private function getOrderItemsForCheckout(int $orderId): array
    {
        $db = $this->getDatabase();
        $rows = $db->setQuery(
            $db->getQuery(true)
                ->select(['product_id', 'quantity', 'variant_info'])
                ->from($db->quoteName('#__sanctuaryshop_order_items'))
                ->where('order_id = ' . $orderId)
        )->loadObjectList() ?: [];
        return $rows;
    }

    private function validateInventory(array $items): void
    {
        $db = $this->getDatabase();
        foreach ($items as $item) {
            $productId = (int) $item->product_id;
            $quantity  = max(1, (int) $item->quantity);
            $product = $db->setQuery(
                $db->getQuery(true)
                    ->select(['product_type', 'stock'])
                    ->from($db->quoteName('#__sanctuaryshop_products'))
                    ->where('id = ' . $productId)
            )->loadObject();
            if (!$product) {
                throw new \RuntimeException('A product in your cart is no longer available.');
            }
            if (in_array($product->product_type, ['', 'physical'], true) && (int) $product->stock < $quantity) {
                throw new \RuntimeException('A product in your cart no longer has enough stock.');
            }
            $variantInfo = is_string($item->variant_info ?? null)
                ? json_decode($item->variant_info, true)
                : ($item->variant_info ?? []);
            foreach ((array) $variantInfo as $selection) {
                $optionId = (int) ($selection['option_id'] ?? 0);
                if ($optionId <= 0) {
                    throw new \RuntimeException('A selected product option is invalid.');
                }
                $option = $db->setQuery(
                    $db->getQuery(true)
                        ->select('stock')
                        ->from($db->quoteName('#__sanctuaryshop_product_variant_options'))
                        ->where('id = ' . $optionId)
                        ->where('product_id = ' . $productId)
                )->loadObject();
                if (!$option || ((int) $option->stock >= 0 && (int) $option->stock < $quantity)) {
                    throw new \RuntimeException('A selected product option no longer has enough stock.');
                }
            }
        }
    }

    private function expireStaleOrders(object $params): void
    {
        $minutes = max(15, (int) $params->get('pending_order_expiry_minutes', 60));
        $cutoff = Factory::getDate('-' . $minutes . ' minutes')->toSql();
        $db = $this->getDatabase();
        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__sanctuaryshop_orders'))
                ->set('status = ' . $db->quote('cancelled'))
                ->set('modified = ' . $db->quote(Factory::getDate()->toSql()))
                ->where('status = ' . $db->quote('pending'))
                ->where('created < ' . $db->quote($cutoff))
        )->execute();
    }

    private function enforceCheckoutRateLimit(): void
    {
        $session = Factory::getApplication()->getSession();
        $now = time();
        $windowStart = (int) $session->get('sanctuaryshop.checkout_window', 0);
        $attempts = (int) $session->get('sanctuaryshop.checkout_attempts', 0);
        if ($windowStart < ($now - 600)) {
            $windowStart = $now;
            $attempts = 0;
        }
        if ($attempts >= 8) {
            throw new \RuntimeException('Too many checkout attempts. Please wait a few minutes and try again.');
        }
        $session->set('sanctuaryshop.checkout_window', $windowStart);
        $session->set('sanctuaryshop.checkout_attempts', $attempts + 1);
    }

    public static function locationRate(string $rules, array $address, float $fallback): float
    {
        $country = strtoupper(trim((string) ($address['country'] ?? '')));
        $state = strtoupper(trim((string) ($address['administrative_district_level_1'] ?? '')));
        $keys = [];
        if ($country !== '' && $state !== '') {
            $keys[] = $country . '-' . $state;
        }
        if ($country !== '') {
            $keys[] = $country;
        }
        $matches = [];
        foreach (preg_split('/\R/', $rules) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $key = strtoupper($key);
            if (in_array($key, $keys, true) && is_numeric($value)) {
                $matches[$key] = max(0, (float) $value);
            }
        }
        foreach ($keys as $key) {
            if (array_key_exists($key, $matches)) {
                return $matches[$key];
            }
        }
        return max(0, $fallback);
    }
}
