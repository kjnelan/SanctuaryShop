<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
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
        $app    = Factory::getApplication();
        $params = ComponentHelper::getParams('com_sanctuaryshop');

        $this->setState('filter.category_id', $app->input->getInt('catid', 0));
        $this->setState('filter.search', $app->input->getString('search', ''));
        $this->setState('filter.product_type', $app->input->getString('type', ''));

        $sort = $app->input->getString('sort', '');
        if ($sort === 'price_asc')  { $ordering = 'a.price';   $direction = 'ASC'; }
        elseif ($sort === 'price_desc') { $ordering = 'a.price'; $direction = 'DESC'; }
        elseif ($sort === 'newest')     { $ordering = 'a.created'; $direction = 'DESC'; }
        elseif ($sort === 'title')      { $ordering = 'a.title'; $direction = 'ASC'; }

        $this->setState('filter.sort', $sort);
        $this->setState('list.limit', $params->get('products_per_page', 12));

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select([
            'a.id', 'a.title', 'a.alias', 'a.description', 'a.price', 'a.sale_price',
            'a.sku', 'a.stock', 'a.image', 'a.category_id', 'a.product_type',
            'c.title AS category_title',
        ])
        ->from($db->quoteName('#__sanctuaryshop_products', 'a'))
        ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.category_id')
        ->where($db->quoteName('a.state') . ' = 1');

        $catId = (int) $this->getState('filter.category_id');
        if ($catId > 0) {
            $query->where($db->quoteName('a.category_id') . ' = ' . $catId);
        }

        $type = $this->getState('filter.product_type');
        if (!empty($type)) {
            $query->where($db->quoteName('a.product_type') . ' = ' . $db->quote($type));
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $s = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $s
                . ' OR ' . $db->quoteName('a.description') . ' LIKE ' . $s
                . ' OR ' . $db->quoteName('a.sku') . ' LIKE ' . $s . ')');
        }

        $query->order(
            $db->escape($this->state->get('list.ordering', 'a.ordering'))
            . ' ' .
            $db->escape($this->state->get('list.direction', 'ASC'))
        );

        return $query;
    }

    public function getCategories(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('c.id, c.title, COUNT(p.id) AS product_count')
            ->from($db->quoteName('#__categories', 'c'))
            ->innerJoin($db->quoteName('#__sanctuaryshop_products', 'p') . ' ON p.category_id = c.id AND p.state = 1')
            ->where('c.extension = ' . $db->quote('com_sanctuaryshop'))
            ->where('c.published = 1')
            ->group('c.id, c.title')
            ->order('c.title ASC');
        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}
