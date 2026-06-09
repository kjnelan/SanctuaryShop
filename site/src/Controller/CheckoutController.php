<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

class CheckoutController extends BaseController
{
    /**
     * Create a pending order from cart + billing data, return JSON.
     * Called via AJAX from the checkout page before card tokenisation.
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
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => true, 'order_id' => $orderId]);
        } catch (\Exception $e) {
            http_response_code(422);
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

        $app->close();
    }

    /**
     * Charge via Square using the payment sourceId/nonce from the Web Payments SDK.
     * Returns JSON {success, payment_id} or {success: false, error}.
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
            $paymentId = $model->chargeWithSquare($orderId, $nonce);
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => true, 'payment_id' => $paymentId]);
        } catch (\Exception $e) {
            http_response_code(422);
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

        $app->close();
    }
}
