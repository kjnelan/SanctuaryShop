<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Product;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;
    public $productFiles = [];
    public $productVariants = [];
    public $productImages = [];

    public function display($tpl = null): void
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (!empty($this->item->id) && $this->item->product_type === 'digital') {
            $this->productFiles = $this->getModel()->getProductFiles((int) $this->item->id);
        }

        if (!empty($this->item->id)) {
            $this->productVariants = $this->getModel()->getProductVariants((int) $this->item->id);
            $this->productImages   = $this->getModel()->getProductImages((int) $this->item->id);
        }

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $isNew   = ($this->item->id == 0);
        $toolbar = Toolbar::getInstance();
        ToolbarHelper::title($isNew ? 'COM_SANCTUARYSHOP_PRODUCT_NEW' : 'COM_SANCTUARYSHOP_PRODUCT_EDIT', 'box-add');
        $toolbar->apply('product.apply');
        $toolbar->save('product.save');
        $toolbar->save2new('product.save2new');
        if (!$isNew) {
            $toolbar->save2copy('product.save2copy');
        }
        $toolbar->cancel($isNew ? 'product.cancel' : 'product.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
