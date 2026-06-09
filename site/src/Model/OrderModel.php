<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class OrderModel extends BaseDatabaseModel
{
    public function getOrder(int $orderId): ?object
    {
        $user = Factory::getApplication()->getIdentity();
        $db   = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_orders'))
            ->where($db->quoteName('id') . ' = ' . (int) $orderId);

        if (!$user->id) {
            return null;
        }
        $query->where($db->quoteName('user_id') . ' = ' . (int) $user->id);

        return $db->setQuery($query)->loadObject();
    }

    public function getOrderItems(int $orderId): array
    {
        $db   = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_order_items'))
            ->where($db->quoteName('order_id') . ' = ' . (int) $orderId);
        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getDownloads(int $orderId): array
    {
        $user = Factory::getApplication()->getIdentity();
        $db   = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                't.token', 't.download_count', 't.max_downloads', 't.expires', 't.revoked',
                'p.title AS product_title',
                'pf.label AS file_label', 'pf.filename', 'pf.filesize',
            ])
            ->from($db->quoteName('#__sanctuaryshop_download_tokens', 't'))
            ->leftJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.id = t.product_id')
            ->leftJoin($db->quoteName('#__sanctuaryshop_product_files', 'pf') . ' ON pf.id = t.file_id')
            ->where('t.order_id = ' . (int) $orderId)
            ->where('t.revoked = 0');

        if ($user->id) {
            $query->where('t.user_id = ' . (int) $user->id);
        }

        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}
