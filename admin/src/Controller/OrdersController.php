<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;

class OrdersController extends AdminController
{
    public function getModel($name = 'Order', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function export(): void
    {
        $db = Factory::getContainer()->get('db');
        $query = $db->getQuery(true)->select('*')->from($db->quoteName('#__sanctuaryshop_orders'))->order('created ASC');
        $rows = $db->setQuery($query)->loadAssocList() ?: [];
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'text/csv; charset=utf-8', true);
        $app->setHeader('Content-Disposition', 'attachment; filename="sanctuaryshop-orders-' . date('Y-m-d') . '.csv"', true);
        $out = fopen('php://output', 'w');
        if ($rows) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
        }
        fclose($out);
        $app->close();
    }
}
