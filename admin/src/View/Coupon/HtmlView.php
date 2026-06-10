<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Coupon;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null): void
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $isNew   = ((int) ($this->item->id ?? 0) === 0);
        $toolbar = Toolbar::getInstance();
        ToolbarHelper::title(Text::_('COM_SANCTUARYSHOP_COUPON'), 'tags');
        $toolbar->apply('coupon.apply');
        $toolbar->save('coupon.save');
        $toolbar->save2new('coupon.save2new');
        $toolbar->cancel($isNew ? 'coupon.cancel' : 'coupon.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
