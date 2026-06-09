<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Coupons;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $this->addToolbar();

        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_SANCTUARYSHOP_COUPONS'), 'tags');
        ToolbarHelper::addNew('coupon.add');
        ToolbarHelper::publish('coupons.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('coupons.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('', 'coupons.delete', 'JTOOLBAR_DELETE');
    }
}
