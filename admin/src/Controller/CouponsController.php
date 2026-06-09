<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class CouponsController extends AdminController
{
    public function getModel($name = 'Coupon', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
