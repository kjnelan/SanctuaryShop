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

    /** Dispatch the tokenized payment to the configured provider. */
    public function payment(): void
    {
        Session::checkToken('get') or jexit('Invalid Token');
        $app = Factory::getApplication();
        $orderId = $this->input->getInt('order_id');
        $provider = $this->input->getCmd('provider', 'square');
        $model = $this->getModel('Checkout', 'Site');
        try {
            if ($provider === 'square') {
                $paymentId = $model->chargeWithSquare($orderId, $this->input->getString('source_id'));
            } elseif ($provider === 'stripe') {
                $paymentId = $model->chargeWithStripe($orderId, $this->input->getString('payment_method'));
            } elseif ($provider === 'authorize_net') {
                $paymentId = $model->chargeWithAuthorizeNet($orderId, $this->input->getString('data_descriptor'), $this->input->getString('data_value'));
            } else {
                throw new \RuntimeException('The selected payment provider is not supported.');
            }
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => true, 'payment_id' => $paymentId]);
        } catch (\Exception $e) {
            http_response_code(422);
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        $app->close();
    }

    public function stripeIntent(): void
    {
        Session::checkToken('get') or jexit('Invalid Token');
        $app = Factory::getApplication();
        try {
            $intent = $this->getModel('Checkout', 'Site')->createStripeIntent($this->input->getInt('order_id'));
            $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => true] + $intent);
        } catch (\Exception $e) {
            http_response_code(422); $app->setHeader('Content-Type', 'application/json', true);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        $app->close();
    }

    public function stripeComplete(): void
    {
        Session::checkToken('get') or jexit('Invalid Token'); $app=Factory::getApplication();
        try{$id=$this->getModel('Checkout','Site')->completeStripePayment($this->input->getInt('order_id'),$this->input->getString('payment_intent'));$app->setHeader('Content-Type','application/json',true);echo json_encode(['success'=>true,'payment_id'=>$id]);}catch(\Exception $e){http_response_code(422);$app->setHeader('Content-Type','application/json',true);echo json_encode(['success'=>false,'error'=>$e->getMessage()]);}$app->close();
    }
}
