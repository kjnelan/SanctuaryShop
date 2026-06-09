<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Account;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

class HtmlView extends BaseHtmlView
{
    public $orders = [];
    public $user;

    public function display($tpl = null): void
    {
        $app        = Factory::getApplication();
        $this->user = $app->getIdentity();

        if (!$this->user->id) {
            $app->enqueueMessage(Text::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'notice');
            $app->redirect(Route::_('index.php?option=com_users&view=login', false));
            return;
        }

        $this->orders = $this->getModel()->getOrders();

        parent::display($tpl);
    }
}
