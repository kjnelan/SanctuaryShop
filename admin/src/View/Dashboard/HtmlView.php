<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $totals;
    public $recentOrders;
    public $topProducts;
    public $currency;
    public $lowStock;

    public function display($tpl = null): void
    {
        $model             = $this->getModel();
        $this->totals      = $model->getTotals();
        $this->recentOrders = $model->getRecentOrders();
        $this->topProducts = $model->getTopProducts();
        $this->lowStock    = $model->getLowStock();
        $this->currency    = strtoupper(ComponentHelper::getParams('com_sanctuaryshop')->get('currency', 'USD'));

        ToolbarHelper::title(Text::_('COM_SANCTUARYSHOP_DASHBOARD'), 'dashboard');

        parent::display($tpl);
    }
}
