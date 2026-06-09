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

        /** @var \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CartModel $model */
        $model = $this->getModel('Cart', 'Site');
        $model->addItem($productId, $quantity);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }

    public function update(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $quantities = $this->input->get('quantity', [], 'array');
        /** @var \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CartModel $model */
        $model = $this->getModel('Cart', 'Site');
        $model->updateQuantities($quantities);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }

    public function remove(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $productId = $this->input->getInt('product_id');
        /** @var \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CartModel $model */
        $model = $this->getModel('Cart', 'Site');
        $model->removeItem($productId);

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=cart', false));
    }
}
