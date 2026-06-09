<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;

class ProductsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['a.ordering', 'a.title', 'a.price', 'a.created'];
        }
        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'ASC'): void
    {
        $app = \Joomla\CMS\Factory::getApplication();
        $this->setState('filter.category_id', $app->input->getInt('catid', 0));
        $params = ComponentHelper::getParams('com_sanctuaryshop');
        $this->setState('list.limit', $params->get('products_per_page', 12));
        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['a.id', 'a.title', 'a.alias', 'a.description', 'a.price', 'a.sale_price', 'a.sku', 'a.stock', 'a.image', 'a.category_id']))
              ->from($db->quoteName('#__sanctuaryshop_products', 'a'))
              ->where($db->quoteName('a.state') . ' = 1');

        $catId = (int) $this->getState('filter.category_id');
        if ($catId > 0) {
            $query->where($db->quoteName('a.category_id') . ' = ' . $catId);
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $s = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $s . ' OR ' . $db->quoteName('a.description') . ' LIKE ' . $s . ')');
        }

        $query->order($db->escape($this->state->get('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->state->get('list.direction', 'ASC')));

        return $query;
    }
}
