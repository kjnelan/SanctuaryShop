<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Cart;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $cartItems;
    protected $subtotal;
    protected $cartCount;
    protected $couponCode;
    protected $couponDiscount;

    public function display($tpl = null): void
    {
        /** @var \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CartModel $model */
        $model            = $this->getModel();
        $this->cartItems  = $model->getItems();
        $this->subtotal   = $model->getSubtotal();
        $this->cartCount  = $model->getCount();
        $this->couponCode = $model->getCouponCode();
        $this->couponDiscount = $model->getCouponDiscount($this->subtotal);

        parent::display($tpl);
    }
}
