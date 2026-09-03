<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Checkout;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SanctuaryShop\Component\Sanctuaryshop\Site\Model\CheckoutModel;

class HtmlView extends BaseHtmlView
{
    protected $cartItems;
    protected $subtotal;
    protected $discount;
    protected $tax;
    protected $shipping;
    protected $shippingMethods = [];
    protected $total;
    protected $taxRate;
    protected $currency;
    protected $squareAppId;
    protected $squareLocationId;
    protected $squareEnvironment;
    protected $paymentProvider;
    protected $stripePublishableKey;
    protected $authorizePublicClientKey;
    protected $authorizeApiLoginId;
    protected $authorizeEnvironment;
    protected $orderId;
    public $orderDownloads = [];

    public function display($tpl = null): void
    {
        $params = ComponentHelper::getParams('com_sanctuaryshop');
        $this->squareAppId       = $params->get('square_application_id', '');
        $this->squareLocationId  = $params->get('square_location_id', '');
        $this->squareEnvironment = $params->get('square_environment', 'sandbox');
        $this->paymentProvider = $params->get('payment_provider', 'square');
        $this->stripePublishableKey = $params->get('stripe_publishable_key', '');
        $this->authorizePublicClientKey = $params->get('authorize_public_client_key', '');
        $this->authorizeApiLoginId = $params->get('authorize_api_login_id', '');
        $this->authorizeEnvironment = $params->get('authorize_environment', 'sandbox');
        $this->currency          = strtoupper($params->get('currency', 'USD'));
            $this->taxRate           = CheckoutModel::locationRate($params->get('tax_rules', ''), ['country' => 'US'], (float) $params->get('tax_rate', 0));

        $layout = $this->getLayout();

        if ($layout === 'confirmation') {
            $this->orderId = Factory::getApplication()->getInput()->getInt('order_id');
            if ($this->orderId) {
                $this->orderDownloads = $this->getOrderDownloads($this->orderId);
            }
        } else {
            $cartModel       = $this->getModel('Cart');
            $this->cartItems = $cartModel->getItems();
            $this->subtotal  = $cartModel->getSubtotal();
            $this->discount  = $cartModel->getCouponDiscount($this->subtotal);
            $afterDiscount   = max(0, $this->subtotal - $this->discount);
            $this->tax       = (int) $params->get('prices_include_tax', 0) === 1 && $this->taxRate > 0
                ? round($afterDiscount - ($afterDiscount / (1 + $this->taxRate / 100)), 2)
                : round($afterDiscount * $this->taxRate / 100, 2);

            $this->shippingMethods = CheckoutModel::shippingMethods((string) $params->get('shipping_methods', ''), (float) $params->get('shipping_flat_rate', 0));
            $shippingData = CheckoutModel::calculateShipping($params, ['country' => 'US'], $this->cartItems, $this->subtotal, $this->discount, $this->shippingMethods[0]['code']);
            $freeThreshold = (float) $params->get('shipping_free_threshold', 0);
            $thresholdBase = (int) $params->get('shipping_free_after_discount', 0) === 1 ? $afterDiscount : $this->subtotal;
            $this->shipping = $freeThreshold > 0 && $thresholdBase >= $freeThreshold ? 0.00 : $shippingData['amount'];
            $this->total       = round($afterDiscount + $this->tax + $this->shipping, 2);

            if ($this->paymentProvider === 'square') {
                Factory::getDocument()->addScript($this->squareEnvironment === 'production' ? 'https://web.squarecdn.com/v1/square.js' : 'https://sandbox.web.squarecdn.com/v1/square.js');
            } elseif ($this->paymentProvider === 'stripe') {
                Factory::getDocument()->addScript('https://js.stripe.com/v3/');
            } elseif ($this->paymentProvider === 'authorize_net') {
                Factory::getDocument()->addScript($this->authorizeEnvironment === 'production' ? 'https://js.authorize.net/v1/Accept.js' : 'https://jstest.authorize.net/v1/Accept.js');
            }
        }

        parent::display($tpl);
    }

    private function getOrderDownloads(int $orderId): array
    {
        try {
            $user  = Factory::getApplication()->getIdentity();
            $db    = Factory::getContainer()->get('db');
            $query = $db->getQuery(true)
                ->select([
                    't.token', 't.download_count', 't.max_downloads', 't.expires', 't.revoked',
                    'p.title AS product_title',
                    'pf.label AS file_label', 'pf.filename', 'pf.filesize',
                ])
                ->from($db->quoteName('#__sanctuaryshop_download_tokens', 't'))
                ->leftJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.id = t.product_id')
                ->leftJoin($db->quoteName('#__sanctuaryshop_product_files', 'pf') . ' ON pf.id = t.file_id')
                ->where('t.order_id = ' . $orderId)
                ->where('t.revoked = 0')
                ->where('t.order_id IN (SELECT id FROM #__sanctuaryshop_orders WHERE status = ' . $db->quote('completed') . ')');
            if ($user->id) {
                $query->where('t.user_id = ' . (int) $user->id);
            } elseif ((int) Factory::getApplication()->getSession()->get('sanctuaryshop.confirmation_order_id', 0) !== $orderId) {
                return [];
            }
            return $db->setQuery($query)->loadObjectList() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
