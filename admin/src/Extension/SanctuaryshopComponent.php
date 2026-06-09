<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

class SanctuaryshopComponent extends MVCComponent implements
    BootableExtensionInterface,
    CategoryServiceInterface
{
    use CategoryServiceTrait;
    use HTMLRegistryAwareTrait;

    public function boot(ContainerInterface $container): void
    {
        // Boot-time service registration (if needed)
    }

    public function countItems(array $items, string $section): void
    {
        // Used by category manager to show product counts
    }
}
