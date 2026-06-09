<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Products;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    public $categories = [];
    public $currency   = 'USD';
    public $activeCatId = 0;
    public $search     = '';
    public $sort       = '';
    public $activeType = '';

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->categories = $this->getModel()->getCategories();

        $app               = Factory::getApplication();
        $params            = ComponentHelper::getParams('com_sanctuaryshop');
        $this->currency    = strtoupper($params->get('currency', 'USD'));
        $this->activeCatId = $app->input->getInt('catid', 0);
        $this->search      = $app->input->getString('search', '');
        $this->sort        = $app->input->getString('sort', '');
        $this->activeType  = $app->input->getString('type', '');

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $doc = Factory::getDocument();
        $menu = $app->getMenu()->getActive();
        if ($menu) {
            $pageTitle = $menu->getParams()->get('page_title', $menu->title);
            if ($this->search) {
                $pageTitle .= ' — Search: ' . $this->search;
            }
            $doc->setTitle($pageTitle);
        }

        parent::display($tpl);
    }
}
