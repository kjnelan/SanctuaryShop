<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Products;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
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
        ToolbarHelper::title('COM_SANCTUARYSHOP_PRODUCTS', 'cart');
        ToolbarHelper::addNew('product.add');
        ToolbarHelper::editList('product.edit');
        ToolbarHelper::publish('products.publish');
        ToolbarHelper::unpublish('products.unpublish');
        ToolbarHelper::deleteList('', 'products.delete');
        ToolbarHelper::preferences('com_sanctuaryshop');
    }
}
