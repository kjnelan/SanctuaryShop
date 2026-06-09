<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class OrderModel extends AdminModel
{
    public $typeAlias = 'com_sanctuaryshop.order';

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_sanctuaryshop.order', 'order', ['control' => 'jform', 'load_data' => $loadData]);
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_sanctuaryshop.edit.order.data', []);
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    public function getOrderItems(int $orderId): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_order_items'))
            ->where($db->quoteName('order_id') . ' = ' . $orderId);
        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getTable($name = 'Order', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
