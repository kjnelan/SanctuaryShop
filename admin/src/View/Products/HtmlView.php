<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Products;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    public $categories = [];
    public $currencySym = '$';

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $model = $this->getModel();
        if (method_exists($model, 'getCategories')) {
            $this->categories = $model->getCategories();
        }

        $currency = strtoupper(ComponentHelper::getParams('com_sanctuaryshop')->get('currency', 'USD'));
        $map      = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'CAD' => 'CA$', 'AUD' => 'A$'];
        $this->currencySym = $map[$currency] ?? $currency . ' ';

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
        $toolbar->link('JTOOLBAR_OPTIONS', 'index.php?option=com_config&view=component&component=com_sanctuaryshop')
                ->icon('icon-cog');
    }
}
