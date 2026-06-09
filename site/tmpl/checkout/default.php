<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

if (empty($this->cartItems)) : ?>
<div class="alert alert-warning">
    Your cart is empty. <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>">Continue shopping</a>
</div>
<?php return; endif; ?>

<div class="sanctuaryshop-checkout">
    <h1>Checkout</h1>
    <div class="row">
        <!-- Left: billing + payment -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Billing Information</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="billing_firstname" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="billing_lastname" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="billing_email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" id="billing_address1" class="form-control mb-2" placeholder="Street address" required>
                            <input type="text" id="billing_address2" class="form-control" placeholder="Apt, suite, etc. (optional)">
                        </div>
                        <div class="col-5">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" id="billing_city" class="form-control" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" id="billing_state" class="form-control" maxlength="2" placeholder="TX" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">ZIP <span class="text-danger">*</span></label>
                            <input type="text" id="billing_zip" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Payment</h5></div>
                <div class="card-body">
                    <div id="card-container" class="mb-3 p-3 border rounded bg-light" style="min-height:100px"></div>
                    <div id="payment-message" class="mb-3" style="display:none"></div>
                    <button id="pay-button" class="btn btn-success btn-lg w-100" disabled>
                        Pay $<?php echo number_format($this->subtotal, 2); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: order summary -->
        <div class="col-lg-5">
            <div class="card sticky-top" style="top:80px">
                <div class="card-header"><h5 class="mb-0">Order Summary</h5></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                        <?php foreach ($this->cartItems as $item) : ?>
                            <tr>
                                <td><?php echo $this->escape($item->title); ?> &times; <?php echo (int) $item->quantity; ?></td>
                                <td class="text-end">$<?php echo number_format($item->total_price, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr><td>Subtotal</td><td class="text-end">$<?php echo number_format($this->subtotal, 2); ?></td></tr>
                        </tfoot>
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

    function showMessage(text, type = 'danger') {
        msgBox.className = 'alert alert-' + type;
        msgBox.textContent = text;
        msgBox.style.display = '';
    }

    if (!window.Square || !appId || !locationId) {
        showMessage('Payment system is not configured. Please contact the store.', 'warning');
        return;
    }

    let card;
    try {
        const payments = Square.payments(appId, locationId);
        card = await payments.card();
        await card.attach('#card-container');
        payBtn.disabled = false;
    } catch (e) {
        showMessage('Failed to load payment form: ' + e.message);
        return;
    }

    payBtn.addEventListener('click', async () => {
        // Validate billing fields
        const fields = ['billing_firstname','billing_lastname','billing_email',
                        'billing_address1','billing_city','billing_state','billing_zip'];
        for (const id of fields) {
            const el = document.getElementById(id);
            if (!el.value.trim()) { el.focus(); showMessage('Please fill in all required fields.'); return; }
        }

        payBtn.disabled = true;
        payBtn.textContent = 'Processing…';
        msgBox.style.display = 'none';

        try {
            // Step 1: create pending order
            const billingData = new URLSearchParams({
                [token]: '1',
                'jform[billing_firstname]': document.getElementById('billing_firstname').value,
                'jform[billing_lastname]':  document.getElementById('billing_lastname').value,
                'jform[billing_email]':     document.getElementById('billing_email').value,
                'jform[billing][address_line_1]':  document.getElementById('billing_address1').value,
                'jform[billing][address_line_2]':  document.getElementById('billing_address2').value,
                'jform[billing][locality]':        document.getElementById('billing_city').value,
                'jform[billing][administrative_district_level_1]': document.getElementById('billing_state').value,
                'jform[billing][postal_code]':     document.getElementById('billing_zip').value,
            });

            const orderResp = await fetch(processUrl, { method: 'POST', body: billingData,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'} });
            const orderData = await orderResp.json();
            if (!orderData.success) throw new Error(orderData.error || 'Could not create order.');

            // Step 2: tokenise card
            const tokenResult = await card.tokenize();
            if (tokenResult.status !== 'OK') {
                throw new Error(tokenResult.errors?.map(e => e.message).join(', ') || 'Card tokenization failed.');
            }

            // Step 3: charge via Square
            const payResp = await fetch(
                payUrl + '&order_id=' + orderData.order_id +
                '&source_id=' + encodeURIComponent(tokenResult.token) +
                '&' + token + '=1'
            );
            const payData = await payResp.json();
            if (!payData.success) throw new Error(payData.error || 'Payment failed.');

            // Success — redirect to confirmation
            window.location.href = confirmUrl + '&order_id=' + orderData.order_id;

        } catch (err) {
            showMessage(err.message);
            payBtn.disabled = false;
            payBtn.textContent = 'Pay $<?php echo number_format($this->subtotal, 2); ?>';
        }
    });
}());
</script>
