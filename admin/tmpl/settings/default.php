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
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sanctuaryshop-settings', 'payment', Text::_('COM_SANCTUARYSHOP_TAB_PAYMENT')); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_LABEL'); ?></h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="square_environment"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ENV'); ?></label>
                        <select class="form-select" id="square_environment" name="square_environment">
                            <option value="sandbox"<?php echo $p->get('square_environment', 'sandbox') === 'sandbox' ? ' selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ENV_SANDBOX'); ?></option>
                            <option value="production"<?php echo $p->get('square_environment') === 'production' ? ' selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ENV_PRODUCTION'); ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="square_application_id"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_APP_ID'); ?></label>
                        <input type="text" class="form-control" id="square_application_id" name="square_application_id" value="<?php echo $this->escape($p->get('square_application_id', '')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="square_access_token"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_ACCESS_TOKEN'); ?></label>
                        <input type="password" class="form-control" id="square_access_token" name="square_access_token" value="<?php echo $this->escape($p->get('square_access_token', '')); ?>" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="square_location_id"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_LOCATION_ID'); ?></label>
                        <input type="text" class="form-control" id="square_location_id" name="square_location_id" value="<?php echo $this->escape($p->get('square_location_id', '')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="square_webhook_signature_key"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_WEBHOOK_KEY'); ?></label>
                        <input type="password" class="form-control" id="square_webhook_signature_key" name="square_webhook_signature_key" value="<?php echo $this->escape($p->get('square_webhook_signature_key', '')); ?>" autocomplete="new-password">
                        <label class="form-label fw-semibold mt-3" for="square_webhook_url"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_SQUARE_WEBHOOK_URL'); ?></label>
                        <input type="url" class="form-control" id="square_webhook_url" name="square_webhook_url" value="<?php echo $this->escape($p->get('square_webhook_url', '')); ?>" placeholder="https://example.org/index.php?option=com_sanctuaryshop&amp;task=webhook.square">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sanctuaryshop-settings', 'shipping', Text::_('COM_SANCTUARYSHOP_TAB_SHIPPING')); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shipping_flat_rate"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FLAT_RATE'); ?></label>
                        <input type="number" class="form-control" id="shipping_flat_rate" name="shipping_flat_rate" value="<?php echo (float) $p->get('shipping_flat_rate', 0); ?>" min="0" step="0.01">
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FLAT_RATE_NOTE'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="shipping_free_threshold"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FREE_THRESHOLD'); ?></label>
                        <input type="number" class="form-control" id="shipping_free_threshold" name="shipping_free_threshold" value="<?php echo (float) $p->get('shipping_free_threshold', 0); ?>" min="0" step="0.01">
                        <div class="form-text"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIG_FREE_THRESHOLD_NOTE'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
