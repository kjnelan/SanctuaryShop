<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
?>
<div class="sanctuaryshop-products">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php foreach ($this->items as $item) : ?>
        <?php $displayPrice = $item->sale_price ? (float) $item->sale_price : (float) $item->price; ?>
        <div class="col">
            <div class="card h-100">
                <?php if ($item->image) : ?>
                    <img src="<?php echo $this->escape($item->image); ?>" class="card-img-top" alt="<?php echo $this->escape($item->title); ?>" loading="lazy" style="height:220px;object-fit:cover;">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=product&id=' . $item->id); ?>" class="stretched-link text-decoration-none">
                            <?php echo $this->escape($item->title); ?>
                        </a>
                    </h5>
                    <div class="mt-auto pt-2">
                        <?php if ($item->sale_price) : ?>
                            <span class="text-muted text-decoration-line-through small">$<?php echo number_format($item->price, 2); ?></span>
                            <span class="fw-bold text-danger ms-1">$<?php echo number_format($item->sale_price, 2); ?></span>
                        <?php else : ?>
                            <span class="fw-bold">$<?php echo number_format($item->price, 2); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <?php if ((int) $item->stock > 0) : ?>
                        <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.add'); ?>" method="post">
                            <input type="hidden" name="product_id" value="<?php echo (int) $item->id; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <?php echo HTMLHelper::_('form.token'); ?>
                            <button type="submit" class="btn btn-primary btn-sm w-100">Add to Cart</button>
                        </form>
                    <?php else : ?>
                        <button class="btn btn-secondary btn-sm w-100" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <?php if ($this->pagination->get('pages.total') > 1) : ?>
        <div class="mt-4"><?php echo $this->pagination->getPagesLinks(); ?></div>
    <?php endif; ?>
</div>
