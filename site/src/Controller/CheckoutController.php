<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class CheckoutController extends BaseController
{
    /**
     * Process the checkout form, create the order, and initiate Square payment.
     */
    public function process(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $app  = Factory::getApplication();
        $data = $this->input->get('jform', [], 'array');

        /** @var \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CheckoutModel $model */
        $model = $this->getModel('Checkout', 'Site');

        try {
            $orderId = $model->processOrder($data);
            $app->setUserState('com_sanctuaryshop.checkout.order_id', $orderId);
            $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=checkout&layout=confirmation&order_id=' . $orderId, false));
        } catch (\RuntimeException $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&view=checkout', false));
        }
    }

    /**
     * Handle Square Web Payments SDK callback (AJAX endpoint).
     * Receives a payment sourceId / nonce from the frontend, charges via Square API.
     */
    public function squarePayment(): void
    {
        Session::checkToken('get') or jexit('Invalid Token');

        $app     = Factory::getApplication();
        $orderId = $this->input->getInt('order_id');
        $nonce   = $this->input->getString('source_id');

        /** @var \SanctuaryShop\Component\Sanctuaryshop\Site\Model\CheckoutModel $model */
        $model = $this->getModel('Checkout', 'Site');

        try {
            $result = $model->chargeWithSquare($orderId, $nonce);
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => true, 'payment_id' => $result]);
        } catch (\RuntimeException $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        $app->close();
    }
}
