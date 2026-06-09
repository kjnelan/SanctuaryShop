<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<div class="sanctuaryshop-cart">
    <h1>Shopping Cart</h1>

    <?php if (empty($this->cartItems)) : ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>">Continue shopping</a>
        </div>
    <?php else : ?>
        <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.update'); ?>" method="post" id="cartForm">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-center" style="width:110px">Qty</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Remove</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->cartItems as $item) : ?>
                    <tr>
                        <td>
                            <?php echo $this->escape($item->title); ?>
                            <?php if ($item->sku) : ?><br><span class="small text-muted"><?php echo $this->escape($item->sku); ?></span><?php endif; ?>
                        </td>
                        <td class="text-end">$<?php echo number_format($item->unit_price, 2); ?></td>
                        <td class="text-center">
                            <input type="number" name="quantity[<?php echo (int) $item->product_id; ?>]"
                                   value="<?php echo (int) $item->quantity; ?>" min="0" max="999"
                                   class="form-control form-control-sm text-center">
                        </td>
                        <td class="text-end">$<?php echo number_format($item->total_price, 2); ?></td>
                        <td class="text-end">
                            <button type="submit" name="remove_item" value="<?php echo (int) $item->product_id; ?>"
                                    class="btn btn-sm btn-outline-danger" title="Remove"
                                    onclick="document.getElementById('remove_product_id').value=this.value; document.getElementById('cartForm').action='<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.remove'); ?>';">
                                &times;
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Subtotal</td>
                        <td class="text-end">$<?php echo number_format($this->subtotal, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <input type="hidden" id="remove_product_id" name="product_id" value="0">
            <?php echo HTMLHelper::_('form.token'); ?>

            <div class="d-flex justify-content-between mt-3">
                <button type="submit" class="btn btn-outline-secondary">Update Cart</button>
                <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=checkout'); ?>" class="btn btn-primary btn-lg">
                    Checkout &rarr;
                </a>
            </div>
        </form>
    <?php endif; ?>
</div>
