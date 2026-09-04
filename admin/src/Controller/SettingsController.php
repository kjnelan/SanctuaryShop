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
        $params->set('shop_tagline',      $input->getString('shop_tagline', ''));
        $params->set('receipt_title',    $input->getString('receipt_title', 'Receipt'));
        $params->set('receipt_footer',   $input->getString('receipt_footer', 'Thank you for your order.'));
        $params->set('receipt_logo',     $input->getString('receipt_logo', ''));
        $receiptColor = $input->getString('receipt_color', '#1a1a2e');
        $params->set('receipt_color',    preg_match('/^#[0-9a-fA-F]{6}$/', $receiptColor) ? $receiptColor : '#1a1a2e');
        $params->set('store_email',       $input->getString('store_email', ''));
        $params->set('store_phone',       $input->getString('store_phone', ''));
        $params->set('store_address',     $input->getString('store_address', ''));
        $params->set('store_city',        $input->getString('store_city', ''));
        $params->set('store_state',       $input->getString('store_state', ''));
        $params->set('store_postal_code', $input->getString('store_postal_code', ''));
        $params->set('store_country',     strtoupper($input->getString('store_country', 'US')));
        $params->set('currency',          $input->getString('currency', 'USD'));
        $params->set('tax_rate',          $input->getFloat('tax_rate', 0));
        $params->set('prices_include_tax', $input->getInt('prices_include_tax', 0));
        $params->set('tax_rules',         $input->getString('tax_rules', ''));
        $params->set('products_per_page', $input->getInt('products_per_page', 12));
        $params->set('notify_email',      $input->getString('notify_email', ''));
        $params->set('send_customer_confirmation', $input->getInt('send_customer_confirmation', 0));
        $params->set('guest_checkout',     $input->getInt('guest_checkout', 0));
        $params->set('require_phone',      $input->getInt('require_phone', 0));
        $params->set('require_terms',     $input->getInt('require_terms', 0));
        $params->set('terms_url',         $input->getString('terms_url', ''));
        $params->set('pending_order_expiry_minutes', $input->getInt('pending_order_expiry_minutes', 60));
        $params->set('low_stock_threshold', $input->getInt('low_stock_threshold', 5));

        // Payment
        $params->set('payment_provider',             $input->getCmd('payment_provider', 'square'));
        $params->set('square_environment',           $input->getString('square_environment', 'sandbox'));
        $params->set('square_application_id',        $input->getString('square_application_id', ''));
        $params->set('square_access_token',          $input->getString('square_access_token', ''));
        $params->set('square_location_id',           $input->getString('square_location_id', ''));
        $params->set('square_webhook_signature_key', $input->getString('square_webhook_signature_key', ''));
        $params->set('square_webhook_url',             $input->getString('square_webhook_url', ''));
        $params->set('stripe_publishable_key',      $input->getString('stripe_publishable_key', ''));
        $params->set('stripe_secret_key',            $input->getString('stripe_secret_key', ''));
        $params->set('stripe_webhook_secret',        $input->getString('stripe_webhook_secret', ''));
        $params->set('authorize_environment',        $input->getCmd('authorize_environment', 'sandbox'));
        $params->set('authorize_api_login_id',       $input->getString('authorize_api_login_id', ''));
        $params->set('authorize_transaction_key',    $input->getString('authorize_transaction_key', ''));
        $params->set('authorize_public_client_key',  $input->getString('authorize_public_client_key', ''));

        // Shipping
        $params->set('shipping_enabled',        $input->getInt('shipping_enabled', 0));
        $params->set('shipping_flat_rate',      $input->getFloat('shipping_flat_rate', 0));
        $params->set('shipping_handling_fee',   $input->getFloat('shipping_handling_fee', 0));
        $params->set('shipping_free_threshold', $input->getFloat('shipping_free_threshold', 0));
        $params->set('shipping_free_after_discount', $input->getInt('shipping_free_after_discount', 0));
        $params->set('shipping_methods',        $input->getString('shipping_methods', ''));
        $params->set('shipping_weight_rules',   $input->getString('shipping_weight_rules', ''));
        $params->set('shipping_rules',          $input->getString('shipping_rules', ''));

        // Inventory
        $params->set('allow_backorders', $input->getInt('allow_backorders', 0));

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
