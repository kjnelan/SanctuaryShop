<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class AccountModel extends BaseDatabaseModel
{
    public function getOrders(): array
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->id) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->where('user_id = ' . (int) $user->id)
            ->order('id DESC');
        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getDownloads(): array
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->id) {
            return [];
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                't.*',
                $db->quoteName('p.title', 'product_title'),
                $db->quoteName('pf.label', 'file_label'),
                $db->quoteName('pf.filename', 'filename'),
                $db->quoteName('pf.filesize', 'filesize'),
            ])
            ->from($db->quoteName('#__sanctuaryshop_download_tokens', 't'))
            ->leftJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.id = t.product_id')
            ->leftJoin($db->quoteName('#__sanctuaryshop_product_files', 'pf') . ' ON pf.id = t.file_id')
            ->where('t.user_id = ' . (int) $user->id)
            ->where('t.revoked = 0')
            ->order('t.product_id ASC, t.id ASC');
        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}
