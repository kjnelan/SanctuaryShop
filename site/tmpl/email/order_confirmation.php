<?php
defined('_JEXEC') or die;
/**
 * Email template variables:
 * @var int    $orderId
 * @var object $order
 * @var array  $items       order items
 * @var array  $downloads   download link info
 * @var string $currencySym
 * @var string $shopName
 */
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Helvetica,Arial,sans-serif">
<table style="width:100%;background:#f4f4f4;padding:20px">
<tr><td align="center">
    <table style="max-width:600px;width:100%;background:#fff;border-radius:6px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)">

        <!-- Header -->
        <tr><td style="background:#1a1a2e;padding:30px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px"><?php echo htmlspecialchars($shopName, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p style="color:#aab;margin:8px 0 0;font-size:14px">Order Confirmation</p>
        </td></tr>

        <tr><td style="padding:10px 30px">
            <p style="margin:0;font-size:13px;color:#555">
                View your order online:
                <a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&view=order&id=' . (int) $orderId . '&token=' . urlencode($order->guest_token), true, 0, true); ?>">Order details and downloads</a>
            </p>
        </td></tr>

        <!-- Intro -->
        <tr><td style="padding:30px 30px 10px">
            <p style="margin:0 0 6px;font-size:16px">Thank you for your order!</p>
            <p style="margin:0;color:#555;font-size:14px">
                Order #<?php echo str_pad($orderId, 5, '0', STR_PAD_LEFT); ?> &bull;
                <?php echo htmlspecialchars($order->billing_email, ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <p style="margin:12px 0 0;color:#888;font-size:13px">Placed <?php echo \Joomla\CMS\HTML\HTMLHelper::_('date', $order->created, 'F j, Y g:i A'); ?></p>
        </td></tr>

        <!-- Items table -->
        <tr><td style="padding:10px 30px">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:2px solid #eee">
                        <th style="text-align:left;padding:8px 0;font-size:12px;color:#888;text-transform:uppercase">Product</th>
                        <th style="text-align:center;padding:8px 0;font-size:12px;color:#888;text-transform:uppercase;width:60px">Qty</th>
                        <th style="text-align:right;padding:8px 0;font-size:12px;color:#888;text-transform:uppercase;width:80px">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) : ?>
                    <tr style="border-bottom:1px solid #f0f0f0">
                        <td style="padding:12px 0;font-size:14px"><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($item->sku) : ?>
                            <br><span style="font-size:12px;color:#999">SKU: <?php echo htmlspecialchars($item->sku, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;padding:12px 0;font-size:14px"><?php echo (int) $item->quantity; ?></td>
                        <td style="text-align:right;padding:12px 0;font-size:14px"><?php echo $currencySym . number_format($item->total_price, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </td></tr>

        <!-- Totals -->
        <tr><td style="padding:10px 30px 20px">
            <table style="width:100%;border-collapse:collapse">
                <?php if ((float) $order->discount > 0 || (float) $order->shipping > 0 || (float) $order->tax > 0) : ?>
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#555">Subtotal</td>
                    <td style="text-align:right;padding:4px 0;font-size:14px"><?php echo $currencySym . number_format($order->subtotal, 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float) $order->discount > 0) : ?>
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#c00">Discount (<?php echo htmlspecialchars($order->coupon_code, ENT_QUOTES, 'UTF-8'); ?>)</td>
                    <td style="text-align:right;padding:4px 0;font-size:14px;color:#c00">-<?php echo $currencySym . number_format($order->discount, 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float) $order->shipping > 0) : ?>
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#555">Shipping</td>
                    <td style="text-align:right;padding:4px 0;font-size:14px"><?php echo $currencySym . number_format($order->shipping, 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float) $order->tax > 0) : ?>
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#555">Tax</td>
                    <td style="text-align:right;padding:4px 0;font-size:14px"><?php echo $currencySym . number_format($order->tax, 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr style="border-top:2px solid #333">
                    <td style="padding:10px 0 0;font-size:16px;font-weight:bold">Total</td>
                    <td style="text-align:right;padding:10px 0 0;font-size:16px;font-weight:bold"><?php echo $currencySym . number_format($order->total, 2); ?></td>
                </tr>
            </table>
        </td></tr>

        <!-- Download links -->
        <?php if (!empty($downloads)) : ?>
        <tr><td style="padding:10px 30px">
            <h3 style="margin:0 0 10px;font-size:15px">Digital Downloads</h3>
            <?php $currentProduct = ''; ?>
            <?php foreach ($downloads as $dl) : ?>
                <?php if ($dl->product_title !== $currentProduct) : ?>
                    <?php $currentProduct = $dl->product_title; ?>
                    <p style="margin:0 0 4px;font-size:14px;font-weight:bold"><?php echo htmlspecialchars($dl->product_title, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <p style="margin:0 0 4px 16px;font-size:13px">
                    <a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&task=download.get&token=' . $dl->token, true, 0, true); ?>">
                        <?php echo htmlspecialchars($dl->file_label ?: $dl->filename, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <?php if ($dl->filesize > 0) : ?>
                        <span style="color:#999">(<?php echo number_format($dl->filesize / 1024 / 1024, 1); ?> MB)</span>
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>
            <p style="margin:12px 0 0;font-size:12px;color:#999">
                Downloads expire on their expiration date. Each link can be downloaded up to the maximum allowed times.
            </p>
        </td></tr>
        <?php endif; ?>

        <!-- Billing info -->
        <tr><td style="padding:20px 30px">
            <table style="width:100%">
                <tr>
                    <td style="width:50%;vertical-align:top">
                        <h3 style="margin:0 0 6px;font-size:13px;color:#888;text-transform:uppercase">Billing</h3>
                        <p style="margin:0;font-size:14px"><?php echo htmlspecialchars($order->billing_name, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p style="margin:0;font-size:14px;color:#555"><?php echo htmlspecialchars($order->billing_email, ENT_QUOTES, 'UTF-8'); ?></p>
                    </td>
                    <?php
                    $billingAddr = json_decode($order->billing_address, true);
                    $shippingAddr = json_decode($order->shipping_address, true);
                    ?>
                    <td style="width:50%;vertical-align:top">
                        <h3 style="margin:0 0 6px;font-size:13px;color:#888;text-transform:uppercase">Shipping</h3>
                        <?php if (!empty($shippingAddr)) : ?>
                        <p style="margin:0;font-size:14px">
                            <?php echo htmlspecialchars(($shippingAddr['address_line_1'] ?? '') . ($shippingAddr['address_line_2'] ?? '' ? ', ' . $shippingAddr['address_line_2'] : ''), ENT_QUOTES, 'UTF-8'); ?><br>
                            <?php echo htmlspecialchars(($shippingAddr['locality'] ?? '') . ', ' . ($shippingAddr['administrative_district_level_1'] ?? '') . ' ' . ($shippingAddr['postal_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <?php else : ?>
                        <p style="margin:0;font-size:14px;color:#999">Same as billing</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td></tr>

        <!-- Footer -->
        <tr><td style="padding:20px 30px;background:#fafafa;text-align:center;border-top:1px solid #eee">
            <p style="margin:0;font-size:12px;color:#999">
                <?php echo htmlspecialchars($shopName, ENT_QUOTES, 'UTF-8'); ?><br>
                Questions? Reply to this email.
            </p>
        </td></tr>

    </table>
</td></tr>
</table>
</body>
</html>
