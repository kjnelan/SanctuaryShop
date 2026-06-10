<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class CartController extends BaseController
{
    public function add(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $productId   = $this->input->getInt('product_id');
        $quantity    = max(1, $this->input->getInt('quantity', 1));
        $variantJson = $this->input->getString('variant_info', '');
        $variantInfo = null;
        if ($variantJson) {
            $decoded = json_decode($variantJson, true);
            if (is_array($decoded)) {
                $variantInfo = $decoded;
            }
        }

        $model = $this->getModel('Cart', 'Site');
        $model->addItem($productId, $quantity, $variantInfo);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }

    public function buyNow(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $productId   = $this->input->getInt('product_id');
        $quantity    = max(1, $this->input->getInt('quantity', 1));
        $variantJson = $this->input->getString('variant_info', '');
        $variantInfo = null;
        if ($variantJson) {
            $decoded = json_decode($variantJson, true);
            if (is_array($decoded)) {
                $variantInfo = $decoded;
            }
        }

        $model = $this->getModel('Cart', 'Site');
        $model->addItem($productId, $quantity, $variantInfo);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=checkout', false));
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

        $cartKey = $this->input->getString('cart_key', '');
        $model   = $this->getModel('Cart', 'Site');
        $model->removeItem($cartKey);

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
                    Text::_($result['message']),
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
