<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$p = $this->params;
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=settings.save'); ?>"
      method="post" name="adminForm" id="adminForm">

    <?php echo HTMLHelper::_('uitab.startTabSet', 'sanctuaryshop-settings', ['active' => 'general', 'recall' => true]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sanctuaryshop-settings', 'general', Text::_('COM_SANCTUARYSHOP_TAB_GENERAL')); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shop_name"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SHOP_NAME'); ?></label>
                        <input type="text" class="form-control" id="shop_name" name="shop_name" value="<?php echo $this->escape($p->get('shop_name', '')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shop_tagline"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SHOP_TAGLINE'); ?></label>
                        <input type="text" class="form-control" id="shop_tagline" name="shop_tagline" value="<?php echo $this->escape($p->get('shop_tagline', '')); ?>">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold" for="store_email"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STORE_EMAIL'); ?></label><input type="email" class="form-control" id="store_email" name="store_email" value="<?php echo $this->escape($p->get('store_email', '')); ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold" for="store_phone"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STORE_PHONE'); ?></label><input type="text" class="form-control" id="store_phone" name="store_phone" value="<?php echo $this->escape($p->get('store_phone', '')); ?>"></div>
                        <div class="col-12"><label class="form-label fw-semibold" for="store_address"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STORE_ADDRESS'); ?></label><input type="text" class="form-control" id="store_address" name="store_address" value="<?php echo $this->escape($p->get('store_address', '')); ?>"></div>
                        <div class="col-md-5"><label class="form-label fw-semibold" for="store_city"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STORE_CITY'); ?></label><input type="text" class="form-control" id="store_city" name="store_city" value="<?php echo $this->escape($p->get('store_city', '')); ?>"></div>
                        <div class="col-md-3"><label class="form-label fw-semibold" for="store_state"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STORE_STATE'); ?></label><input type="text" class="form-control" id="store_state" name="store_state" value="<?php echo $this->escape($p->get('store_state', '')); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold" for="store_postal_code"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STORE_POSTAL'); ?></label><input type="text" class="form-control" id="store_postal_code" name="store_postal_code" value="<?php echo $this->escape($p->get('store_postal_code', '')); ?>"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold" for="store_country"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STORE_COUNTRY'); ?></label><input type="text" class="form-control" id="store_country" name="store_country" value="<?php echo $this->escape($p->get('store_country', 'US')); ?>" maxlength="2"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="currency"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_CURRENCY'); ?></label>
                        <select class="form-select" id="currency" name="currency">
                            <?php foreach (['USD' => 'USD — US Dollar', 'EUR' => 'EUR — Euro', 'GBP' => 'GBP — British Pound', 'CAD' => 'CAD — Canadian Dollar', 'AUD' => 'AUD — Australian Dollar'] as $code => $label) : ?>
                                <option value="<?php echo $code; ?>"<?php echo $p->get('currency', 'USD') === $code ? ' selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="tax_rate"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_TAX_RATE'); ?></label>
                        <input type="number" class="form-control" id="tax_rate" name="tax_rate" value="<?php echo (float) $p->get('tax_rate', 0); ?>" min="0" max="100" step="0.01">
                        <div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="prices_include_tax" name="prices_include_tax" value="1"<?php echo (int) $p->get('prices_include_tax', 0) === 1 ? ' checked' : ''; ?>><label class="form-check-label" for="prices_include_tax"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_PRICES_INCLUDE_TAX'); ?></label></div>
                        <textarea class="form-control mt-2" id="tax_rules" name="tax_rules" rows="4" placeholder="US=0&#10;US-TX=8.25"><?php echo $this->escape($p->get('tax_rules', '')); ?></textarea>
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_TAX_RATE_NOTE'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="products_per_page"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_PRODUCTS_PER_PAGE'); ?></label>
                        <input type="number" class="form-control" id="products_per_page" name="products_per_page" value="<?php echo (int) $p->get('products_per_page', 12); ?>" min="1" step="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="notify_email"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_NOTIFY_EMAIL'); ?></label>
                        <input type="email" class="form-control" id="notify_email" name="notify_email" value="<?php echo $this->escape($p->get('notify_email', '')); ?>">
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_NOTIFY_EMAIL_NOTE'); ?></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_CUSTOMER_CONFIRMATION'); ?></label><div class="form-check"><input type="hidden" name="send_customer_confirmation" value="0"><input class="form-check-input" type="checkbox" id="send_customer_confirmation" name="send_customer_confirmation" value="1"<?php echo (int) $p->get('send_customer_confirmation', 1) === 1 ? ' checked' : ''; ?>><label class="form-check-label" for="send_customer_confirmation"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_CUSTOMER_CONFIRMATION_NOTE'); ?></label></div></div>
                        <div class="col-md-4"><label class="form-label fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_GUEST_CHECKOUT'); ?></label><div class="form-check"><input type="hidden" name="guest_checkout" value="0"><input class="form-check-input" type="checkbox" id="guest_checkout" name="guest_checkout" value="1"<?php echo (int) $p->get('guest_checkout', 1) === 1 ? ' checked' : ''; ?>><label class="form-check-label" for="guest_checkout"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_GUEST_CHECKOUT_NOTE'); ?></label></div></div>
                        <div class="col-md-4"><label class="form-label fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_REQUIRE_PHONE'); ?></label><div class="form-check"><input class="form-check-input" type="checkbox" id="require_phone" name="require_phone" value="1"<?php echo (int) $p->get('require_phone', 0) === 1 ? ' checked' : ''; ?>><label class="form-check-label" for="require_phone"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_REQUIRE_PHONE_NOTE'); ?></label></div></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="terms_url"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_TERMS_URL'); ?></label>
                        <input type="url" class="form-control" id="terms_url" name="terms_url" value="<?php echo $this->escape($p->get('terms_url', '')); ?>" placeholder="https://example.org/terms">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_REQUIRE_TERMS'); ?></label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="require_terms" name="require_terms" value="1"<?php echo (int) $p->get('require_terms', 1) === 1 ? ' checked' : ''; ?>>
                            <label class="form-check-label" for="require_terms"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_REQUIRE_TERMS_NOTE'); ?></label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pending_order_expiry_minutes"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_PENDING_EXPIRY'); ?></label>
                        <input type="number" class="form-control" id="pending_order_expiry_minutes" name="pending_order_expiry_minutes" value="<?php echo (int) $p->get('pending_order_expiry_minutes', 60); ?>" min="15" max="10080">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="low_stock_threshold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_LOW_STOCK'); ?></label>
                        <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" value="<?php echo (int) $p->get('low_stock_threshold', 5); ?>" min="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sanctuaryshop-settings', 'payment', Text::_('COM_SANCTUARYSHOP_TAB_PAYMENT')); ?>
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-primary mb-1">
                <div class="card-header bg-primary text-white"><h3 class="card-title mb-0"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_PAYMENT_PROVIDER'); ?></h3></div>
                <div class="card-body">
                    <div class="row align-items-end g-3">
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold" for="payment_provider"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_PAYMENT_PROVIDER'); ?></label>
                            <select class="form-select" id="payment_provider" name="payment_provider" aria-describedby="payment-provider-note">
                                <?php foreach (['square' => Text::_('COM_SANCTUARYSHOP_CONFIG_PROVIDER_SQUARE'), 'stripe' => Text::_('COM_SANCTUARYSHOP_CONFIG_PROVIDER_STRIPE'), 'authorize_net' => Text::_('COM_SANCTUARYSHOP_CONFIG_PROVIDER_AUTHORIZE_NET')] as $value => $label) : ?>
                                    <option value="<?php echo $value; ?>"<?php echo $p->get('payment_provider', 'square') === $value ? ' selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-6"><div id="payment-provider-note" class="form-text mb-2"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_PROVIDER_NOTE'); ?></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12" data-payment-provider-panel="square">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_LABEL'); ?></h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold" for="square_environment"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ENV'); ?></label><select class="form-select" id="square_environment" name="square_environment"><option value="sandbox"<?php echo $p->get('square_environment', 'sandbox') === 'sandbox' ? ' selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ENV_SANDBOX'); ?></option><option value="production"<?php echo $p->get('square_environment') === 'production' ? ' selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ENV_PRODUCTION'); ?></option></select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold" for="square_application_id"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_APP_ID'); ?></label><input type="text" class="form-control" id="square_application_id" name="square_application_id" value="<?php echo $this->escape($p->get('square_application_id', '')); ?>" autocomplete="off"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold" for="square_access_token"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ACCESS_TOKEN'); ?></label><input type="password" class="form-control" id="square_access_token" name="square_access_token" value="<?php echo $this->escape($p->get('square_access_token', '')); ?>" autocomplete="new-password"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold" for="square_location_id"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_LOCATION_ID'); ?></label><input type="text" class="form-control" id="square_location_id" name="square_location_id" value="<?php echo $this->escape($p->get('square_location_id', '')); ?>" autocomplete="off"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold" for="square_webhook_signature_key"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_WEBHOOK_KEY'); ?></label><input type="password" class="form-control" id="square_webhook_signature_key" name="square_webhook_signature_key" value="<?php echo $this->escape($p->get('square_webhook_signature_key', '')); ?>" autocomplete="new-password"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold" for="square_webhook_url"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_WEBHOOK_URL'); ?></label><input type="url" class="form-control" id="square_webhook_url" name="square_webhook_url" value="<?php echo $this->escape($p->get('square_webhook_url', '')); ?>" placeholder="https://example.org/index.php?option=com_sanctuaryshop&amp;task=webhook.square"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12" data-payment-provider-panel="stripe" hidden>
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STRIPE_LABEL'); ?></h3></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold" for="stripe_publishable_key"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STRIPE_PUBLISHABLE_KEY'); ?></label><input type="text" class="form-control" id="stripe_publishable_key" name="stripe_publishable_key" value="<?php echo $this->escape($p->get('stripe_publishable_key', '')); ?>" autocomplete="off"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="stripe_secret_key"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STRIPE_SECRET_KEY'); ?></label><input type="password" class="form-control" id="stripe_secret_key" name="stripe_secret_key" value="<?php echo $this->escape($p->get('stripe_secret_key', '')); ?>" autocomplete="new-password"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="stripe_webhook_secret"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_STRIPE_WEBHOOK_SECRET'); ?></label><input type="password" class="form-control" id="stripe_webhook_secret" name="stripe_webhook_secret" value="<?php echo $this->escape($p->get('stripe_webhook_secret', '')); ?>" autocomplete="new-password"></div>
                </div></div>
            </div>
        </div>

        <div class="col-12" data-payment-provider-panel="authorize_net" hidden>
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_AUTHORIZE_LABEL'); ?></h3></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold" for="authorize_environment"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_AUTHORIZE_ENV'); ?></label><select class="form-select" id="authorize_environment" name="authorize_environment"><option value="sandbox"<?php echo $p->get('authorize_environment', 'sandbox') === 'sandbox' ? ' selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_ENV_SANDBOX'); ?></option><option value="production"<?php echo $p->get('authorize_environment') === 'production' ? ' selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_ENV_PRODUCTION'); ?></option></select></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="authorize_api_login_id"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_AUTHORIZE_LOGIN'); ?></label><input type="text" class="form-control" id="authorize_api_login_id" name="authorize_api_login_id" value="<?php echo $this->escape($p->get('authorize_api_login_id', '')); ?>" autocomplete="off"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="authorize_transaction_key"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_AUTHORIZE_TRANSACTION_KEY'); ?></label><input type="password" class="form-control" id="authorize_transaction_key" name="authorize_transaction_key" value="<?php echo $this->escape($p->get('authorize_transaction_key', '')); ?>" autocomplete="new-password"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold" for="authorize_public_client_key"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_AUTHORIZE_PUBLIC_KEY'); ?></label><input type="text" class="form-control" id="authorize_public_client_key" name="authorize_public_client_key" value="<?php echo $this->escape($p->get('authorize_public_client_key', '')); ?>" autocomplete="off"></div>
                </div></div>
            </div>
        </div>
    </div>
    <script>
    (() => {
        const selector = document.getElementById('payment_provider');
        const panels = document.querySelectorAll('[data-payment-provider-panel]');
        const updatePanels = () => panels.forEach((panel) => { panel.hidden = panel.dataset.paymentProviderPanel !== selector.value; });
        if (selector) { selector.addEventListener('change', updatePanels); updatePanels(); }
    })();
    </script>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sanctuaryshop-settings', 'shipping', Text::_('COM_SANCTUARYSHOP_TAB_SHIPPING')); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SHIPPING_ENABLED'); ?></label>
                        <div class="form-check"><input type="hidden" name="shipping_enabled" value="0"><input class="form-check-input" type="checkbox" id="shipping_enabled" name="shipping_enabled" value="1"<?php echo (int) $p->get('shipping_enabled', 1) === 1 ? ' checked' : ''; ?>><label class="form-check-label" for="shipping_enabled"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SHIPPING_ENABLED_NOTE'); ?></label></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shipping_flat_rate"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FLAT_RATE'); ?></label>
                        <input type="number" class="form-control" id="shipping_flat_rate" name="shipping_flat_rate" value="<?php echo (float) $p->get('shipping_flat_rate', 0); ?>" min="0" step="0.01">
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FLAT_RATE_NOTE'); ?></div>
                    </div>
                    <div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label fw-semibold" for="shipping_handling_fee"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_HANDLING_FEE'); ?></label><input type="number" class="form-control" id="shipping_handling_fee" name="shipping_handling_fee" value="<?php echo (float) $p->get('shipping_handling_fee', 0); ?>" min="0" step="0.01"></div><div class="col-md-6"><label class="form-label fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FREE_AFTER_DISCOUNT'); ?></label><div class="form-check"><input class="form-check-input" type="checkbox" id="shipping_free_after_discount" name="shipping_free_after_discount" value="1"<?php echo (int) $p->get('shipping_free_after_discount', 0) === 1 ? ' checked' : ''; ?>><label class="form-check-label" for="shipping_free_after_discount"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FREE_AFTER_DISCOUNT_NOTE'); ?></label></div></div></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shipping_free_threshold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FREE_THRESHOLD'); ?></label>
                        <input type="number" class="form-control" id="shipping_free_threshold" name="shipping_free_threshold" value="<?php echo (float) $p->get('shipping_free_threshold', 0); ?>" min="0" step="0.01">
                        <textarea class="form-control mt-2" id="shipping_rules" name="shipping_rules" rows="4" placeholder="US=5&#10;US-TX=8"><?php echo $this->escape($p->get('shipping_rules', '')); ?></textarea>
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FREE_THRESHOLD_NOTE'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sanctuaryshop-settings', 'inventory', Text::_('COM_SANCTUARYSHOP_TAB_INVENTORY')); ?>
    <div class="row"><div class="col-md-8"><div class="card mb-3"><div class="card-body"><div class="form-check"><input class="form-check-input" type="checkbox" id="allow_backorders" name="allow_backorders" value="1"<?php echo (int) $p->get('allow_backorders', 0) === 1 ? ' checked' : ''; ?>><label class="form-check-label" for="allow_backorders"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_ALLOW_BACKORDERS'); ?></label></div></div></div></div></div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sanctuaryshop-settings', 'downloads', Text::_('COM_SANCTUARYSHOP_TAB_DOWNLOADS')); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="download_path"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_DOWNLOAD_PATH'); ?></label>
                        <input type="text" class="form-control" id="download_path" name="download_path" value="<?php echo $this->escape($p->get('download_path', '')); ?>">
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_DOWNLOAD_PATH_NOTE'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="download_expiry_days"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_DOWNLOAD_EXPIRY'); ?></label>
                        <input type="number" class="form-control" id="download_expiry_days" name="download_expiry_days" value="<?php echo (int) $p->get('download_expiry_days', 30); ?>" min="0" step="1">
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_DOWNLOAD_EXPIRY_NOTE'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="download_max_per_token"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_DOWNLOAD_MAX'); ?></label>
                        <input type="number" class="form-control" id="download_max_per_token" name="download_max_per_token" value="<?php echo (int) $p->get('download_max_per_token', 5); ?>" min="1" step="1">
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_DOWNLOAD_MAX_NOTE'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
