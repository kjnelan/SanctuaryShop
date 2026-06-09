<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class CouponTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__sanctuaryshop_coupons', 'id', $db);
    }

    public function check(): bool
    {
        $this->code = strtoupper(trim($this->code));
        return parent::check();
    }
}
