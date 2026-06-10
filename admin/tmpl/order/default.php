<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$sym         = $currencyMap[$this->item->currency] ?? $this->item->currency . ' ';
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=order.save&id=' . (int) $this->item->id); ?>"
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
                                <td>
                                    <?php echo $this->escape($lineItem->title); ?>
                                    <?php if (!empty($lineItem->variant_info)) : ?>
                                    <?php $vi = json_decode($lineItem->variant_info, true); ?>
                                    <?php if (is_array($vi)) : ?>
                                    <br><small class="text-muted">
                                        <?php foreach ($vi as $vName => $vSel) : ?>
                                        <?php echo $this->escape($vName) . ': ' . $this->escape($vSel['label'] ?? ''); ?>&nbsp;
                                        <?php endforeach; ?>
                                    </small>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $this->escape($lineItem->sku); ?></td>
                                <td class="text-end"><?php echo (int) $lineItem->quantity; ?></td>
                                <td class="text-end"><?php echo $sym . number_format($lineItem->unit_price, 2); ?></td>
                                <td class="text-end"><?php echo $sym . number_format($lineItem->total_price, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="4" class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_SUBTOTAL'); ?></td><td class="text-end"><?php echo $sym . number_format($this->item->subtotal, 2); ?></td></tr>
                            <?php if ((float) $this->item->discount > 0) : ?>
                            <tr><td colspan="4" class="text-end text-danger"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_DISCOUNT'); ?></td><td class="text-end text-danger">-<?php echo $sym . number_format($this->item->discount, 2); ?></td></tr>
                            <?php endif; ?>
                            <tr><td colspan="4" class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_TAX'); ?></td><td class="text-end"><?php echo $sym . number_format($this->item->tax, 2); ?></td></tr>
                            <?php if ((float) $this->item->shipping > 0) : ?>
                            <tr><td colspan="4" class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_SHIPPING'); ?></td><td class="text-end"><?php echo $sym . number_format($this->item->shipping, 2); ?></td></tr>
                            <?php endif; ?>
                            <tr><td colspan="4" class="text-end fw-bold"><?php echo Text::_('COM_SANCTUARYSHOP_TOTAL'); ?></td><td class="text-end fw-bold"><?php echo $sym . number_format($this->item->total, 2); ?></td></tr>
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
            <!-- Notes -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_NOTES'); ?></h3></div>
                <div class="card-body">
                    <?php echo $this->form->renderField('notes'); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <?php echo $this->form->renderField('status'); ?>
                    <?php echo $this->form->renderField('tracking_number'); ?>
                    <dl class="row mt-3">
                        <dt class="col-6"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_SQUARE_REF'); ?></dt>
                        <dd class="col-6 small">
                            <?php if (!empty($this->item->square_order_id)) : ?>
                                <a href="https://squareup.com/dashboard/orders/<?php echo urlencode($this->item->square_order_id); ?>" target="_blank" rel="noopener">
                                    <?php echo $this->escape($this->item->square_order_id); ?>
                                </a>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </dd>
                        <dt class="col-6"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PAYMENT_ID'); ?></dt>
                        <dd class="col-6 small"><?php echo $this->escape($this->item->payment_id ?? '—'); ?></dd>
                        <dt class="col-6"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_DATE'); ?></dt>
                        <dd class="col-6"><?php echo HTMLHelper::_('date', $this->item->created, 'Y-m-d H:i'); ?></dd>
                        <?php if (!empty($this->item->coupon_code)) : ?>
                        <dt class="col-6"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_CODE'); ?></dt>
                        <dd class="col-6"><?php echo $this->escape($this->item->coupon_code); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <!-- Quick Status Actions -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title fs-6">Quick Actions</h3></div>
                <div class="card-body d-grid gap-2">
                    <?php
                    $quickStatuses = [
                        'processing' => 'COM_SANCTUARYSHOP_MARK_PROCESSING',
                        'shipped'    => 'COM_SANCTUARYSHOP_MARK_SHIPPED',
                        'completed'  => 'COM_SANCTUARYSHOP_MARK_COMPLETED',
                        'cancelled'  => 'COM_SANCTUARYSHOP_MARK_CANCELLED',
                    ];
                    $btnClass = ['processing'=>'btn-info','shipped'=>'btn-warning','completed'=>'btn-success','cancelled'=>'btn-danger'];
                    foreach ($quickStatuses as $s => $lang) :
                    ?>
                    <form method="post" action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=order.updateStatus'); ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
                        <input type="hidden" name="status" value="<?php echo $s; ?>">
                        <input type="hidden" name="tracking_number" value="<?php echo $this->escape($this->item->tracking_number ?? ''); ?>">
                        <?php echo HTMLHelper::_('form.token'); ?>
                        <button type="submit" class="btn btn-sm <?php echo $btnClass[$s]; ?> w-100"><?php echo Text::_($lang); ?></button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
