<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class DashboardModel extends BaseDatabaseModel
{
    public function getTotals(): object
    {
        $db = $this->getDatabase();

        // Total revenue (completed orders)
        $query = $db->getQuery(true)
            ->select('COALESCE(SUM(total), 0)')
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('completed'));
        $revenue = (float) $db->setQuery($query)->loadResult();

        // Total orders
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sanctuaryshop_orders'));
        $totalOrders = (int) $db->setQuery($query)->loadResult();

        // Pending orders
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'));
        $pendingOrders = (int) $db->setQuery($query)->loadResult();

        // Total products
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sanctuaryshop_products'))
            ->where($db->quoteName('state') . ' = 1');
        $totalProducts = (int) $db->setQuery($query)->loadResult();

        return (object) compact('revenue', 'totalOrders', 'pendingOrders', 'totalProducts');
    }

    public function getRecentOrders(int $limit = 5): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->order('created DESC');
        return $db->setQuery($query, 0, $limit)->loadObjectList() ?: [];
    }

    public function getTopProducts(int $limit = 5): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('oi.product_id, oi.title, SUM(oi.quantity) AS total_qty, SUM(oi.total_price) AS total_revenue')
            ->from($db->quoteName('#__sanctuaryshop_order_items', 'oi'))
            ->innerJoin($db->quoteName('#__sanctuaryshop_orders', 'o') . ' ON o.id = oi.order_id')
            ->where($db->quoteName('o.status') . ' = ' . $db->quote('completed'))
            ->group('oi.product_id, oi.title')
            ->order('total_revenue DESC');
        return $db->setQuery($query, 0, $limit)->loadObjectList() ?: [];
    }

    public function getLowStock(): array
    {
        $threshold = (int) \Joomla\CMS\Component\ComponentHelper::getParams('com_sanctuaryshop')->get('low_stock_threshold', 5);
        $db = $this->getDatabase();
        return $db->setQuery(
            $db->getQuery(true)->select(['id', 'title', 'sku', 'stock'])->from($db->quoteName('#__sanctuaryshop_products'))
                ->where('state = 1')->where("product_type = 'physical'")->where('stock <= ' . $threshold)->order('stock ASC, title ASC')
        )->loadObjectList() ?: [];
    }
}
