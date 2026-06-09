<?php defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
?>
<div class="sanctuaryshop-confirmation text-center py-5">
    <div class="mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="text-success" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
        </svg>
    </div>
    <h1 class="text-success">Thank You!</h1>
    <p class="lead">Your order has been placed successfully.</p>
    <?php if ($this->orderId) : ?>
        <p>Order reference: <strong>#<?php echo str_pad((int) $this->orderId, 5, '0', STR_PAD_LEFT); ?></strong></p>
    <?php endif; ?>
    <p>You will receive a confirmation email shortly.</p>
    <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" class="btn btn-primary mt-3">Continue Shopping</a>
</div>
