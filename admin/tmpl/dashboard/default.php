<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$sym         = $currencyMap[$this->currency] ?? $this->currency . ' ';
?>
<div class="com-sanctuaryshop-dashboard">
    <!-- Stats cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-primary"><?php echo $sym . number_format($this->totals->revenue, 2); ?></div>
                    <div class="text-muted small"><?php echo Text::_('COM_SANCTUARYSHOP_DASHBOARD_REVENUE'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-success"><?php echo (int) $this->totals->totalOrders; ?></div>
                    <div class="text-muted small"><?php echo Text::_('COM_SANCTUARYSHOP_DASHBOARD_TOTAL_ORDERS'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-warning"><?php echo (int) $this->totals->pendingOrders; ?></div>
                    <div class="text-muted small"><?php echo Text::_('COM_SANCTUARYSHOP_DASHBOARD_PENDING_ORDERS'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-info"><?php echo (int) $this->totals->totalProducts; ?></div>
                    <div class="text-muted small"><?php echo Text::_('COM_SANCTUARYSHOP_DASHBOARD_TOTAL_PRODUCTS'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent orders -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_DASHBOARD_RECENT_ORDERS'); ?></span>
                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=orders'); ?>" class="btn btn-sm btn-outline-secondary">
                        <?php echo Text::_('COM_SANCTUARYSHOP_VIEW_ALL'); ?>
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_CUSTOMER'); ?></th>
                                <th class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_TOTAL'); ?></th>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_STATUS'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->recentOrders as $o) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=order.edit&id=' . (int) $o->id); ?>">
                                        #<?php echo str_pad($o->id, 5, '0', STR_PAD_LEFT); ?>
                                    </a>
                                </td>
                                <td><?php echo $this->escape($o->billing_name); ?></td>
                                <td class="text-end"><?php echo $sym . number_format($o->total, 2); ?></td>
                                <td><span class="badge bg-<?php echo match($o->status){'completed'=>'success','pending'=>'warning','processing'=>'primary','cancelled'=>'secondary','refunded'=>'danger',default=>'secondary'}; ?>"><?php echo ucfirst($o->status); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($this->recentOrders)) : ?>
                            <tr><td colspan="4" class="text-center text-muted py-3"><?php echo Text::_('COM_SANCTUARYSHOP_NO_ORDERS'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top products -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_DASHBOARD_TOP_PRODUCTS'); ?></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT'); ?></th>
                                <th class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_QUANTITY'); ?></th>
                                <th class="text-end"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_REVENUE'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->topProducts as $p) : ?>
                            <tr>
                                <td><?php echo $this->escape($p->title); ?></td>
                                <td class="text-end"><?php echo (int) $p->total_qty; ?></td>
                                <td class="text-end"><?php echo $sym . number_format($p->total_revenue, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($this->topProducts)) : ?>
                            <tr><td colspan="3" class="text-center text-muted py-3"><?php echo Text::_('COM_SANCTUARYSHOP_NO_SALES_YET'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
