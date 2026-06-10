<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;

class ProductModel extends ItemModel
{
    public function getItem($pk = null): ?object
    {
        $pk = (int) ($pk ?: $this->getState('product.id'));

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'a.id', 'a.title', 'a.alias', 'a.description', 'a.price', 'a.sale_price',
                'a.sku', 'a.stock', 'a.image', 'a.category_id', 'a.product_type', 'a.params',
                'c.title AS category_title',
            ])
            ->from($db->quoteName('#__sanctuaryshop_products', 'a'))
            ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.category_id')
            ->where($db->quoteName('a.id') . ' = ' . $pk)
            ->where($db->quoteName('a.state') . ' = 1');

        return $db->setQuery($query)->loadObject() ?: null;
    }

    protected function populateState(): void
    {
        $app = Factory::getApplication();
        $this->setState('product.id', $app->getInput()->getInt('id'));
    }
}
