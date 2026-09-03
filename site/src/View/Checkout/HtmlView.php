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
    protected $discount;
    protected $tax;
    protected $shipping;
    protected $total;
    protected $taxRate;
    protected $currency;
    protected $squareAppId;
    protected $squareLocationId;
    protected $squareEnvironment;
    protected $orderId;
    public $orderDownloads = [];

    public function display($tpl = null): void
    {
        $params = ComponentHelper::getParams('com_sanctuaryshop');
        $this->squareAppId       = $params->get('square_application_id', '');
        $this->squareLocationId  = $params->get('square_location_id', '');
        $this->squareEnvironment = $params->get('square_environment', 'sandbox');
        $this->currency          = strtoupper($params->get('currency', 'USD'));
        $this->taxRate           = (float) $params->get('tax_rate', 0);

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
            $this->tax       = round($afterDiscount * $this->taxRate / 100, 2);

            $flatRate          = (float) $params->get('shipping_flat_rate', 0);
            $freeThreshold     = (float) $params->get('shipping_free_threshold', 0);
            $requiresShipping  = (bool) array_filter($this->cartItems, static fn($item) => ($item->product_type ?? 'physical') === 'physical');
            $this->shipping    = !$requiresShipping ? 0.00 : (($freeThreshold > 0 && $this->subtotal >= $freeThreshold) ? 0.00 : $flatRate);
            $this->total       = round($afterDiscount + $this->tax + $this->shipping, 2);

            $sdkUrl = $this->squareEnvironment === 'production'
                ? 'https://web.squarecdn.com/v1/square.js'
                : 'https://sandbox.web.squarecdn.com/v1/square.js';

            Factory::getDocument()->addScript($sdkUrl);
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
