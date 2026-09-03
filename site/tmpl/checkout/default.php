<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
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
    if (($item->product_type ?? 'physical') === 'physical') {
        $allDigital = false;
        break;
    }
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
                        <div class="col-12" id="billing-phone-row">
                            <label class="form-label"><?php echo Text::_('COM_SANCTUARYSHOP_PHONE'); ?></label>
                            <input type="tel" id="billing_phone" class="form-control" autocomplete="tel"<?php echo (int) ComponentHelper::getParams('com_sanctuaryshop')->get('require_phone', 0) === 1 ? ' required' : ''; ?>>
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

            <?php if (!$allDigital) : ?>
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
            <?php endif; ?>

            <!-- Payment -->
            <div class="card mb-4">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT'); ?></div>
                <div class="card-body">
                    <div id="card-container" class="mb-3 p-3 border rounded bg-light" style="min-height:90px"></div>
                    <div id="payment-message" class="mb-3" style="display:none"></div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="accept_terms"<?php echo (int) ComponentHelper::getParams('com_sanctuaryshop')->get('require_terms', 1) === 1 ? ' required' : ''; ?>>
                        <label class="form-check-label small" for="accept_terms">
                            I agree to the <?php if ($termsUrl = ComponentHelper::getParams('com_sanctuaryshop')->get('terms_url', '')) : ?><a href="<?php echo $this->escape($termsUrl); ?>" target="_blank" rel="noopener">terms and conditions</a><?php else : ?>terms and conditions<?php endif; ?>.
                        </label>
                    </div>
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
                <?php if (!$allDigital && !empty($this->shippingMethods)) : ?>
                <div class="card-body border-top border-bottom">
                    <label class="form-label fw-semibold" for="shipping_method"><?php echo Text::_('COM_SANCTUARYSHOP_SHIPPING_METHOD'); ?></label>
                    <select class="form-select" id="shipping_method" name="shipping_method">
                        <?php foreach ($this->shippingMethods as $method) : ?><option value="<?php echo $this->escape($method['code']); ?>"><?php echo $this->escape($method['label']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="card-footer">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_SUBTOTAL'); ?></td>
                            <td class="text-end"><?php echo $sym . number_format($this->subtotal, 2); ?></td>
                        </tr>
                        <?php if ($this->discount > 0) : ?>
                        <tr>
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_DISCOUNT'); ?></td>
                            <td class="text-end text-danger">-<?php echo $sym . number_format($this->discount, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($this->taxRate > 0) : ?>
                        <tr>
                            <td><?php echo Text::sprintf('COM_SANCTUARYSHOP_TAX_RATE_PCT', $this->taxRate); ?></td>
                            <td id="checkout-tax" class="text-end"><?php echo $sym . number_format($this->tax, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($this->shipping > 0) : ?>
                        <tr>
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_SHIPPING'); ?></td>
                            <td id="checkout-shipping" class="text-end"><?php echo $sym . number_format($this->shipping, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="fw-bold">
                            <td><?php echo Text::_('COM_SANCTUARYSHOP_TOTAL'); ?></td>
                            <td id="checkout-total" class="text-end fs-5"><?php echo $sym . number_format($this->total, 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(async function initPayment() {
    const provider   = <?php echo json_encode($this->paymentProvider); ?>;
    const appId      = <?php echo json_encode($this->squareAppId); ?>;
    const locationId = <?php echo json_encode($this->squareLocationId); ?>;
    const stripeKey  = <?php echo json_encode($this->stripePublishableKey); ?>;
    const authKey    = <?php echo json_encode($this->authorizePublicClientKey); ?>;
    const authLogin  = <?php echo json_encode($this->authorizeApiLoginId); ?>;
    const token      = <?php echo json_encode(Session::getFormToken()); ?>;

    const processUrl = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&task=checkout.process&format=raw', false)); ?>;
    const payUrl     = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&task=checkout.payment&format=raw', false)); ?>;
    const stripeIntentUrl = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&task=checkout.stripeIntent&format=raw', false)); ?>;
    const stripeCompleteUrl = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&task=checkout.stripeComplete&format=raw', false)); ?>;
    const confirmUrl = <?php echo json_encode(Route::_('index.php?option=com_sanctuaryshop&view=checkout&layout=confirmation', false)); ?>;
    const taxRules = <?php echo json_encode((string) ComponentHelper::getParams('com_sanctuaryshop')->get('tax_rules', '')); ?>;
    const shippingRules = <?php echo json_encode((string) ComponentHelper::getParams('com_sanctuaryshop')->get('shipping_rules', '')); ?>;
    const baseTax = <?php echo json_encode((float) $this->taxRate); ?>;
    const baseShipping = <?php echo json_encode((float) $this->shipping); ?>;
    const subtotal = <?php echo json_encode((float) $this->subtotal); ?>;
    const discount = <?php echo json_encode((float) $this->discount); ?>;
    const flatShipping = <?php echo json_encode((float) ComponentHelper::getParams('com_sanctuaryshop')->get('shipping_flat_rate', 0)); ?>;
    const freeShippingThreshold = <?php echo json_encode((float) ComponentHelper::getParams('com_sanctuaryshop')->get('shipping_free_threshold', 0)); ?>;
    const handlingFee = <?php echo json_encode((float) ComponentHelper::getParams('com_sanctuaryshop')->get('shipping_handling_fee', 0)); ?>;
    const shippingEnabled = <?php echo json_encode((int) ComponentHelper::getParams('com_sanctuaryshop')->get('shipping_enabled', 1) === 1); ?>;
    const freeAfterDiscount = <?php echo json_encode((int) ComponentHelper::getParams('com_sanctuaryshop')->get('shipping_free_after_discount', 0) === 1); ?>;
    const shippingMethods = <?php echo json_encode($this->shippingMethods); ?>;
    const totalWeight = <?php echo json_encode(array_sum(array_map(static fn($item) => max(0, (float) ($item->weight ?? 0)) * max(1, (int) ($item->quantity ?? 1)), $this->cartItems))); ?>;
    const pricesIncludeTax = <?php echo json_encode((int) ComponentHelper::getParams('com_sanctuaryshop')->get('prices_include_tax', 0) === 1); ?>;
    const requirePhone = <?php echo json_encode((int) ComponentHelper::getParams('com_sanctuaryshop')->get('require_phone', 0) === 1); ?>;

    const payBtn = document.getElementById('pay-button');
    const msgBox = document.getElementById('payment-message');

    function locationRate(rules, country, state, fallback) {
        const keys = [];
        country = (country || '').trim().toUpperCase();
        state = (state || '').trim().toUpperCase();
        if (country && state) keys.push(country + '-' + state);
        if (country) keys.push(country);
        const values = {};
        (rules || '').split(/\r?\n/).forEach(line => {
            const p = line.split('=');
            if (p.length === 2 && !p[0].trim().startsWith('#') && !isNaN(parseFloat(p[1]))) values[p[0].trim().toUpperCase()] = Math.max(0, parseFloat(p[1]));
        });
        for (const key of keys) if (Object.prototype.hasOwnProperty.call(values, key)) return values[key];
        return Math.max(0, fallback);
    }

    function updateDisplayedTotals() {
        const country = document.getElementById('billing_country')?.value || 'US';
        const state = document.getElementById('billing_state_field')?.value || '';
        const taxRate = locationRate(taxRules, country, state, baseTax);
        const afterDiscount = Math.max(0, subtotal - discount);
        const tax = pricesIncludeTax && taxRate > 0
            ? Math.round((afterDiscount - (afterDiscount / (1 + taxRate / 100))) * 100) / 100
            : Math.round(afterDiscount * taxRate) / 100;
        const same = document.getElementById('same_as_billing')?.checked ?? true;
        const shipCountry = same ? country : (document.getElementById('shipping_country')?.value || country);
        const shipState = same ? state : (document.getElementById('shipping_state_field')?.value || '');
        const selectedMethod = shippingMethods.find(method => method.code === (document.getElementById('shipping_method')?.value || '')) || shippingMethods[0] || {base: flatShipping, per_weight: 0};
        const shippingRate = locationRate(shippingRules, shipCountry, shipState, Number(selectedMethod.base) || flatShipping) + (Number(selectedMethod.per_weight) || 0) * totalWeight + Math.max(0, handlingFee);
        const thresholdBase = freeAfterDiscount ? afterDiscount : subtotal;
        const shipping = !shippingEnabled || freeShippingThreshold > 0 && thresholdBase >= freeShippingThreshold ? 0 : shippingRate;
        const total = Math.round((afterDiscount + tax + shipping) * 100) / 100;
        document.getElementById('checkout-tax') && (document.getElementById('checkout-tax').textContent = <?php echo json_encode($sym); ?> + tax.toFixed(2));
        document.getElementById('checkout-shipping') && (document.getElementById('checkout-shipping').textContent = <?php echo json_encode($sym); ?> + shipping.toFixed(2));
        document.getElementById('checkout-total') && (document.getElementById('checkout-total').textContent = <?php echo json_encode($sym); ?> + total.toFixed(2));
        payBtn.textContent = '<?php echo Text::_('COM_SANCTUARYSHOP_PAY'); ?> ' + <?php echo json_encode($sym); ?> + total.toFixed(2);
    }

    // Same-as-billing toggle
    document.getElementById('same_as_billing')?.addEventListener('change', function () {
        document.getElementById('shipping-fields').style.display = this.checked ? 'none' : '';
        updateDisplayedTotals();
    });
    ['billing_country', 'billing_state_field', 'shipping_country', 'shipping_state_field'].forEach(id => document.getElementById(id)?.addEventListener('input', updateDisplayedTotals));
    document.getElementById('shipping_method')?.addEventListener('change', updateDisplayedTotals);
    updateDisplayedTotals();

    function showMessage(text, type = 'danger') {
        msgBox.className = 'alert alert-' + type;
        msgBox.textContent = text;
        msgBox.style.display = '';
        msgBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (provider === 'square' && (!window.Square || !appId || !locationId)) {
        showMessage('<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_NOT_CONFIGURED'); ?>', 'warning');
        return;
    }
    if (provider === 'stripe' && (!window.Stripe || !stripeKey)) {
        showMessage('<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_NOT_CONFIGURED'); ?>', 'warning'); return;
    }
    if (provider === 'authorize_net' && (!window.Accept || !authKey || !authLogin)) {
        showMessage('<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_NOT_CONFIGURED'); ?>', 'warning'); return;
    }

    let card, stripe;
    try {
        if (provider === 'square') {
            const payments = Square.payments(appId, locationId);
            card = await payments.card(); await card.attach('#card-container');
        } else if (provider === 'stripe') {
            stripe = Stripe(stripeKey);
            const elements = stripe.elements(); card = elements.create('card'); card.mount('#card-container');
        } else {
            document.getElementById('card-container').innerHTML = '<div class="row g-2"><div class="col-12"><label class="form-label">Card number</label><input id="anet-card" class="form-control" inputmode="numeric" autocomplete="cc-number" required></div><div class="col-4"><label class="form-label">Month</label><input id="anet-month" class="form-control" placeholder="MM" inputmode="numeric" autocomplete="cc-exp-month" required></div><div class="col-4"><label class="form-label">Year</label><input id="anet-year" class="form-control" placeholder="YYYY" inputmode="numeric" autocomplete="cc-exp-year" required></div><div class="col-4"><label class="form-label">CVV</label><input id="anet-cvv" class="form-control" inputmode="numeric" autocomplete="cc-csc" required></div></div>';
        }
        payBtn.disabled = false;
    } catch (e) {
        showMessage('<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_LOAD_ERROR'); ?>: ' + e.message);
        return;
    }

    payBtn.addEventListener('click', async () => {
        const required = ['billing_firstname','billing_lastname','billing_email','billing_address1','billing_city','billing_zip'];
        if (requirePhone) required.push('billing_phone');
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

        const sameAsBilling = document.getElementById('same_as_billing')?.checked ?? true;

        try {
            const billingData = new URLSearchParams({
                [token]: '1',
                'jform[billing_firstname]': document.getElementById('billing_firstname').value,
                'jform[billing_lastname]':  document.getElementById('billing_lastname').value,
                'jform[billing_email]':     document.getElementById('billing_email').value,
                'jform[billing_phone]':      document.getElementById('billing_phone').value,
                'jform[shipping_method]':    document.getElementById('shipping_method')?.value || '',
                'jform[billing][address_line_1]':   document.getElementById('billing_address1').value,
                'jform[billing][address_line_2]':   document.getElementById('billing_address2').value,
                'jform[billing][locality]':         document.getElementById('billing_city').value,
                'jform[billing][administrative_district_level_1]': document.getElementById('billing_state_field').value,
                'jform[billing][postal_code]':      document.getElementById('billing_zip').value,
                'jform[billing][country]':          document.getElementById('billing_country').value,
                'jform[accept_terms]':              document.getElementById('accept_terms')?.checked ? '1' : '0',
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

            const paymentParams = new URLSearchParams({order_id: orderData.order_id, provider, [token]: '1'});
            if (provider === 'square') {
                const tokenResult = await card.tokenize();
                if (tokenResult.status !== 'OK') throw new Error(tokenResult.errors?.map(e => e.message).join(', ') || '<?php echo Text::_('COM_SANCTUARYSHOP_CARD_TOKEN_ERROR'); ?>');
                paymentParams.set('source_id', tokenResult.token);
            } else if (provider === 'stripe') {
                const intentResp = await fetch(stripeIntentUrl + '&order_id=' + orderData.order_id + '&' + token + '=1');
                const intentData = await intentResp.json();
                if (!intentData.success) throw new Error(intentData.error || '<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_FAILED'); ?>');
                const result = await stripe.confirmCardPayment(intentData.client_secret, {payment_method: {card, billing_details: {name: document.getElementById('billing_firstname').value + ' ' + document.getElementById('billing_lastname').value, email: document.getElementById('billing_email').value}}});
                if (result.error) throw new Error(result.error.message);
                const completeResp = await fetch(stripeCompleteUrl + '&order_id=' + orderData.order_id + '&payment_intent=' + encodeURIComponent(result.paymentIntent.id) + '&' + token + '=1');
                const completeData = await completeResp.json();
                if (!completeData.success) throw new Error(completeData.error || '<?php echo Text::_('COM_SANCTUARYSHOP_PAYMENT_FAILED'); ?>');
                window.location.href = confirmUrl + '&order_id=' + orderData.order_id;
                return;
            } else {
                const nonce = await new Promise((resolve, reject) => Accept.dispatchData({authData: {clientKey: authKey, apiLoginID: authLogin}, cardData: {cardNumber: document.getElementById('anet-card').value, month: document.getElementById('anet-month').value, year: document.getElementById('anet-year').value, cardCode: document.getElementById('anet-cvv').value, zip: document.getElementById('billing_zip').value}}, response => response.messages?.resultCode === 'Ok' ? resolve(response.opaqueData) : reject(new Error(response.messages?.message?.[0]?.text || 'Authorize.Net card tokenization failed.'))));
                paymentParams.set('data_descriptor', nonce.dataDescriptor); paymentParams.set('data_value', nonce.dataValue);
            }
            const payResp = await fetch(payUrl + '&' + paymentParams.toString());
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
