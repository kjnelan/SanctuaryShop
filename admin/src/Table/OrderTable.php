<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class OrderTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__sanctuaryshop_orders', 'id', $db);
    }
}
