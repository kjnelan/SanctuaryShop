<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="row">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-body">
                    <?php echo $this->form->renderField('title'); ?>
                    <?php echo $this->form->renderField('alias'); ?>
                    <?php echo $this->form->renderField('description'); ?>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><?php echo 'Pricing & Inventory'; ?></h3></div>
                <div class="card-body">
                    <?php echo $this->form->renderField('price'); ?>
                    <?php echo $this->form->renderField('sale_price'); ?>
                    <?php echo $this->form->renderField('sku'); ?>
                    <?php echo $this->form->renderField('stock'); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <?php echo $this->form->renderField('state'); ?>
                    <?php echo $this->form->renderField('category_id'); ?>
                    <?php echo $this->form->renderField('image'); ?>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
