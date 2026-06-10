<?php
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$item   = $this->item;
$params = ComponentHelper::getParams('com_sanctuaryshop');

$currencyMap  = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$currency     = strtoupper($params->get('currency', 'USD'));
$sym          = $currencyMap[$currency] ?? $currency . ' ';

$basePrice = $item->sale_price ? (float) $item->sale_price : (float) $item->price;
$isDigital = $item->product_type === 'digital';
$isService = in_array($item->product_type, ['service', 'subscription']);
$inStock   = (int) $item->stock > 0;

$typeLabels = [
    'physical'     => ['label' => Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_PHYSICAL'),    'class' => 'bg-secondary'],
    'digital'      => ['label' => Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_DIGITAL'),     'class' => 'bg-info text-dark'],
    'subscription' => ['label' => Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_SUBSCRIPTION'),'class' => 'bg-primary'],
    'service'      => ['label' => Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_SERVICE'),      'class' => 'bg-success'],
];
$typeInfo = $typeLabels[$item->product_type] ?? ['label' => ucfirst($item->product_type), 'class' => 'bg-secondary'];
?>
<div class="com-sanctuaryshop-product">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>">
                    <?php echo Text::_('COM_SANCTUARYSHOP_PRODUCTS'); ?>
                </a>
            </li>
            <?php if (!empty($item->category_title)) : ?>
            <li class="breadcrumb-item">
                <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products&catid=' . (int) $item->category_id); ?>">
                    <?php echo $this->escape($item->category_title); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $this->escape($item->title); ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Product image -->
        <div class="col-md-5">
            <?php if ($item->image) : ?>
                <img src="<?php echo $this->escape($item->image); ?>"
                     alt="<?php echo $this->escape($item->title); ?>"
                     class="img-fluid rounded shadow-sm mb-2"
                     id="main-product-image">
            <?php else : ?>
                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-2" style="min-height:300px;">
                    <span class="text-muted fs-1"><?php echo $this->escape($item->title[0] ?? '?'); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($this->images)) : ?>
            <div class="d-flex flex-wrap gap-2 mt-2" id="image-thumbnails">
                <?php if ($item->image) : ?>
                <img src="<?php echo $this->escape($item->image); ?>"
                     alt="<?php echo $this->escape($item->title); ?>"
                     class="img-thumbnail thumb-active"
                     style="width:64px;height:64px;object-fit:cover;cursor:pointer;"
                     data-full="<?php echo $this->escape($item->image); ?>">
                <?php endif; ?>
                <?php foreach ($this->images as $img) : ?>
                <img src="<?php echo $this->escape($img->image); ?>"
                     alt="<?php echo $this->escape($img->alt_text ?: $item->title); ?>"
                     class="img-thumbnail"
                     style="width:64px;height:64px;object-fit:cover;cursor:pointer;"
                     data-full="<?php echo $this->escape($img->image); ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product info -->
        <div class="col-md-7">
            <div class="mb-2">
                <span class="badge <?php echo $typeInfo['class']; ?>"><?php echo $typeInfo['label']; ?></span>
                <?php if (!empty($item->category_title)) : ?>
                    <span class="text-muted small ms-2"><?php echo $this->escape($item->category_title); ?></span>
                <?php endif; ?>
            </div>

            <h1 class="mb-3"><?php echo $this->escape($item->title); ?></h1>

            <!-- Price -->
            <div class="mb-4" id="price-display">
                <?php if ($item->sale_price) : ?>
                    <span class="text-muted text-decoration-line-through fs-5 me-2"><?php echo $sym . number_format($item->price, 2); ?></span>
                    <span class="fw-bold fs-2 text-danger" id="current-price"><?php echo $sym . number_format($item->sale_price, 2); ?></span>
                    <span class="badge bg-danger ms-2">SALE</span>
                <?php else : ?>
                    <span class="fw-bold fs-2" id="current-price"><?php echo $sym . number_format($item->price, 2); ?></span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <?php if (!empty($item->description)) : ?>
                <div class="product-description mb-4 text-body-secondary">
                    <?php echo $item->description; ?>
                </div>
            <?php endif; ?>

            <!-- Digital product delivery note -->
            <?php if ($isDigital) : ?>
                <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                    <?php echo Text::_('COM_SANCTUARYSHOP_DIGITAL_DELIVERY_NOTE'); ?>
                </div>
            <?php endif; ?>

            <!-- SKU -->
            <?php if ($item->sku) : ?>
                <p class="text-muted small mb-3"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_SKU'); ?>: <span id="sku-display"><?php echo $this->escape($item->sku); ?></span></p>
            <?php endif; ?>

            <!-- Variants -->
            <?php if (!empty($this->variants)) : ?>
            <div class="mb-3" id="variant-selectors">
                <?php foreach ($this->variants as $variant) : ?>
                <div class="mb-2">
                    <label class="form-label fw-semibold"><?php echo $this->escape($variant->name); ?></label>
                    <select class="form-select variant-select"
                            data-variant-name="<?php echo $this->escape($variant->name); ?>"
                            data-base-price="<?php echo $basePrice; ?>">
                        <option value="" data-mod="0">— Select <?php echo $this->escape($variant->name); ?> —</option>
                        <?php foreach ($variant->options as $opt) : ?>
                        <option value="<?php echo (int) $opt->id; ?>"
                                data-mod="<?php echo (float) $opt->price_modifier; ?>"
                                data-sku-suffix="<?php echo $this->escape($opt->sku_suffix); ?>"
                                data-label="<?php echo $this->escape($opt->label); ?>">
                            <?php echo $this->escape($opt->label); ?>
                            <?php if ((float) $opt->price_modifier != 0) : ?>
                            (<?php echo (float) $opt->price_modifier > 0 ? '+' : ''; ?><?php echo $sym . number_format(abs($opt->price_modifier), 2); ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="variant-info-json" value="">
            <?php endif; ?>

            <!-- Add to cart / action -->
            <?php if ($inStock || $isDigital) : ?>
                <?php if ($isDigital || $isService) : ?>
                <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.buyNow'); ?>" method="post" class="d-flex gap-2 align-items-center flex-wrap">
                    <input type="hidden" name="product_id" value="<?php echo (int) $item->id; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="variant_info" class="variant-info-field" value="">
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?php echo Text::_('COM_SANCTUARYSHOP_BUY_NOW'); ?>
                    </button>
                </form>
                <?php elseif ($inStock) : ?>
                <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.add'); ?>" method="post"
                      class="d-flex gap-2 align-items-center flex-wrap mb-2" id="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo (int) $item->id; ?>">
                    <div class="d-flex align-items-center gap-2">
                        <label for="qty" class="form-label mb-0 fw-semibold"><?php echo Text::_('COM_SANCTUARYSHOP_QUANTITY'); ?>:</label>
                        <input type="number" id="qty" name="quantity" value="1" min="1"
                               max="<?php echo (int) $item->stock; ?>"
                               class="form-control text-center" style="width:80px">
                    </div>
                    <input type="hidden" name="variant_info" class="variant-info-field" value="">
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <button type="submit" class="btn btn-outline-primary btn-lg">
                        <?php echo Text::_('COM_SANCTUARYSHOP_ADD_TO_CART'); ?>
                    </button>
                </form>
                <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.buyNow'); ?>" method="post"
                      class="d-flex gap-2 align-items-center flex-wrap">
                    <input type="hidden" name="product_id" value="<?php echo (int) $item->id; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="variant_info" class="variant-info-field" value="">
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?php echo Text::_('COM_SANCTUARYSHOP_BUY_NOW'); ?>
                    </button>
                </form>
                <?php if ($item->stock <= 5) : ?>
                    <p class="text-warning mt-2 small">
                        <?php echo Text::sprintf('COM_SANCTUARYSHOP_ONLY_N_LEFT', (int) $item->stock); ?>
                    </p>
                <?php endif; ?>
                <?php endif; ?>
            <?php else : ?>
                <div class="alert alert-warning"><?php echo Text::_('COM_SANCTUARYSHOP_OUT_OF_STOCK'); ?></div>
                <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" class="btn btn-outline-secondary mt-2">
                    <?php echo Text::_('COM_SANCTUARYSHOP_CONTINUE_SHOPPING'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    // Thumbnail gallery
    var thumbnails = document.querySelectorAll('#image-thumbnails img');
    var mainImg    = document.getElementById('main-product-image');
    thumbnails.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (mainImg) {
                mainImg.src = thumb.dataset.full;
                mainImg.alt = thumb.alt;
            }
            thumbnails.forEach(function (t) { t.classList.remove('thumb-active'); });
            thumb.classList.add('thumb-active');
        });
    });

    // Variant price update
    var basePriceEl = document.getElementById('current-price');
    var basePrice   = <?php echo $basePrice; ?>;
    var sym         = '<?php echo addslashes($sym); ?>';

    function updateVariantInfo() {
        var selects  = document.querySelectorAll('.variant-select');
        var modifier = 0;
        var info     = {};
        selects.forEach(function (sel) {
            var opt  = sel.options[sel.selectedIndex];
            var mod  = parseFloat(opt.dataset.mod || 0);
            modifier += mod;
            if (opt.value) {
                info[sel.dataset.variantName] = {
                    option_id: opt.value,
                    label: opt.dataset.label,
                    price_modifier: mod,
                    sku_suffix: opt.dataset.skuSuffix || ''
                };
            }
        });

        var newPrice = basePrice + modifier;
        if (basePriceEl) {
            basePriceEl.textContent = sym + newPrice.toFixed(2);
        }

        var infoJson = Object.keys(info).length ? JSON.stringify(info) : '';
        document.querySelectorAll('.variant-info-field').forEach(function (f) {
            f.value = infoJson;
        });
    }

    document.querySelectorAll('.variant-select').forEach(function (sel) {
        sel.addEventListener('change', updateVariantInfo);
    });
})();
</script>
