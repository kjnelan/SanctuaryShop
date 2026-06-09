<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('dropdown', 'dropdown');
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=coupons'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo HTMLHelper::_('searchtools.form', ['selector' => 'adminForm'], []); ?>
                <table class="table table-striped" id="couponList">
                    <thead>
                        <tr>
                            <th style="width:1%"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                            <th style="width:5%"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SANCTUARYSHOP_COUPON_CODE', 'a.code', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                            <th style="width:10%"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SANCTUARYSHOP_COUPON_TYPE', 'a.type', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                            <th style="width:10%"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SANCTUARYSHOP_COUPON_VALUE', 'a.value', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                            <th style="width:10%"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_USED'); ?></th>
                            <th style="width:15%"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SANCTUARYSHOP_COUPON_EXPIRES', 'a.expires', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                            <th style="width:5%"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->items as $i => $item) : ?>
                            <tr>
                                <td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                                <td><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'coupons.', true); ?></td>
                                <td>
                                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=coupon.edit&id=' . (int) $item->id); ?>">
                                        <?php echo $this->escape($item->code); ?>
                                    </a>
                                </td>
                                <td><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_TYPE_' . strtoupper($item->type)); ?></td>
                                <td><?php echo ($item->type === 'percentage') ? $item->value . '%' : number_format($item->value, 2); ?></td>
                                <td>
                                    <?php echo (int) $item->used_count; ?>
                                    <?php if ($item->usage_limit > 0) : ?>
                                        / <?php echo (int) $item->usage_limit; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ($item->expires && $item->expires !== '0000-00-00 00:00:00') ? HTMLHelper::_('date', $item->expires, Text::_('DATE_FORMAT_LC2')) : '-'; ?></td>
                                <td><?php echo (int) $item->id; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($this->items)) : ?>
                            <tr><td colspan="8"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php echo $this->pagination->getListFooter(); ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
