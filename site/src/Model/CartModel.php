<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class CartModel extends BaseDatabaseModel
{
    private const SESSION_KEY = 'sanctuaryshop.cart';
    private const COUPON_KEY  = 'sanctuaryshop.coupon';

    /**
     * Cart format: [ cart_key => ['product_id' => int, 'quantity' => int, 'variant_info' => array|null] ]
     * cart_key = "{productId}_{variantHash}" where variantHash = md5(json_encode(sorted variant_info)) or "0"
     */
    private function makeCartKey(int $productId, ?array $variantInfo): string
    {
        if (empty($variantInfo)) {
            return $productId . '_0';
        }
        ksort($variantInfo);
        return $productId . '_' . substr(md5(json_encode($variantInfo)), 0, 8);
    }

    public function getItems(): array
    {
        $cart     = $this->getSessionCart();
        $items    = [];
        $db       = $this->getDatabase();

        if (empty($cart)) {
            return $items;
        }

        $ids = [];
        foreach ($cart as $entry) {
            $ids[] = (int) $entry['product_id'];
        }
        $ids = array_unique($ids);

        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'price', 'sale_price', 'sku', 'image', 'product_type', 'subscription_plan_id', 'stock']))
            ->from($db->quoteName('#__sanctuaryshop_products'))
            ->whereIn($db->quoteName('id'), $ids)
            ->where($db->quoteName('state') . ' = 1');

        $products = $db->setQuery($query)->loadObjectList('id');

        foreach ($cart as $cartKey => $entry) {
            $productId   = (int) $entry['product_id'];
            $quantity    = (int) $entry['quantity'];
            $variantInfo = $entry['variant_info'] ?? null;

            if (!isset($products[$productId])) {
                continue;
            }
            $p = $products[$productId];
            $unitPrice = (float) ($p->sale_price ?: $p->price);

            if (!empty($variantInfo)) {
                // Never trust prices or labels stored in the browser session.
                $variantInfo = $this->canonicalizeVariantInfo($productId, $variantInfo);
                if ($variantInfo === false || $variantInfo === null) {
                    continue;
                }
                $modifier = array_sum(array_map(static fn($sel) => (float) $sel['price_modifier'], $variantInfo));
                $unitPrice += $modifier;
            }

            $unitPrice = round($unitPrice, 2);

            $items[] = (object) [
                'cart_key'    => $cartKey,
                'product_id'  => $productId,
                'title'       => $p->title,
                'sku'         => $p->sku,
                'image'       => $p->image,
                'product_type' => $p->product_type,
                'subscription_plan_id' => $p->subscription_plan_id,
                'unit_price'  => $unitPrice,
                'quantity'    => $quantity,
                'total_price' => round($unitPrice * $quantity, 2),
                'variant_info' => $variantInfo,
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
        return array_sum(array_map(static fn($item) => (int) $item->quantity, $this->getItems()));
    }

    public function addItem(int $productId, int $quantity, ?array $variantInfo = null): void
    {
        $quantity = min(999, max(1, $quantity));
        $db = $this->getDatabase();
        $product = $db->setQuery(
            $db->getQuery(true)
                ->select(['id', 'state'])
                ->from($db->quoteName('#__sanctuaryshop_products'))
                ->where('id = ' . $productId)
        )->loadObject();
        if (!$product || !(int) $product->state) {
            throw new \RuntimeException('Product is not available.');
        }

        $variantInfo = !empty($variantInfo)
            ? $this->canonicalizeVariantInfo($productId, $variantInfo)
            : null;
        if ($variantInfo === false) {
            throw new \RuntimeException('Invalid product option.');
        }

        $cart    = $this->getSessionCart();
        $cartKey = $this->makeCartKey($productId, $variantInfo);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'product_id'  => $productId,
                'quantity'    => $quantity,
                'variant_info' => $variantInfo,
            ];
        }
        $this->saveSessionCart($cart);
    }

    public function updateQuantities(array $quantities): void
    {
        $cart    = $this->getSessionCart();
        $newCart = [];
        foreach ($quantities as $cartKey => $qty) {
            $qty = min(999, max(0, (int) $qty));
            if ($qty > 0 && isset($cart[$cartKey])) {
                $newCart[$cartKey] = $cart[$cartKey];
                $newCart[$cartKey]['quantity'] = $qty;
            }
        }
        $this->saveSessionCart($newCart);
    }

    public function removeItem(string $cartKey): void
    {
        $cart = $this->getSessionCart();
        unset($cart[$cartKey]);
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

        return round($subtotal * min(100, max(0, (float) $coupon->value)) / 100, 2);
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

    /**
     * Convert browser-supplied option data into canonical database-backed data.
     * null means no options; false means invalid options.
     */
    private function canonicalizeVariantInfo(int $productId, array $variantInfo): array|false|null
    {
        if (empty($variantInfo)) {
            return null;
        }

        $optionIds = [];
        foreach ($variantInfo as $selection) {
            $optionId = (int) ($selection['option_id'] ?? 0);
            if ($optionId <= 0) {
                return false;
            }
            $optionIds[] = $optionId;
        }

        $db = $this->getDatabase();
        $options = $db->setQuery(
            $db->getQuery(true)
                ->select(['o.id', 'o.label', 'o.price_modifier', 'o.sku_suffix', 'o.stock', 'v.name AS variant_name'])
                ->from($db->quoteName('#__sanctuaryshop_product_variant_options', 'o'))
                ->innerJoin($db->quoteName('#__sanctuaryshop_product_variants', 'v') . ' ON v.id = o.variant_id')
                ->where('o.product_id = ' . $productId)
                ->whereIn('o.id', $optionIds)
        )->loadObjectList('id');

        if (count($options) !== count(array_unique($optionIds))) {
            return false;
        }

        $canonical = [];
        foreach ($optionIds as $optionId) {
            $option = $options[$optionId] ?? null;
            if (!$option || isset($canonical[$option->variant_name])) {
                return false;
            }
            $canonical[$option->variant_name] = [
                'option_id'      => (int) $option->id,
                'label'          => (string) $option->label,
                'price_modifier' => (float) $option->price_modifier,
                'sku_suffix'     => (string) $option->sku_suffix,
                'stock'          => (int) $option->stock,
            ];
        }

        ksort($canonical);
        return $canonical;
    }
}
