<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Order;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

class HtmlView extends BaseHtmlView
{
    public $order;
    public $items;
    public $downloads;

    public function display($tpl = null): void
    {
        $app   = Factory::getApplication();
        $user  = $app->getIdentity();

        if (!$user->id) {
            $app->enqueueMessage(Text::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'notice');
            $app->redirect(Route::_('index.php?option=com_users&view=login', false));
            return;
        }

        $orderId    = $app->getInput()->getInt('id');
        $model      = $this->getModel();
        $this->order = $model->getOrder($orderId);

        if (!$this->order) {
            $app->enqueueMessage(Text::_('COM_SANCTUARYSHOP_ORDER_NOT_FOUND'), 'error');
            $app->redirect(Route::_('index.php?option=com_sanctuaryshop&view=account', false));
            return;
        }

        $this->items     = $model->getOrderItems($orderId);
        $this->downloads = $model->getDownloads($orderId);

        parent::display($tpl);
    }
}
