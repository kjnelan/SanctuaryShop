<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$sym         = $currencyMap[$this->currency] ?? $this->currency . ' ';

if (empty($this->cartItems)) : ?>
<div class="alert alert-warning">
    <?php echo Text::_('COM_SANCTUARYSHOP_CART_EMPTY'); ?>
    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" class="alert-link">
        <?php echo Text::_('COM_SANCTUARYSHOP_CONTINUE_SHOPPING'); ?>
    </a>
</div>
<?php return; endif;

// Check if all items are digital (no shipping needed)
$allDigital = true;
foreach ($this->cartItems as $item) {
    // We don't have product_type on cart items yet — use a fallback
    // It'll always show shipping; digital-only detection is a UX nicety
}
?>

<div class="com-sanctuaryshop-checkout">
    <h1 class="mb-4"><?php echo Text::_('COM_SANCTUARYSHOP_CHECKOUT'); ?></h1>

    <div class="row g-4">
        <!-- Left: billing + shipping + payment -->
        <div class="col-lg-7">

            <!-- Billing -->
            <div class="card mb-4">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_BILLING_INFO'); ?></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_FIRST_NAME'); ?> <span class="text-danger">*</span></label>
                            <input type="text" id="billing_firstname" class="form-control" required autocomplete="given-name">
                        </div>
                        <div class="col-6">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_LAST_NAME'); ?> <span class="text-danger">*</span></label>
                            <input type="text" id="billing_lastname" class="form-control" required autocomplete="family-name">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_EMAIL'); ?> <span class="text-danger">*</span></label>
                            <input type="email" id="billing_email" class="form-control" required autocomplete="email">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_ADDRESS'); ?> <span class="text-danger">*</span></label>
                            <input type="text" id="billing_address1" class="form-control mb-2" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_STREET_ADDRESS'); ?>" required autocomplete="address-line1">
                            <input type="text" id="billing_address2" class="form-control" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_ADDRESS2'); ?>" autocomplete="address-line2">
                        </div>
                        <div class="col-5">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_CITY'); ?> <span class="text-danger">*</span></label>
                            <input type="text" id="billing_city" class="form-control" required autocomplete="address-level2">
                        </div>
                        <div class="col-3">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_STATE'); ?></label>
                            <input type="text" id="billing_state_field" class="form-control" maxlength="50" autocomplete="address-level1">
                        </div>
                        <div class="col-4">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_ZIP'); ?> <span class="text-danger">*</span></label>
                            <input type="text" id="billing_zip" class="form-control" required autocomplete="postal-code">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_COUNTRY'); ?></label>
                            <input type="text" id="billing_country" class="form-control" value="US" maxlength="2" autocomplete="country" placeholder="US">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping address -->
            <div class="card mb-4">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <?php echo Text::_('COM_SANCTUARYSHOP_SHIPPING_INFO'); ?>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="same_as_billing" checked>
                        <label class="form-check-label fw-normal" for="same_as_billing">
                            <?php echo Text::_('COM_SANCTUARYSHOP_SAME_AS_BILLING'); ?>
                        </label>
                    </div>
                </div>
                <div class="card-body" id="shipping-fields" style="display:none">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_FIRST_NAME'); ?></label>
                            <input type="text" id="shipping_firstname" class="form-control" autocomplete="shipping given-name">
                        </div>
                        <div class="col-6">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_LAST_NAME'); ?></label>
                            <input type="text" id="shipping_lastname" class="form-control" autocomplete="shipping family-name">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_ADDRESS'); ?></label>
                            <input type="text" id="shipping_address1" class="form-control mb-2" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_STREET_ADDRESS'); ?>" autocomplete="shipping address-line1">
                            <input type="text" id="shipping_address2" class="form-control" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_ADDRESS2'); ?>" autocomplete="shipping address-line2">
                        </div>
                        <div class="col-5">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_CITY'); ?></label>
                            <input type="text" id="shipping_city" class="form-control" autocomplete="shipping address-level2">
                        </div>
                        <div class="col-3">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_STATE'); ?></label>
                            <input type="text" id="shipping_state_field" class="form-control" maxlength="50" autocomplete="shipping address-level1">
                        </div>
                        <div class="col-4">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_ZIP'); ?></label>
                            <input type="text" id="shipping_zip" class="form-control" autocomplete="shipping postal-code">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_COUNTRY'); ?></label>
                            <input type="text" id="shipping_country" class="form-control" value="US" maxlength="2" autocomplete="shipping country">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment -->
            <div class="card mb-4">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT'); ?></div>
                <div class="card-body">
                    <div id="card-container" class="mb-3 p-3 border rounded bg-light" style="min-height:90px"></div>
                    <div id="payment-message" class="mb-3" style="display:none"></div>
                    <button id="pay-button" class="btn btn-success btn-lg w-100" disabled>
                        <?php echo Text::_('COM_SANCTUARYSHOP_PAY'); ?> <?php echo $sym . number_format($this->total, 2); ?>
                    </button>
                    <p class="text-center text-muted small mt-2 mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg>
                        <?php echo Text::_('COM_SANCTUARYSHOP_SECURE_PAYMENT'); ?>
                    </p>
                </div>
            </div>

        </div>

        <!-- Right: order summary -->
        <div class="col-lg-5">
            <div class="card sticky-top" style="top:80px">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_SUMMARY'); ?></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                    <?php foreach ($this->cartItems as $item) : ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="me-2">
                                <div class="fw-semibold"><?php echo $this->escape($item->title); ?></div>
                                <small class="text-muted">&times; <?php echo (int) $item->quantity; ?></small>
                            </div>
                            <span><?php echo $sym . number_format($item->total_price, 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_SUBTOTAL'); ?></td>
                            <td class="text-end"><?php echo $sym . number_format($this->subtotal, 2); ?></td>
                        </tr>
                        <?php if ($this->taxRate > 0) : ?>
                        <tr>
                            <td><?php echo Text::sprintf('COM_SANCTUARYSHOP_TAX_RATE_PCT', $this->taxRate); ?></td>
                            <td class="text-end"><?php echo $sym . number_format($this->tax, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="fw-bold">
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_TOTAL'); ?></td>
                            <td class="text-end fs-5"><?php echo $sym . number_format($this->total, 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(async function initSquare() {
    const appId      = <?php echo json_encode($this->squareAppId); ?>;
    const locationId = <?php echo json_encode($this->squareLocationId); ?>;
    const token      = <?php echo json_encode(Session::getFormToken()); ?>;

    const processUrl = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&task=checkout.process&format=raw', false)); ?>;
    const payUrl     = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&task=checkout.squarePayment&format=raw', false)); ?>;
    const confirmUrl = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&view=checkout&layout=confirmation', false)); ?>;

    const payBtn = document.getElementById('pay-button');
    const msgBox = document.getElementById('payment-message');

    // Same-as-billing toggle
    document.getElementById('same_as_billing').addEventListener('change', function () {
        document.getElementById('shipping-fields').style.display = this.checked ? 'none' : '';
    });

    function showMessage(text, type = 'danger') {
        msgBox.className = 'alert alert-' + type;
        msgBox.textContent = text;
        msgBox.style.display = '';
        msgBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (!window.Square || !appId || !locationId) {
        showMessage('<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_NOT_CONFIGURED'); ?>', 'warning');
        return;
    }

    let card;
    try {
        const payments = Square.payments(appId, locationId);
        card = await payments.card();
        await card.attach('#card-container');
        payBtn.disabled = false;
    } catch (e) {
        showMessage('<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_LOAD_ERROR'); ?>: ' + e.message);
        return;
    }

    payBtn.addEventListener('click', async () => {
        const required = ['billing_firstname','billing_lastname','billing_email','billing_address1','billing_city','billing_zip'];
        for (const id of required) {
            const el = document.getElementById(id);
            if (!el.value.trim()) {
                el.focus();
                el.classList.add('is-invalid');
                showMessage('<?php echo Text::_('COM_SANCTUARYSHOP_FILL_REQUIRED_FIELDS'); ?>');
                return;
            }
            el.classList.remove('is-invalid');
        }

        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span><?php echo Text::_('COM_SANCTUARYSHOP_PROCESSING'); ?>…';
        msgBox.style.display = 'none';

        const sameAsBilling = document.getElementById('same_as_billing').checked;

        try {
            const billingData = new URLSearchParams({
                [token]: '1',
                'jform[billing_firstname]': document.getElementById('billing_firstname').value,
                'jform[billing_lastname]':  document.getElementById('billing_lastname').value,
                'jform[billing_email]':     document.getElementById('billing_email').value,
                'jform[billing][address_line_1]':   document.getElementById('billing_address1').value,
                'jform[billing][address_line_2]':   document.getElementById('billing_address2').value,
                'jform[billing][locality]':         document.getElementById('billing_city').value,
                'jform[billing][administrative_district_level_1]': document.getElementById('billing_state_field').value,
                'jform[billing][postal_code]':      document.getElementById('billing_zip').value,
                'jform[billing][country]':          document.getElementById('billing_country').value,
            });

            if (!sameAsBilling) {
                billingData.append('jform[shipping][address_line_1]',   document.getElementById('shipping_address1').value);
                billingData.append('jform[shipping][address_line_2]',   document.getElementById('shipping_address2').value);
                billingData.append('jform[shipping][locality]',         document.getElementById('shipping_city').value);
                billingData.append('jform[shipping][administrative_district_level_1]', document.getElementById('shipping_state_field').value);
                billingData.append('jform[shipping][postal_code]',      document.getElementById('shipping_zip').value);
                billingData.append('jform[shipping][country]',          document.getElementById('shipping_country').value);
            }

            const orderResp = await fetch(processUrl, {
                method: 'POST',
                body: billingData,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            });
            const orderData = await orderResp.json();
            if (!orderData.success) throw new Error(orderData.error || '<?php echo Text::_('COM_SANCTUARYSHOP_ORDER_CREATE_ERROR'); ?>');

            const tokenResult = await card.tokenize();
            if (tokenResult.status !== 'OK') {
                throw new Error(tokenResult.errors?.map(e => e.message).join(', ') || '<?php echo Text::_('COM_SANCTUARYSHOP_CARD_TOKEN_ERROR'); ?>');
            }

            const payResp = await fetch(
                payUrl + '&order_id=' + orderData.order_id +
                '&source_id=' + encodeURIComponent(tokenResult.token) +
                '&' + token + '=1'
            );
            const payData = await payResp.json();
            if (!payData.success) throw new Error(payData.error || '<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_FAILED'); ?>');

            window.location.href = confirmUrl + '&order_id=' + orderData.order_id;

        } catch (err) {
            showMessage(err.message);
            payBtn.disabled = false;
            payBtn.textContent = '<?php echo Text::_('COM_SANCTUARYSHOP_PAY'); ?> <?php echo $sym . number_format($this->total, 2); ?>';
        }
    });
}());
</script>
