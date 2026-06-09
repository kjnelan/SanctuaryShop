<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use SanctuaryShop\Component\Sanctuaryshop\Administrator\Extension\SanctuaryshopComponent;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new CategoryFactory('\\SanctuaryShop\\Component\\Sanctuaryshop'));
        $container->registerServiceProvider(new MVCFactory('\\SanctuaryShop\\Component\\Sanctuaryshop'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\SanctuaryShop\\Component\\Sanctuaryshop'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new SanctuaryshopComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                return $component;
            }
        );
    }
};
