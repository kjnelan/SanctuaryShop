<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class ProductTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__sanctuaryshop_products', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        if (empty($this->alias)) {
            $this->alias = ApplicationHelper::stringURLSafe($this->title);
        }
        if (empty($this->alias)) {
            $this->alias = (string) time();
        }
        return parent::check();
    }
}
