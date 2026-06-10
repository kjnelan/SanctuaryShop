<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$dispatcher = new \SanctuaryShop\Module\SanctuaryshopCart\Dispatcher\Dispatcher(
    $module,
    \Joomla\CMS\Factory::getApplication()
);
$dispatcher->dispatch();
