<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Product;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null): void
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? 'COM_SANCTUARYSHOP_PRODUCT_NEW' : 'COM_SANCTUARYSHOP_PRODUCT_EDIT', 'box-add');
        ToolbarHelper::apply('product.apply');
        ToolbarHelper::save('product.save');
        ToolbarHelper::save2new('product.save2new');
        if (!$isNew) {
            ToolbarHelper::save2copy('product.save2copy');
        }
        ToolbarHelper::cancel($isNew ? 'product.cancel' : 'product.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
