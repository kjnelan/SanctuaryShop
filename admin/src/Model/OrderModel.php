<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use SanctuaryShop\Component\Sanctuaryshop\Site\Model\CheckoutModel;

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

    public function updateStatus(int $orderId, string $status, string $trackingNumber = ''): bool
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__sanctuaryshop_orders'))
                ->set($db->quoteName('status') . ' = ' . $db->quote($status))
                ->set($db->quoteName('modified') . ' = ' . $db->quote(Factory::getDate()->toSql()))
                ->where($db->quoteName('id') . ' = ' . (int) $orderId);

            if ($trackingNumber !== '') {
                $query->set($db->quoteName('tracking_number') . ' = ' . $db->quote($trackingNumber));
            }

            $db->setQuery($query)->execute();
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function refundWithSquare(int $orderId, ?float $amount = null): string
    {
        $checkout = new CheckoutModel;
        return $checkout->refundWithSquare($orderId, $amount);
    }

    public function getRefunds(int $orderId): array
    {
        return $this->getDatabase()->setQuery(
            $this->getDatabase()->getQuery(true)->select('*')->from($this->getDatabase()->quoteName('#__sanctuaryshop_refunds'))->where('order_id = ' . (int) $orderId)->order('id DESC')
        )->loadObjectList() ?: [];
    }

    public function save($data)
    {
        // Ensure tracking_number is preserved
        $result = parent::save($data);
        return $result;
    }
}
