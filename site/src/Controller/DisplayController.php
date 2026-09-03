<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'products';

    public function display($cachable = false, $urlparams = []): static
    {
        // Cart, checkout, account, confirmation, and download views contain
        // session/customer-specific data and must never be page-cached.
        $cachable = false;

        $safeurlparams = [
            'catid'  => 'INT',
            'id'     => 'INT',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'limit'  => 'INT',
            'start'  => 'INT',
            'page'   => 'INT',
        ];

        return parent::display($cachable, $safeurlparams);
    }
}
