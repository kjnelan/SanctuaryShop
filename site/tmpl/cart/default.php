<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<div class="sanctuaryshop-cart">
    <h1>Shopping Cart</h1>

    <?php if (empty($this->cartItems)) : ?>
        <div class="alert alert-info">Your cart is empty. <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>">Continue shopping</a></div>
    <?php else : ?>
        <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.update'); ?>" method="post">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-center" style="width:110px">Quantity</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->cartItems as $item) : ?>
                    <tr>
                        <td><?php echo $this->escape($item->title); ?><br><span class="small text-muted"><?php echo $this->escape($item->sku); ?></span></td>
                        <td class="text-end">$<?php echo number_format($item->unit_price, 2); ?></td>
                        <td class="text-center">
                            <input type="number" name="quantity[<?php echo (int) $item->product_id; ?>]" value="<?php echo (int) $item->quantity; ?>" min="0" class="form-control form-control-sm text-center">
                        </td>
                        <td class="text-end">$<?php echo number_format($item->total_price, 2); ?></td>
                        <td class="text-end">
                            <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.remove&product_id=' . (int) $item->product_id . '&' . HTMLHelper::_('form.token', '', false) . '=1'); ?>" class="btn btn-sm btn-outline-danger">&times;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Subtotal</td>
                        <td class="text-end fw-bold">$<?php echo number_format($this->subtotal, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <?php echo HTMLHelper::_('form.token'); ?>
            <div class="d-flex justify-content-between mt-3">
                <button type="submit" class="btn btn-outline-secondary">Update Cart</button>
                <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=checkout'); ?>" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        </form>
    <?php endif; ?>
</div>
