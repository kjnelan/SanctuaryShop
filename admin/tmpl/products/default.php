<?php defined('_JEXEC') or die; ?>
<form action="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo \Joomla\CMS\Layout\LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <table class="table table-striped" id="productList">
                    <thead>
                        <tr>
                            <th class="w-1 text-center"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('grid.checkall'); ?></th>
                            <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $this->listDirn, $this->listOrder); ?></th>
                            <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'COM_SANCTUARYSHOP_FIELD_CATEGORY', 'category_title', $this->listDirn, $this->listOrder); ?></th>
                            <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'COM_SANCTUARYSHOP_FIELD_PRICE', 'a.price', $this->listDirn, $this->listOrder); ?></th>
                            <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'COM_SANCTUARYSHOP_FIELD_STOCK', 'a.stock', $this->listDirn, $this->listOrder); ?></th>
                            <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $this->listDirn, $this->listOrder); ?></th>
                            <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                            <td>
                                <a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&task=product.edit&id=' . $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                                <div class="small text-muted"><?php echo 'SKU: ' . $this->escape($item->sku); ?></div>
                            </td>
                            <td><?php echo $this->escape($item->category_title ?? '—'); ?></td>
                            <td>$<?php echo number_format($item->price, 2); ?></td>
                            <td><?php echo (int) $item->stock; ?></td>
                            <td><?php echo \Joomla\CMS\HTML\HTMLHelper::_('jgrid.published', $item->state, $i, 'products.', true, 'cb'); ?></td>
                            <td><?php echo $item->id; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php echo $this->pagination->getListFooter(); ?>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo \Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>
</form>
