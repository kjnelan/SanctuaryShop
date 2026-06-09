<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="com-sanctuaryshop-account">
    <h2><?php echo Text::_('COM_SANCTUARYSHOP_MY_ORDERS'); ?></h2>

    <?php if (empty($this->orders)) : ?>
        <p class="text-muted"><?php echo Text::_('COM_SANCTUARYSHOP_NO_ORDERS'); ?></p>
    <?php else : ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_ORDER_NUMBER'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_DATE'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_STATUS'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_TOTAL'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->orders as $order) : ?>
            <tr>
                <td>#<?php echo str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo HTMLHelper::_('date', $order->created, Text::_('DATE_FORMAT_LC2')); ?></td>
                <td>
                    <?php
                    $statusMap = ['pending' => 'secondary', 'processing' => 'primary', 'completed' => 'success', 'cancelled' => 'danger', 'refunded' => 'warning'];
                    $badge     = $statusMap[$order->status] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $badge; ?>"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_STATUS_' . strtoupper($order->status)); ?></span>
                </td>
                <td><?php echo $order->currency; ?> <?php echo number_format($order->total, 2); ?></td>
                <td>
                    <?php if ($order->status === 'completed') : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=order&id=' . (int) $order->id); ?>" class="btn btn-sm btn-outline-primary"><?php echo Text::_('COM_SANCTUARYSHOP_VIEW_ORDER'); ?></a>
                        <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=downloads'); ?>" class="btn btn-sm btn-outline-info"><?php echo Text::_('COM_SANCTUARYSHOP_VIEW_DOWNLOADS'); ?></a>
                    <?php else : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=order&id=' . (int) $order->id); ?>" class="btn btn-sm btn-outline-secondary"><?php echo Text::_('COM_SANCTUARYSHOP_VIEW_ORDER'); ?></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
