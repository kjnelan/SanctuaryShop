<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class OrdersController extends AdminController
{
    public function getModel($name = 'Order', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
