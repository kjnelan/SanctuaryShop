<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class SettingsController extends BaseController
{
    public function save(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $app   = Factory::getApplication();
        $input = $app->getInput();

        $params = ComponentHelper::getParams('com_sanctuaryshop');

        // General
        $params->set('shop_name',         $input->getString('shop_name', ''));
        $params->set('currency',          $input->getString('currency', 'USD'));
        $params->set('tax_rate',          $input->getFloat('tax_rate', 0));
        $params->set('products_per_page', $input->getInt('products_per_page', 12));
        $params->set('notify_email',      $input->getString('notify_email', ''));
        $params->set('pending_order_expiry_minutes', $input->getInt('pending_order_expiry_minutes', 60));

        // Payment
        $params->set('square_environment',           $input->getString('square_environment', 'sandbox'));
        $params->set('square_application_id',        $input->getString('square_application_id', ''));
        $params->set('square_access_token',          $input->getString('square_access_token', ''));
        $params->set('square_location_id',           $input->getString('square_location_id', ''));
        $params->set('square_webhook_signature_key', $input->getString('square_webhook_signature_key', ''));
        $params->set('square_webhook_url',             $input->getString('square_webhook_url', ''));

        // Shipping
        $params->set('shipping_flat_rate',      $input->getFloat('shipping_flat_rate', 0));
        $params->set('shipping_free_threshold', $input->getFloat('shipping_free_threshold', 0));

        // Downloads
        $params->set('download_path',          $input->getString('download_path', ''));
        $params->set('download_expiry_days',   $input->getInt('download_expiry_days', 30));
        $params->set('download_max_per_token', $input->getInt('download_max_per_token', 5));

        $db    = Factory::getContainer()->get('db');
        $query = $db->getQuery(true)
            ->update('#__extensions')
            ->set('params = ' . $db->quote($params->toString()))
            ->where('element = ' . $db->quote('com_sanctuaryshop'))
            ->where('type = ' . $db->quote('component'));
        $db->setQuery($query)->execute();

        $app->enqueueMessage(Text::_('COM_SANCTUARYSHOP_SETTINGS_SAVED'), 'success');
        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=settings', false));
    }

    public function cancel(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop', false));
    }
}
