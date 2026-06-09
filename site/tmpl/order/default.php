<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$sym         = $currencyMap[$this->order->currency] ?? $this->order->currency . ' ';

$statusMap = ['pending' => 'secondary', 'processing' => 'primary', 'completed' => 'success', 'cancelled' => 'danger', 'refunded' => 'warning'];
$badge     = $statusMap[$this->order->status] ?? 'secondary';
?>
<div class="com-sanctuaryshop-order">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <?php echo Text::_('COM_SANCTUARYSHOP_ORDER'); ?> #<?php echo str_pad((int) $this->order->id, 5, '0', STR_PAD_LEFT); ?>
        </h1>
        <span class="badge bg-<?php echo $badge; ?> fs-6"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_' . strtoupper($this->order->status)); ?></span>
    </div>

    <p class="text-muted">
        <?php echo Text::_('COM_SANCTUARYSHOP_FIELD_DATE'); ?>: <?php echo HTMLHelper::_('date', $this->order->created, Text::_('DATE_FORMAT_LC2')); ?>
    </p>

    <!-- Items -->
    <div class="card mb-4">
        <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_ITEMS'); ?></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SANCTUARYSHOP_QUANTITY'); ?></th>
                        <th class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_UNIT_PRICE'); ?></th>
                        <th class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_LINE_TOTAL'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $item) : ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo $this->escape($item->title); ?></div>
                            <?php if ($item->sku) : ?>
                                <small class="text-muted">SKU: <?php echo $this->escape($item->sku); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo (int) $item->quantity; ?></td>
                        <td class="text-end"><?php echo $sym . number_format($item->unit_price, 2); ?></td>
                        <td class="text-end"><?php echo $sym . number_format($item->total_price, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="3" class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_SUBTOTAL'); ?></td><td class="text-end"><?php echo $sym . number_format($this->order->subtotal, 2); ?></td></tr>
                    <?php if ((float) $this->order->discount > 0) : ?>
                    <tr><td colspan="3" class="text-end text-danger"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_DISCOUNT'); ?></td><td class="text-end text-danger">-<?php echo $sym . number_format($this->order->discount, 2); ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) $this->order->tax > 0) : ?>
                    <tr><td colspan="3" class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_TAX'); ?></td><td class="text-end"><?php echo $sym . number_format($this->order->tax, 2); ?></td></tr>
                    <?php endif; ?>
                    <?php if ((float) $this->order->shipping > 0) : ?>
                    <tr><td colspan="3" class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_SHIPPING'); ?></td><td class="text-end"><?php echo $sym . number_format($this->order->shipping, 2); ?></td></tr>
                    <?php endif; ?>
                    <tr class="fw-bold"><td colspan="3" class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_TOTAL'); ?></td><td class="text-end fs-5"><?php echo $sym . number_format($this->order->total, 2); ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Downloads -->
    <?php if (!empty($this->downloads)) : ?>
    <div class="card mb-4 border-info">
        <div class="card-header bg-info bg-opacity-10 fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_DOWNLOADS'); ?></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT'); ?></th>
                        <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE'); ?></th>
                        <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILESIZE'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->downloads as $dl) : ?>
                    <tr>
                        <td><?php echo $this->escape($dl->product_title); ?></td>
                        <td><?php echo $this->escape($dl->file_label ?: basename($dl->filename)); ?></td>
                        <td><?php echo $dl->filesize > 0 ? number_format($dl->filesize / 1048576, 1) . ' MB' : '—'; ?></td>
                        <td>
                            <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=download.get&token=' . urlencode($dl->token)); ?>"
                               class="btn btn-sm btn-primary">
                                <?php echo Text::_('COM_SANCTUARYSHOP_DOWNLOAD'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Addresses -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_BILLING_INFO'); ?></div>
                <div class="card-body">
                    <p class="mb-1"><?php echo $this->escape($this->order->billing_name); ?></p>
                    <p class="mb-1"><?php echo $this->escape($this->order->billing_email); ?></p>
                    <?php
                    $addr = json_decode($this->order->billing_address, true);
                    if (!empty($addr)) : ?>
                    <p class="mb-0">
                        <?php echo $this->escape($addr['address_line_1'] ?? ''); ?><br>
                        <?php if (!empty($addr['address_line_2'])) : ?>
                            <?php echo $this->escape($addr['address_line_2']); ?><br>
                        <?php endif; ?>
                        <?php echo $this->escape(($addr['locality'] ?? '') . ', ' . ($addr['administrative_district_level_1'] ?? '') . ' ' . ($addr['postal_code'] ?? '')); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_SHIPPING_INFO'); ?></div>
                <div class="card-body">
                    <?php
                    $addr = json_decode($this->order->shipping_address, true);
                    if (!empty($addr)) : ?>
                    <p class="mb-0">
                        <?php echo $this->escape($addr['address_line_1'] ?? ''); ?><br>
                        <?php if (!empty($addr['address_line_2'])) : ?>
                            <?php echo $this->escape($addr['address_line_2']); ?><br>
                        <?php endif; ?>
                        <?php echo $this->escape(($addr['locality'] ?? '') . ', ' . ($addr['administrative_district_level_1'] ?? '') . ' ' . ($addr['postal_code'] ?? '')); ?>
                    </p>
                    <?php else : ?>
                    <p class="text-muted mb-0"><?php echo Text::_('COM_SANCTUARYSHOP_SAME_AS_BILLING'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=account'); ?>" class="btn btn-outline-secondary">
            &larr; <?php echo Text::_('COM_SANCTUARYSHOP_BACK_TO_ORDERS'); ?>
        </a>
    </div>
</div>
