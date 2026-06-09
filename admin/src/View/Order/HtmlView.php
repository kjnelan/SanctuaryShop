<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Order;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $orderItems;
    protected $state;

    public function display($tpl = null): void
    {
        $this->form       = $this->get('Form');
        $this->item       = $this->get('Item');
        $this->state      = $this->get('State');
        $this->orderItems = $this->getModel()->getOrderItems((int) $this->item->id);

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title('COM_SANCTUARYSHOP_ORDER_VIEW', 'eye');
        ToolbarHelper::apply('order.apply');
        ToolbarHelper::save('order.save');
        ToolbarHelper::cancel('order.cancel', 'JTOOLBAR_CLOSE');
    }
}
