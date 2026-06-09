<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ProductsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'state', 'a.state',
                'category_id', 'a.category_id',
                'price', 'a.price',
                'ordering', 'a.ordering',
                'created', 'a.created',
            ];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['a.id', 'a.title', 'a.alias', 'a.price', 'a.sale_price', 'a.sku', 'a.stock', 'a.state', 'a.ordering', 'a.created', 'a.category_id']))
              ->from($db->quoteName('#__sanctuaryshop_products', 'a'));

        // Join category title
        $query->select($db->quoteName('c.title', 'category_title'))
              ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.category_id'));

        // Filter by state
        $state = (string) $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $state);
        }

        // Filter by category
        $categoryId = $this->getState('filter.category_id');
        if ($categoryId) {
            $query->where($db->quoteName('a.category_id') . ' = ' . (int) $categoryId);
        }

        // Filter by search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('a.sku') . ' LIKE ' . $search . ')');
            }
        }

        $orderCol  = $this->state->get('list.ordering', 'a.ordering');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
