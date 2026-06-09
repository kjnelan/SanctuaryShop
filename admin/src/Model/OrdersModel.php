<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class OrdersModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'a.id', 'status', 'a.status', 'total', 'a.total', 'created', 'a.created', 'user_id', 'a.user_id'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['a.id', 'a.status', 'a.total', 'a.currency', 'a.billing_name', 'a.billing_email', 'a.payment_method', 'a.square_order_id', 'a.created', 'a.user_id']))
              ->from($db->quoteName('#__sanctuaryshop_orders', 'a'));

        $status = $this->getState('filter.status');
        if (!empty($status)) {
            $query->where($db->quoteName('a.status') . ' = ' . $db->quote($status));
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('a.billing_name') . ' LIKE ' . $search . ' OR ' . $db->quoteName('a.billing_email') . ' LIKE ' . $search . ')');
            }
        }

        $orderCol  = $this->state->get('list.ordering', 'a.created');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
