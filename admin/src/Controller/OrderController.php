<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

class OrderController extends FormController
{
    protected $view_list = 'orders';

    public function updateStatus(): void
    {
        Session::checkToken() or jexit('Invalid Token');

        $app            = Factory::getApplication();
        $input          = $app->getInput();
        $orderId        = $input->getInt('id');
        $status         = $input->getString('status', '');
        $trackingNumber = $input->getString('tracking_number', '');

        $allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'];
        if (!in_array($status, $allowed)) {
            $app->enqueueMessage('Invalid status.', 'error');
            $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&task=order.edit&id=' . $orderId, false));
            return;
        }

        /** @var \SanctuaryShop\Component\Sanctuaryshop\Administrator\Model\OrderModel $model */
        $model = $this->getModel('Order');
        if ($model->updateStatus($orderId, $status, $trackingNumber)) {
            $app->enqueueMessage(Text::_('JSAVED'), 'success');
        } else {
            $app->enqueueMessage($model->getError(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&task=order.edit&id=' . $orderId, false));
    }

    public function refund(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $orderId = $this->input->getInt('id');
        $amount = $this->input->getFloat('refund_amount', 0);
        $model = $this->getModel('Order');

        try {
            $refundId = $model->refundWithSquare($orderId, $amount > 0 ? $amount : null);
            $this->setMessage(Text::_('COM_SANCTUARYSHOP_REFUND_SUCCESS') . ' ' . $refundId, 'success');
        } catch (\Throwable $e) {
            $this->setMessage(Text::_('COM_SANCTUARYSHOP_REFUND_FAILED') . ' ' . $e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_sanctuaryshop&task=order.edit&id=' . $orderId, false));
    }

    public function save($key = null, $urlVar = null)
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $data  = $input->get('jform', [], 'array');

        // Merge tracking_number from top-level input if present
        $trackingNumber = $input->getString('tracking_number', null);
        if ($trackingNumber !== null && !isset($data['tracking_number'])) {
            $data['tracking_number'] = $trackingNumber;
        }

        $input->set('jform', $data);

        return parent::save($key, $urlVar);
    }
}
