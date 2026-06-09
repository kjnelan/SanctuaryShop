<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class CartController extends BaseController
{
    public function add(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $productId = $this->input->getInt('product_id');
        $quantity  = max(1, $this->input->getInt('quantity', 1));

        $model = $this->getModel('Cart', 'Site');
        $model->addItem($productId, $quantity);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }

    public function update(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $quantities = $this->input->get('quantity', [], 'array');
        $model = $this->getModel('Cart', 'Site');
        $model->updateQuantities($quantities);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }

    public function remove(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $productId = $this->input->getInt('product_id');
        $model = $this->getModel('Cart', 'Site');
        $model->removeItem($productId);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }

    public function applyCoupon(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $code = $this->input->getString('coupon_code', '');
        $model = $this->getModel('Cart', 'Site');

        if (!empty($code)) {
            $result = $model->applyCoupon($code);
            if (!$result['success']) {
                $this->setRedirect(
                    Route::_('index.php?option=com_sanctuaryshop&view=cart', false),
                    $result['message'],
                    'warning'
                );
                return;
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }

    public function removeCoupon(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $model = $this->getModel('Cart', 'Site');
        $model->removeCoupon();

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }
}
