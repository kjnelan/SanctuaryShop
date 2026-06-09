<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
?>
<div class="sanctuaryshop-checkout">
    <h1>Checkout</h1>

    <?php if (empty($this->cartItems)) : ?>
        <div class="alert alert-warning">Your cart is empty.</div>
    <?php else : ?>
    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Billing Information</h5></div>
                <div class="card-body">
                    <form id="checkoutForm" action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=checkout.process'); ?>" method="post">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="jform[billing_firstname]" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="jform[billing_lastname]" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email *</label>
                                <input type="email" name="jform[billing_email]" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address *</label>
                                <input type="text" name="jform[billing][address_line_1]" class="form-control mb-2" placeholder="Street address" required>
                                <input type="text" name="jform[billing][address_line_2]" class="form-control" placeholder="Apt, suite, etc. (optional)">
                            </div>
                            <div class="col-6">
                                <label class="form-label">City *</label>
                                <input type="text" name="jform[billing][locality]" class="form-control" required>
                            </div>
                            <div class="col-3">
                                <label class="form-label">State *</label>
                                <input type="text" name="jform[billing][administrative_district_level_1]" class="form-control" maxlength="2" required>
                            </div>
                            <div class="col-3">
                                <label class="form-label">ZIP *</label>
                                <input type="text" name="jform[billing][postal_code]" class="form-control" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5>Payment</h5>
                        <div id="card-container" class="mb-3 p-3 border rounded bg-light" style="min-height:90px"></div>
                        <div id="payment-status-container" class="mb-3"></div>

                        <?php echo HTMLHelper::_('form.token'); ?>
                        <input type="hidden" name="source_id" id="source_id">
                        <input type="hidden" name="order_id" id="order_id" value="0">

                        <button id="card-button" type="button" class="btn btn-primary btn-lg w-100">
                            Pay $<?php echo number_format($this->subtotal, 2); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
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
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Subtotal</td>
                                <td class="text-end">$<?php echo number_format($this->subtotal, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
(async () => {
    const appId      = '<?php echo $this->escape($this->squareAppId); ?>';
    const locationId = '<?php echo $this->escape($this->squareLocationId); ?>';
    const ajaxBase   = '<?php echo Route::_('index.php?option=com_sanctuaryshop&task=checkout.squarePayment&format=raw', false); ?>';
    const token      = '<?php echo Session::getFormToken(); ?>';

    if (!window.Square || !appId || !locationId) {
        document.getElementById('card-container').innerHTML =
            '<div class="alert alert-danger">Payment system not configured. Please contact the store.</div>';
        return;
    }

    const payments = Square.payments(appId, locationId);
    const card     = await payments.card();
    await card.attach('#card-container');

    document.getElementById('card-button').addEventListener('click', async () => {
        const btn = document.getElementById('card-button');
        btn.disabled = true;
        btn.textContent = 'Processing…';

        try {
            // First submit billing form to create the pending order
            const form     = document.getElementById('checkoutForm');
            const formData = new FormData(form);

            const orderResp = await fetch('<?php echo Route::_('index.php?option=com_sanctuaryshop&task=checkout.process&format=json', false); ?>', {
                method: 'POST',
                body: formData,
            });
            const orderData = await orderResp.json();

            if (!orderData.success) {
                throw new Error(orderData.error || 'Failed to create order.');
            }

            const orderId = orderData.order_id;

            // Tokenise the card
            const result = await card.tokenize();
            if (result.status !== 'OK') {
                throw new Error(result.errors?.[0]?.message || 'Card tokenization failed.');
            }

            // Charge via Square
            const payResp = await fetch(
                ajaxBase + '&order_id=' + orderId + '&source_id=' + encodeURIComponent(result.token) + '&' + token + '=1',
                { method: 'GET' }
            );
            const payData = await payResp.json();

            if (!payData.success) {
                throw new Error(payData.error || 'Payment failed.');
            }

            // Redirect to confirmation
            window.location.href = '<?php echo Route::_('index.php?option=com_sanctuaryshop&view=checkout&layout=confirmation', false); ?>&order_id=' + orderId;

        } catch (err) {
            document.getElementById('payment-status-container').innerHTML =
                '<div class="alert alert-danger">' + err.message + '</div>';
            btn.disabled    = false;
            btn.textContent = 'Try Again';
        }
    });
})();
</script>
