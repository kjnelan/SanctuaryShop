<?php
namespace SanctuaryShop\Module\SanctuaryshopCart\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Helper\ModuleHelper;
use SanctuaryShop\Component\Sanctuaryshop\Site\Model\CartModel;

class Dispatcher
{
    private $module;
    private $app;

    public function __construct($module, CMSApplicationInterface $app)
    {
        $this->module = $module;
        $this->app    = $app;
    }

    public function dispatch(): void
    {
        // Load cart data
        $cartModel = new CartModel(['ignore_request' => true]);
        $cartItems = $cartModel->getItems();
        $cartCount = $cartModel->getCount();
        $subtotal  = $cartModel->getSubtotal();

        $module = $this->module;

        include ModuleHelper::getLayoutPath('mod_sanctuaryshop_cart', 'default');
    }
}
