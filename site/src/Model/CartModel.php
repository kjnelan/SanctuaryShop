<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class CartModel extends BaseDatabaseModel
{
    private const SESSION_KEY = 'sanctuaryshop.cart';
    private const COUPON_KEY  = 'sanctuaryshop.coupon';

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
        $this->saveCoupon(null);
    }

    public function applyCoupon(string $code): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_coupons'))
            ->where($db->quoteName('code') . ' = ' . $db->quote(strtoupper(trim($code))))
            ->where($db->quoteName('published') . ' = 1');
        $coupon = $db->setQuery($query, 0, 1)->loadObject();

        if (!$coupon) {
            return ['success' => false, 'message' => 'COM_SANCTUARYSHOP_COUPON_INVALID'];
        }

        if ($coupon->expires && $coupon->expires !== '0000-00-00 00:00:00') {
            $now = Factory::getDate()->toSql();
            if ($coupon->expires < $now) {
                return ['success' => false, 'message' => 'COM_SANCTUARYSHOP_COUPON_EXPIRED'];
            }
        }

        if ($coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit) {
            return ['success' => false, 'message' => 'COM_SANCTUARYSHOP_COUPON_EXHAUSTED'];
        }

        $subtotal = $this->getSubtotal();
        if ((float) $coupon->min_subtotal > 0 && $subtotal < (float) $coupon->min_subtotal) {
            return ['success' => false, 'message' => 'COM_SANCTUARYSHOP_COUPON_MIN_NOT_REACHED'];
        }

        $this->saveCoupon($coupon->code);
        return ['success' => true, 'message' => ''];
    }

    public function removeCoupon(): void
    {
        $this->saveCoupon(null);
    }

    public function getCouponCode(): ?string
    {
        return Factory::getApplication()->getSession()->get(self::COUPON_KEY);
    }

    public function getCouponDiscount(float $subtotal): float
    {
        $code = $this->getCouponCode();
        if (!$code) {
            return 0.00;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('type, value')
            ->from($db->quoteName('#__sanctuaryshop_coupons'))
            ->where($db->quoteName('code') . ' = ' . $db->quote($code))
            ->where($db->quoteName('published') . ' = 1');
        $coupon = $db->setQuery($query, 0, 1)->loadObject();

        if (!$coupon) {
            return 0.00;
        }

        if ($coupon->type === 'fixed') {
            return min((float) $coupon->value, $subtotal);
        }

        return round($subtotal * (float) $coupon->value / 100, 2);
    }

    private function getSessionCart(): array
    {
        return Factory::getApplication()->getSession()->get(self::SESSION_KEY, []);
    }

    private function saveSessionCart(array $cart): void
    {
        Factory::getApplication()->getSession()->set(self::SESSION_KEY, $cart);
    }

    private function saveCoupon(?string $code): void
    {
        Factory::getApplication()->getSession()->set(self::COUPON_KEY, $code);
    }
}
