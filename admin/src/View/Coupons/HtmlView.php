<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Coupons;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $toolbar = Toolbar::getInstance();
        ToolbarHelper::title(Text::_('COM_SANCTUARYSHOP_COUPONS'), 'tags');
        $toolbar->addNew('coupon.add');
        $toolbar->publish('coupons.publish')->listCheck(true);
        $toolbar->unpublish('coupons.unpublish')->listCheck(true);
        $toolbar->delete('coupons.delete')->message('JGLOBAL_CONFIRM_DELETE')->listCheck(true);
    }
}
