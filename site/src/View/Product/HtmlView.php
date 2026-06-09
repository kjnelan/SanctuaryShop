<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\View\Product;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $item;

    public function display($tpl = null): void
    {
        $this->item = $this->get('Item');

        if (!$this->item) {
            throw new \Exception('Product not found', 404);
        }

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }
}
