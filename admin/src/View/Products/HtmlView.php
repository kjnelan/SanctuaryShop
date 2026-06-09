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
    public $filterForm;
    public $activeFilters;
    public $listDirn;
    public $listOrder;

    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->listDirn      = $this->escape($this->state->get('list.direction'));
        $this->listOrder     = $this->escape($this->state->get('list.ordering'));

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $toolbar = Toolbar::getInstance();
        ToolbarHelper::title('COM_SANCTUARYSHOP_PRODUCTS', 'cart');
        $toolbar->addNew('product.add');
        $toolbar->publish('products.publish')->listCheck(true);
        $toolbar->unpublish('products.unpublish')->listCheck(true);
        $toolbar->delete('products.delete')->message('JGLOBAL_CONFIRM_DELETE')->listCheck(true);
        $toolbar->preferences('com_sanctuaryshop');
    }
}
