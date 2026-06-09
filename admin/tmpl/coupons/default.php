<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=coupons'); ?>"
      method="post" name="adminForm" id="adminForm">

    <div class="row mb-3">
        <div class="col-md-5">
            <div class="input-group">
                <input type="search" name="filter[search]" class="form-control"
                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                       placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_COUPON_SEARCH_PLACEHOLDER'); ?>">
                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=coupons'); ?>"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
            </div>
        </div>
        <div class="col-md-3">
            <select name="filter[type]" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_ALL_TYPES'); ?></option>
                <option value="percentage" <?php echo $this->state->get('filter.type') === 'percentage' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_TYPE_PERCENTAGE'); ?></option>
                <option value="fixed"      <?php echo $this->state->get('filter.type') === 'fixed'      ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_TYPE_FIXED'); ?></option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="filter[published]" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <option value="1" <?php echo $this->state->get('filter.published') === '1' ? 'selected' : ''; ?>><?php echo Text::_('JPUBLISHED'); ?></option>
                <option value="0" <?php echo $this->state->get('filter.published') === '0' ? 'selected' : ''; ?>><?php echo Text::_('JUNPUBLISHED'); ?></option>
            </select>
        </div>
    </div>

    <table class="table table-striped" id="couponList">
        <thead>
            <tr>
                <th style="width:1%"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th style="width:5%"><?php echo Text::_('JSTATUS'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_CODE'); ?></th>
                <th style="width:12%"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_TYPE'); ?></th>
                <th style="width:10%"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_VALUE'); ?></th>
                <th style="width:10%"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_USED'); ?></th>
                <th style="width:15%"><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_EXPIRES'); ?></th>
                <th style="width:5%"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <tr>
                <td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'coupons.', true, 'cb'); ?></td>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=coupon.edit&id=' . (int) $item->id); ?>">
                        <strong><?php echo $this->escape($item->code); ?></strong>
                    </a>
                    <?php if ($item->min_subtotal > 0) : ?>
                        <div class="small text-muted"><?php echo Text::sprintf('COM_SANCTUARYSHOP_COUPON_MIN_NOTE', number_format($item->min_subtotal, 2)); ?></div>
                    <?php endif; ?>
                </td>
                <td><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_TYPE_' . strtoupper($item->type)); ?></td>
                <td>
                    <?php if ($item->type === 'percentage') : ?>
                        <?php echo number_format($item->value, 0); ?>%
                    <?php else : ?>
                        <?php echo number_format($item->value, 2); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo (int) $item->used_count; ?>
                    <?php if ($item->usage_limit > 0) : ?>
                        / <?php echo (int) $item->usage_limit; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo ($item->expires && $item->expires !== '0000-00-00 00:00:00')
                        ? HTMLHelper::_('date', $item->expires, Text::_('DATE_FORMAT_LC2'))
                        : '<span class="text-muted">—</span>'; ?>
                </td>
                <td><?php echo (int) $item->id; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($this->items)) : ?>
            <tr><td colspan="8" class="text-center text-muted py-4"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
