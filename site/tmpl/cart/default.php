<?php
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$params      = ComponentHelper::getParams('com_sanctuaryshop');
$currency    = strtoupper($params->get('currency', 'USD'));
$taxRate     = (float) $params->get('tax_rate', 0);
$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$sym         = $currencyMap[$currency] ?? $currency . ' ';
$afterDisc   = max(0, $this->subtotal - $this->couponDiscount);
$tax         = (int) $params->get('prices_include_tax', 0) === 1 && $taxRate > 0
    ? round($afterDisc - ($afterDisc / (1 + $taxRate / 100)), 2)
    : round($afterDisc * $taxRate / 100, 2);
$flatRate    = (float) $params->get('shipping_flat_rate', 0);
$handlingFee = (float) $params->get('shipping_handling_fee', 0);
$shippingMethods = \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CheckoutModel::shippingMethods((string) $params->get('shipping_methods', ''), $flatRate);
$defaultMethod = $shippingMethods[0];
$totalWeight = array_sum(array_map(static fn($item) => max(0, (float) ($item->weight ?? 0)) * max(1, (int) ($item->quantity ?? 1)), $this->cartItems));
$weightRate = null;
foreach (preg_split('/\R/', (string) $params->get('shipping_weight_rules', '')) ?: [] as $line) {
    $parts = array_map('trim', explode('=', trim($line), 2));
    if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1]) && $totalWeight <= (float) $parts[0]) { $weightRate = max(0, (float) $parts[1]); break; }
}
$freeThresh  = (float) $params->get('shipping_free_threshold', 0);
$thresholdBase = (int) $params->get('shipping_free_after_discount', 0) === 1 ? $afterDisc : $this->subtotal;
$shippingBase = $weightRate === null ? $flatRate + ($defaultMethod['per_weight'] * $totalWeight) : $weightRate;
$shipping    = (int) $params->get('shipping_enabled', 1) !== 1 ? 0.00 : (($freeThresh > 0 && $thresholdBase >= $freeThresh) ? 0.00 : $shippingBase + max(0, $handlingFee));
$total       = round($afterDisc + $tax + $shipping, 2);
?>
<div class="com-sanctuaryshop-cart">
    <h1 class="mb-4"><?php echo Text::_('COM_SANCTUARYSHOP_CART'); ?>
        <?php if (!empty($this->cartItems)) : ?>
            <span class="badge bg-secondary fs-6"><?php echo array_sum(array_column($this->cartItems, 'quantity')); ?></span>
        <?php endif; ?>
    </h1>

    <?php if (empty($this->cartItems)) : ?>
        <div class="alert alert-info">
            <?php echo Text::_('COM_SANCTUARYSHOP_CART_EMPTY'); ?>
            <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" class="alert-link">
                <?php echo Text::_('COM_SANCTUARYSHOP_CONTINUE_SHOPPING'); ?>
            </a>
        </div>
    <?php else : ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.update'); ?>"
                  method="post" id="cartForm">

                <div class="card">
                    <div class="card-body p-0">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT'); ?></th>
                                <th class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_UNIT_PRICE'); ?></th>
                                <th class="text-center" style="width:110px"><?php echo Text::_('COM_SANCTUARYSHOP_QUANTITY'); ?></th>
                                <th class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_LINE_TOTAL'); ?></th>
                                <th class="text-end pe-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->cartItems as $item) : ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold"><?php echo $this->escape($item->title); ?></div>
                                    <?php if ($item->sku) : ?>
                                        <div class="small text-muted">SKU: <?php echo $this->escape($item->sku); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo $sym . number_format($item->unit_price, 2); ?></td>
                                <td class="text-center">
                                    <input type="number"
                                           name="quantity[<?php echo $this->escape($item->cart_key); ?>]"
                                           value="<?php echo (int) $item->quantity; ?>"
                                           min="0" max="999"
                                           class="form-control form-control-sm text-center">
                                </td>
                                <td class="text-end fw-semibold"><?php echo $sym . number_format($item->total_price, 2); ?></td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            title="<?php echo Text::_('COM_SANCTUARYSHOP_REMOVE'); ?>"
                                            onclick="removeItem('<?php echo $this->escape($item->cart_key); ?>')">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" class="btn btn-outline-secondary">
                        &larr; <?php echo Text::_('COM_SANCTUARYSHOP_CONTINUE_SHOPPING'); ?>
                    </a>
                    <button type="submit" class="btn btn-outline-primary">
                        <?php echo Text::_('COM_SANCTUARYSHOP_UPDATE_CART'); ?>
                    </button>
                </div>

                <input type="hidden" id="remove_cart_key" name="cart_key" value="">
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>

        <!-- Order summary sidebar -->
        <div class="col-lg-4">
            <!-- Coupon -->
            <div class="card mb-3">
                <div class="card-body">
                    <?php if ($this->couponCode) : ?>
                        <p class="mb-2">
                            <strong><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_CODE'); ?>:</strong>
                            <span class="text-success"><?php echo $this->escape($this->couponCode); ?></span>
                        </p>
                        <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.removeCoupon'); ?>"
                              method="post" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <?php echo Text::_('COM_SANCTUARYSHOP_COUPON_REMOVE'); ?>
                            </button>
                            <?php echo HTMLHelper::_('form.token'); ?>
                        </form>
                    <?php else : ?>
                        <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.applyCoupon'); ?>"
                              method="post" class="row g-2">
                            <div class="col-8">
                                <input type="text" name="coupon_code" class="form-control form-control-sm"
                                       placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_COUPON_CODE'); ?>"
                                       maxlength="50">
                            </div>
                            <div class="col-4">
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                    <?php echo Text::_('COM_SANCTUARYSHOP_COUPON_APPLY'); ?>
                                </button>
                            </div>
                            <?php echo HTMLHelper::_('form.token'); ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card sticky-top" style="top:80px">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_SUMMARY'); ?></div>
                <div class="card-body">
                    <table class="table table-sm mb-3">
                        <tr>
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_SUBTOTAL'); ?></td>
                            <td class="text-end"><?php echo $sym . number_format($this->subtotal, 2); ?></td>
                        </tr>
                        <?php if ($this->couponDiscount > 0) : ?>
                        <tr>
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_DISCOUNT'); ?> (<?php echo $this->escape($this->couponCode); ?>)</td>
                            <td class="text-end text-danger">-<?php echo $sym . number_format($this->couponDiscount, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($taxRate > 0) : ?>
                        <tr>
                            <td><?php echo Text::sprintf('COM_SANCTUARYSHOP_TAX_RATE_PCT', $taxRate); ?></td>
                            <td class="text-end"><?php echo $sym . number_format($tax, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($shipping > 0) : ?>
                        <tr>
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_SHIPPING'); ?></td>
                            <td class="text-end"><?php echo $sym . number_format($shipping, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="fw-bold">
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_TOTAL'); ?></td>
                            <td class="text-end"><?php echo $sym . number_format($total, 2); ?></td>
                        </tr>
                    </table>
                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=checkout'); ?>" class="btn btn-success btn-lg w-100">
                        <?php echo Text::_('COM_SANCTUARYSHOP_PROCEED_CHECKOUT'); ?> &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
function removeItem(cartKey) {
    var form = document.getElementById('cartForm');
    document.getElementById('remove_cart_key').value = cartKey;
    form.action = '<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.remove'); ?>';
    form.submit();
}
</script>
