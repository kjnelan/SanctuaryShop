<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=downloads'); ?>" method="post" name="adminForm" id="adminForm">

    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" name="filter[search]" class="form-control" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>">
        </div>
        <div class="col-md-3">
            <select name="filter[revoked]" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('COM_SANCTUARYSHOP_FILTER_ALL_TOKENS'); ?></option>
                <option value="0" <?php echo $this->state->get('filter.revoked') === '0' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_FILTER_ACTIVE'); ?></option>
                <option value="1" <?php echo $this->state->get('filter.revoked') === '1' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_FILTER_REVOKED'); ?></option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-secondary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=downloads'); ?>" class="btn btn-outline-secondary"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th width="20"><input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_TOKEN'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_ORDER_ID'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_USER'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_DOWNLOADS'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_EXPIRES'); ?></th>
                <th><?php echo Text::_('JSTATUS'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($this->items)) : ?>
            <tr><td colspan="9" class="text-center text-muted"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php else : ?>
            <?php foreach ($this->items as $i => $item) : ?>
            <tr>
                <td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td><code title="<?php echo $this->escape($item->token); ?>"><?php echo substr($item->token, 0, 12) . '…'; ?></code></td>
                <td><?php echo $this->escape($item->product_title); ?></td>
                <td><?php echo $this->escape($item->file_label ?: $item->filename); ?></td>
                <td><a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=order&id=' . (int) $item->order_id); ?>">#<?php echo str_pad($item->order_id, 5, '0', STR_PAD_LEFT); ?></a></td>
                <td><?php echo $this->escape($item->user_name ?: Text::_('JGUEST')); ?></td>
                <td><?php echo (int) $item->download_count; ?> / <?php echo (int) $item->max_downloads; ?></td>
                <td><?php echo $item->expires ? HTMLHelper::_('date', $item->expires, Text::_('DATE_FORMAT_LC2')) : '&mdash;'; ?></td>
                <td>
                    <?php if ($item->revoked) : ?>
                        <span class="badge bg-danger"><?php echo Text::_('COM_SANCTUARYSHOP_STATUS_REVOKED'); ?></span>
                    <?php elseif ($item->expires && strtotime($item->expires) < time()) : ?>
                        <span class="badge bg-warning text-dark"><?php echo Text::_('COM_SANCTUARYSHOP_STATUS_EXPIRED'); ?></span>
                    <?php else : ?>
                        <span class="badge bg-success"><?php echo Text::_('COM_SANCTUARYSHOP_STATUS_ACTIVE'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
