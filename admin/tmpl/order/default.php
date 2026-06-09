<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=order.edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Order Items</h3></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->orderItems as $lineItem) : ?>
                            <tr>
                                <td><?php echo $this->escape($lineItem->title); ?></td>
                                <td><?php echo $this->escape($lineItem->sku); ?></td>
                                <td class="text-end"><?php echo (int) $lineItem->quantity; ?></td>
                                <td class="text-end">$<?php echo number_format($lineItem->unit_price, 2); ?></td>
                                <td class="text-end">$<?php echo number_format($lineItem->total_price, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="4" class="text-end fw-bold">Order Total</td><td class="text-end fw-bold">$<?php echo number_format($this->item->total, 2); ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Billing Address</h3></div>
                <div class="card-body">
                    <pre><?php echo $this->escape($this->item->billing_address); ?></pre>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <?php echo $this->form->renderField('status'); ?>
                    <dl class="row">
                        <dt class="col-6">Square Order ID</dt>
                        <dd class="col-6 small"><?php echo $this->escape($this->item->square_order_id ?? '—'); ?></dd>
                        <dt class="col-6">Payment ID</dt>
                        <dd class="col-6 small"><?php echo $this->escape($this->item->payment_id ?? '—'); ?></dd>
                        <dt class="col-6">Created</dt>
                        <dd class="col-6"><?php echo HTMLHelper::_('date', $this->item->created, 'Y-m-d H:i'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
