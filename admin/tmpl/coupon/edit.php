<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=coupon&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="coupon-form" class="form-validate">
    <div class="form-horizontal">
        <fieldset>
            <legend><?php echo Text::_('COM_SANCTUARYSHOP_COUPON_DETAILS'); ?></legend>
            <?php echo $this->form->renderFieldset('details'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
