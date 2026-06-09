<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Cart is stored in the PHP session as an array keyed by product_id.
 * Each entry: ['product_id' => int, 'quantity' => int]
 */
class CartModel extends BaseDatabaseModel
{
    private const SESSION_KEY = 'sanctuaryshop.cart';

    public function getItems(): array
    {
        $cart     = $this->getSessionCart();
        $items    = [];
        $db       = $this->getDatabase();

        if (empty($cart)) {
            return $items;
        }

        $ids   = array_map('intval', array_keys($cart));
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'price', 'sale_price', 'sku', 'image']))
            ->from($db->quoteName('#__sanctuaryshop_products'))
            ->whereIn($db->quoteName('id'), $ids)
            ->where($db->quoteName('state') . ' = 1');

        $products = $db->setQuery($query)->loadObjectList('id');

        foreach ($cart as $productId => $quantity) {
            if (!isset($products[$productId])) {
                continue;
            }
            $p = $products[$productId];
            $unitPrice = (float) ($p->sale_price ?: $p->price);
            $items[]   = (object) [
                'product_id'  => $productId,
                'title'       => $p->title,
                'sku'         => $p->sku,
                'image'       => $p->image,
                'unit_price'  => $unitPrice,
                'quantity'    => (int) $quantity,
                'total_price' => $unitPrice * $quantity,
            ];
        }

        return $items;
    }

    public function getSubtotal(): float
    {
        return array_sum(array_column($this->getItems(), 'total_price'));
    }

    public function getCount(): int
    {
        return (int) array_sum($this->getSessionCart());
    }

    public function addItem(int $productId, int $quantity): void
    {
        $cart = $this->getSessionCart();
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $this->saveSessionCart($cart);
    }

    public function updateQuantities(array $quantities): void
    {
        $cart = [];
        foreach ($quantities as $productId => $qty) {
            $qty = (int) $qty;
            if ($qty > 0) {
                $cart[(int) $productId] = $qty;
            }
        }
        $this->saveSessionCart($cart);
    }

    public function removeItem(int $productId): void
    {
        $cart = $this->getSessionCart();
        unset($cart[$productId]);
        $this->saveSessionCart($cart);
    }

    public function clear(): void
    {
        $this->saveSessionCart([]);
    }

    private function getSessionCart(): array
    {
        return Factory::getApplication()->getSession()->get(self::SESSION_KEY, []);
    }

    private function saveSessionCart(array $cart): void
    {
        Factory::getApplication()->getSession()->set(self::SESSION_KEY, $cart);
    }
}
