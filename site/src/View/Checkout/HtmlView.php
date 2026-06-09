<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Checkout;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $cartItems;
    protected $subtotal;
    protected $squareAppId;
    protected $squareLocationId;
    protected $squareEnvironment;
    protected $orderId;

    public function display($tpl = null): void
    {
        $params = ComponentHelper::getParams('com_sanctuaryshop');
        $this->squareAppId       = $params->get('square_application_id', '');
        $this->squareLocationId  = $params->get('square_location_id', '');
        $this->squareEnvironment = $params->get('square_environment', 'sandbox');

        $layout = $this->getLayout();

        if ($layout === 'confirmation') {
            $this->orderId = Factory::getApplication()->input->getInt('order_id');
        } else {
            /** @var \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CartModel $cartModel */
            $cartModel       = $this->getModel('Cart');
            $this->cartItems = $cartModel->getItems();
            $this->subtotal  = $cartModel->getSubtotal();

            // Load Square Web Payments SDK
            $sdkUrl = $this->squareEnvironment === 'production'
                ? 'https://web.squarecdn.com/v1/square.js'
                : 'https://sandbox.web.squarecdn.com/v1/square.js';

            $doc = Factory::getDocument();
            $doc->addScript($sdkUrl);
        }

        parent::display($tpl);
    }
}
