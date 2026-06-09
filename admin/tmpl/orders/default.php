<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=orders'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <input type="search" name="filter[search]" class="form-control"
                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                       placeholder="Search by name or email">
                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=orders'); ?>">Clear</a>
            </div>
        </div>
        <div class="col-md-3">
            <select name="filter[status]" class="form-select" onchange="this.form.submit()">
                <option value="">— All Statuses —</option>
                <option value="pending" <?php echo $this->state->get('filter.status') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $this->state->get('filter.status') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="completed" <?php echo $this->state->get('filter.status') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $this->state->get('filter.status') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                <option value="refunded" <?php echo $this->state->get('filter.status') === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
            </select>
        </div>
    </div>

    <table class="table table-striped" id="orderList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th>Order #</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Square Ref</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <?php
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
                <td><?php echo $item->currency; ?> $<?php echo number_format($item->total, 2); ?></td>
                <td><span class="badge bg-<?php echo $statusColour; ?>"><?php echo ucfirst($item->status); ?></span></td>
                <td class="small text-muted"><?php echo $this->escape($item->square_order_id ?? '—'); ?></td>
                <td><?php echo HTMLHelper::_('date', $item->created, 'Y-m-d H:i'); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($this->items)) : ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No orders yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
