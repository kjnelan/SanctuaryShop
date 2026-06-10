<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Settings;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $params;

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_sanctuaryshop');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $toolbar = Toolbar::getInstance();
        ToolbarHelper::title('COM_SANCTUARYSHOP_SETTINGS', 'cog');
        $toolbar->apply('settings.save');
        $toolbar->cancel('settings.cancel', 'JTOOLBAR_CANCEL');
    }
}
