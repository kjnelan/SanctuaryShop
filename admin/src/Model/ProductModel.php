<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

class ProductModel extends AdminModel
{
    public $typeAlias = 'com_sanctuaryshop.product';

    protected function canDelete($record): bool
    {
        if (!empty($record->id)) {
            return Factory::getApplication()->getIdentity()->authorise('core.delete', 'com_sanctuaryshop.product.' . (int) $record->id);
        }
        return false;
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_sanctuaryshop.product', 'product', ['control' => 'jform', 'load_data' => $loadData]);
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_sanctuaryshop.edit.product.data', []);
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    public function getTable($name = 'Product', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
