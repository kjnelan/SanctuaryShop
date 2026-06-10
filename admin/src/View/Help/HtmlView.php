<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\View\Help;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $toolbar = Toolbar::getInstance();
        ToolbarHelper::title(Text::_('COM_SANCTUARYSHOP_HELP'), 'info-circle');
    }
}
