<?php
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;

$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$currency    = strtoupper(ComponentHelper::getParams('com_sanctuaryshop')->get('currency', 'USD'));
$sym         = $currencyMap[$currency] ?? $currency . ' ';

$cartUrl     = Route::_('index.php?option=com_sanctuaryshop&view=cart');
$checkoutUrl = Route::_('index.php?option=com_sanctuaryshop&view=checkout');
?>
<div class="mod-sanctuaryshop-cart dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle position-relative"
            type="button"
            id="cartDropdownBtn"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Shopping Cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L1.01 3.607 .61 2H.5A.5.5 0 0 1 0 1.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </svg>
        <?php if ($cartCount > 0) : ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?php echo (int) $cartCount; ?>
        </span>
        <?php endif; ?>
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:280px;" aria-labelledby="cartDropdownBtn">
        <?php if (empty($cartItems)) : ?>
            <p class="text-muted mb-2 small">Your cart is empty.</p>
        <?php else : ?>
            <?php foreach ($cartItems as $item) : ?>
            <div class="d-flex justify-content-between align-items-start mb-2 small">
                <div class="me-2">
                    <div class="fw-semibold"><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php if (!empty($item->variant_info)) : ?>
                    <div class="text-muted" style="font-size:0.75em;">
                        <?php foreach ($item->variant_info as $vName => $vSel) : ?>
                        <?php echo htmlspecialchars($vName, ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars($vSel['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>&nbsp;
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="text-muted">×<?php echo (int) $item->quantity; ?></div>
                </div>
                <div class="text-nowrap"><?php echo $sym . number_format($item->total_price, 2); ?></div>
            </div>
            <?php endforeach; ?>
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold mb-3">
                <span>Subtotal</span>
                <span><?php echo $sym . number_format($subtotal, 2); ?></span>
            </div>
        <?php endif; ?>
        <div class="d-grid gap-2">
            <a href="<?php echo $cartUrl; ?>" class="btn btn-outline-primary btn-sm">View Cart</a>
            <?php if (!empty($cartItems)) : ?>
            <a href="<?php echo $checkoutUrl; ?>" class="btn btn-primary btn-sm">Checkout</a>
            <?php endif; ?>
        </div>
    </div>
</div>
