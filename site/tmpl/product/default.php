<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$item = $this->item;
$displayPrice = $item->sale_price ? (float) $item->sale_price : (float) $item->price;
?>
<div class="sanctuaryshop-product">
    <div class="row">
        <div class="col-md-5">
            <?php if ($item->image) : ?>
                <img src="<?php echo $this->escape($item->image); ?>" alt="<?php echo $this->escape($item->title); ?>" class="img-fluid rounded">
            <?php endif; ?>
        </div>
        <div class="col-md-7">
            <h1><?php echo $this->escape($item->title); ?></h1>
            <div class="mb-3">
                <?php if ($item->sale_price) : ?>
                    <span class="text-muted text-decoration-line-through fs-5">$<?php echo number_format($item->price, 2); ?></span>
                    <span class="fw-bold fs-3 text-danger ms-2">$<?php echo number_format($item->sale_price, 2); ?></span>
                <?php else : ?>
                    <span class="fw-bold fs-3">$<?php echo number_format($item->price, 2); ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($item->description)) : ?>
                <div class="product-description mb-3"><?php echo $item->description; ?></div>
            <?php endif; ?>

            <p class="text-muted small">SKU: <?php echo $this->escape($item->sku); ?></p>

            <?php if ((int) $item->stock > 0) : ?>
                <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.add'); ?>" method="post" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="product_id" value="<?php echo (int) $item->id; ?>">
                    <label for="qty" class="form-label mb-0">Qty:</label>
                    <input type="number" id="qty" name="quantity" value="1" min="1" max="<?php echo (int) $item->stock; ?>" class="form-control" style="width:80px">
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
            <?php else : ?>
                <div class="alert alert-warning">This item is currently out of stock.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
