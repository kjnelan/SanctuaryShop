<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <input type="search" name="filter[search]" class="form-control"
                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                       placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
            </div>
        </div>
        <div class="col-md-3">
            <select name="filter[category]" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('COM_SANCTUARYSHOP_SELECT_CATEGORY'); ?></option>
                <?php foreach ($this->categories as $cat) : ?>
                <option value="<?php echo (int) $cat->id; ?>" <?php echo $this->state->get('filter.category') == $cat->id ? 'selected' : ''; ?>>
                    <?php echo $this->escape($cat->title); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <table class="table table-striped" id="productList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT_TYPE'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_CATEGORY'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRICE'); ?></th>
                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_STOCK'); ?></th>
                <th><?php echo Text::_('JSTATUS'); ?></th>
                <th><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <tr>
                <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=product.edit&id=' . (int) $item->id); ?>">
                        <?php echo $this->escape($item->title); ?>
                    </a>
                    <?php if ($item->sku) : ?><div class="small text-muted">SKU: <?php echo $this->escape($item->sku); ?></div><?php endif; ?>
                </td>
                <td>
                    <?php
                    $typeBadge = ['physical'=>'secondary','digital'=>'info','subscription'=>'primary','service'=>'success'];
                    $typeBadgeClass = $typeBadge[$item->product_type ?? 'physical'] ?? 'secondary';
                    $typeKey = 'COM_SANCTUARYSHOP_PRODUCT_TYPE_' . strtoupper($item->product_type ?? 'PHYSICAL');
                    ?>
                    <span class="badge bg-<?php echo $typeBadgeClass; ?>"><?php echo Text::_($typeKey); ?></span>
                </td>
                <td><?php echo $this->escape($item->category_title ?? '—'); ?></td>
                <td>
                    <?php echo $this->currencySym; ?><?php echo number_format($item->price, 2); ?>
                    <?php if ($item->sale_price) : ?>
                        <span class="badge bg-danger ms-1">Sale: <?php echo $this->currencySym . number_format($item->sale_price, 2); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo (int) $item->stock; ?></td>
                <td><?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'products.', true, 'cb'); ?></td>
                <td><?php echo (int) $item->id; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($this->items)) : ?>
            <tr><td colspan="8" class="text-center text-muted py-4"><?php echo Text::_('COM_SANCTUARYSHOP_NO_PRODUCTS_FOUND'); ?> <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=product.add'); ?>"><?php echo Text::_('COM_SANCTUARYSHOP_ADD_FIRST_PRODUCT'); ?></a>.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
