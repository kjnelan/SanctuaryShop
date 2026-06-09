<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class DownloadsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['t.id', 't.token', 't.order_id', 't.user_id', 't.revoked', 't.expires'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select([
            't.*',
            $db->quoteName('p.title', 'product_title'),
            $db->quoteName('pf.label', 'file_label'),
            $db->quoteName('pf.filename', 'filename'),
            $db->quoteName('u.name', 'user_name'),
        ])
        ->from($db->quoteName('#__sanctuaryshop_download_tokens', 't'))
        ->leftJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.id = t.product_id')
        ->leftJoin($db->quoteName('#__sanctuaryshop_product_files', 'pf') . ' ON pf.id = t.file_id')
        ->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = t.user_id');

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . implode(' OR ', [
                't.token LIKE ' . $search,
                'p.title LIKE ' . $search,
                'u.name LIKE ' . $search,
            ]) . ')');
        }

        $revoked = $this->getState('filter.revoked', '');
        if ($revoked !== '') {
            $query->where('t.revoked = ' . (int) $revoked);
        }

        $query->order('t.id DESC');

        return $query;
    }
}
