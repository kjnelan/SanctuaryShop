<?php defined('_JEXEC') or die; ?>
<form action="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&view=orders'); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo \Joomla\CMS\Layout\LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

    <table class="table table-striped" id="orderList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('grid.checkall'); ?></th>
                <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'JGLOBAL_NUM', 'a.id', $this->listDirn, $this->listOrder); ?></th>
                <th scope="col">Customer</th>
                <th scope="col">Total</th>
                <th scope="col">Status</th>
                <th scope="col">Payment Ref</th>
                <th scope="col"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $this->listDirn, $this->listOrder); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <tr>
                <td class="text-center"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td>
                    <a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_sanctuaryshop&task=order.edit&id=' . $item->id); ?>">
                        #<?php echo str_pad($item->id, 5, '0', STR_PAD_LEFT); ?>
                    </a>
                </td>
                <td>
                    <?php echo $this->escape($item->billing_name); ?>
                    <div class="small text-muted"><?php echo $this->escape($item->billing_email); ?></div>
                </td>
                <td><?php echo $item->currency; ?> <?php echo number_format($item->total, 2); ?></td>
                <td><span class="badge bg-<?php echo $item->status === 'completed' ? 'success' : ($item->status === 'pending' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($item->status); ?></span></td>
                <td class="small"><?php echo $this->escape($item->square_order_id ?? '—'); ?></td>
                <td><?php echo \Joomla\CMS\HTML\HTMLHelper::_('date', $item->created, 'Y-m-d H:i'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo \Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>
</form>
