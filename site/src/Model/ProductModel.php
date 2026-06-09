<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ItemModel;

class ProductModel extends ItemModel
{
    public function getItem($pk = null): ?object
    {
        $pk = (int) ($pk ?: $this->getState('product.id'));

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['a.id', 'a.title', 'a.alias', 'a.description', 'a.price', 'a.sale_price', 'a.sku', 'a.stock', 'a.image', 'a.category_id', 'a.params']))
            ->from($db->quoteName('#__sanctuaryshop_products', 'a'))
            ->where($db->quoteName('a.id') . ' = ' . $pk)
            ->where($db->quoteName('a.state') . ' = 1');

        return $db->setQuery($query)->loadObject() ?: null;
    }

    protected function populateState(): void
    {
        $app = \Joomla\CMS\Factory::getApplication();
        $this->setState('product.id', $app->input->getInt('id'));
    }
}
