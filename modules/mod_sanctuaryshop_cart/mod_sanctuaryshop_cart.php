<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use SanctuaryShop\Component\Sanctuaryshop\Site\Model\CartModel;

$cartModel = new CartModel(['ignore_request' => true]);
$cartItems = $cartModel->getItems();
$cartCount = $cartModel->getCount();
$subtotal  = $cartModel->getSubtotal();

require ModuleHelper::getLayoutPath('mod_sanctuaryshop_cart', 'default');
