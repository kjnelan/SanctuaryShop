<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=orders'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <input type="search" name="filter[search]" class="form-control"
                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                       placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_SEARCH_NAME_EMAIL'); ?>">
                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=orders'); ?>"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
            </div>
        </div>
        <div class="col-md-3">
            <select name="filter[status]" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('COM_SANCTUARYSHOP_SELECT_STATUS'); ?></option>
                <option value="pending" <?php echo $this->state->get('filter.status') === 'pending' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_PENDING'); ?></option>
                <option value="processing" <?php echo $this->state->get('filter.status') === 'processing' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_PROCESSING'); ?></option>
                <option value="completed" <?php echo $this->state->get('filter.status') === 'completed' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_COMPLETED'); ?></option>
                <option value="cancelled" <?php echo $this->state->get('filter.status') === 'cancelled' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_CANCELLED'); ?></option>
                <option value="refunded" <?php echo $this->state->get('filter.status') === 'refunded' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_REFUNDED'); ?></option>
            </select>
        </div>
        <div class="col-md-3 text-md-end mt-2 mt-md-0">
            <a class="btn btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=orders.export'); ?>">
                <?php echo Text::_('COM_SANCTUARYSHOP_EXPORT_ORDERS'); ?>
            </a>
        </div>
    </div>

    <table class="table table-striped" id="orderList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_ORDER_NUMBER'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_CUSTOMER'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_TOTAL'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_STATUS'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_SQUARE_REF'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_DATE'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $sym          = $currencyMap[$item->currency] ?? $item->currency . ' ';
            $statusColour = match($item->status) {
                'completed'  => 'success',
                'processing' => 'primary',
                'pending'    => 'warning',
                'cancelled'  => 'secondary',
                'refunded'   => 'danger',
                default      => 'secondary',
            };
            ?>
            <tr>
                <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=order.edit&id=' . (int) $item->id); ?>">
                        #<?php echo str_pad($item->id, 5, '0', STR_PAD_LEFT); ?>
                    </a>
                </td>
                <td>
                    <?php echo $this->escape($item->billing_name); ?>
                    <div class="small text-muted"><?php echo $this->escape($item->billing_email); ?></div>
                </td>
                <td><?php echo $sym . number_format($item->total, 2); ?></td>
                <td><span class="badge bg-<?php echo $statusColour; ?>"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_' . strtoupper($item->status)); ?></span></td>
                <td class="small text-muted"><?php echo $this->escape($item->square_order_id ?? '—'); ?></td>
                <td><?php echo HTMLHelper::_('date', $item->created, 'Y-m-d H:i'); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($this->items)) : ?>
            <tr><td colspan="7" class="text-center text-muted py-4"><?php echo Text::_('COM_SANCTUARYSHOP_NO_ORDERS'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
