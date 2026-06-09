<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Coupon;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->addToolbar();

        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        $isNew = ((int) $this->item->id === 0);
        ToolbarHelper::title(Text::_('COM_SANCTUARYSHOP_COUPON') . ': ' . ($isNew ? Text::_('JNEW') : $this->item->code));
        ToolbarHelper::apply('coupon.apply');
        ToolbarHelper::save('coupon.save');
        ToolbarHelper::save2new('coupon.save2new');
        ToolbarHelper::cancel('coupon.cancel');
    }
}
