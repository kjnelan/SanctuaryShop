<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$baseUrl     = Route::_('index.php?option=com_sanctuaryshop&view=products');
$currencyMap = ['USD'=>'$','EUR'=>'€','GBP'=>'£','CAD'=>'CA$','AUD'=>'A$'];
$sym         = $currencyMap[$this->currency] ?? $this->currency . ' ';

$typeLabels = [
    'physical'     => ['label' => 'Physical',     'class' => 'bg-secondary'],
    'digital'      => ['label' => 'Digital',       'class' => 'bg-info text-dark'],
    'subscription' => ['label' => 'Subscription',  'class' => 'bg-primary'],
    'service'      => ['label' => 'Service',        'class' => 'bg-success'],
];
?>
<div class="com-sanctuaryshop-products">

    <!-- Search + Sort bar -->
    <form method="get" action="<?php echo $baseUrl; ?>" class="row g-2 mb-4 align-items-end">
        <input type="hidden" name="option" value="com_sanctuaryshop">
        <input type="hidden" name="view" value="products">
        <?php if ($this->activeCatId) : ?>
            <input type="hidden" name="catid" value="<?php echo (int) $this->activeCatId; ?>">
        <?php endif; ?>
        <div class="col-sm-5 col-md-6">
            <label class="visually-hidden" for="ss-search"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
            <div class="input-group">
                <input type="search" id="ss-search" name="search" class="form-control"
                       value="<?php echo $this->escape($this->search); ?>"
                       placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_SEARCH_PRODUCTS'); ?>">
                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <?php if ($this->search || $this->activeCatId || $this->activeType || $this->sort) : ?>
                    <a href="<?php echo $baseUrl; ?>" class="btn btn-outline-secondary"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-sm-4 col-md-3">
            <label class="visually-hidden" for="ss-type"><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT_TYPE'); ?></label>
            <select id="ss-type" name="type" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('COM_SANCTUARYSHOP_ALL_TYPES'); ?></option>
                <option value="physical"     <?php echo $this->activeType === 'physical'     ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_PHYSICAL'); ?></option>
                <option value="digital"      <?php echo $this->activeType === 'digital'      ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_DIGITAL'); ?></option>
                <option value="subscription" <?php echo $this->activeType === 'subscription' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_SUBSCRIPTION'); ?></option>
                <option value="service"      <?php echo $this->activeType === 'service'      ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_PRODUCT_TYPE_SERVICE'); ?></option>
            </select>
        </div>
        <div class="col-sm-3 col-md-3">
            <label class="visually-hidden" for="ss-sort"><?php echo Text::_('COM_SANCTUARYSHOP_SORT_BY'); ?></label>
            <select id="ss-sort" name="sort" class="form-select" onchange="this.form.submit()">
                <option value=""><?php echo Text::_('COM_SANCTUARYSHOP_SORT_FEATURED'); ?></option>
                <option value="title"      <?php echo $this->sort === 'title'      ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_SORT_TITLE'); ?></option>
                <option value="price_asc"  <?php echo $this->sort === 'price_asc'  ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_SORT_PRICE_ASC'); ?></option>
                <option value="price_desc" <?php echo $this->sort === 'price_desc' ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_SORT_PRICE_DESC'); ?></option>
                <option value="newest"     <?php echo $this->sort === 'newest'     ? 'selected' : ''; ?>><?php echo Text::_('COM_SANCTUARYSHOP_SORT_NEWEST'); ?></option>
            </select>
        </div>
    </form>

    <div class="row">

        <!-- Category sidebar (hidden if no categories) -->
        <?php if (!empty($this->categories)) : ?>
        <div class="col-lg-2 col-md-3 mb-4">
            <div class="list-group">
                <a href="<?php echo $baseUrl . ($this->search ? '&search=' . urlencode($this->search) : ''); ?>"
                   class="list-group-item list-group-item-action <?php echo !$this->activeCatId ? 'active' : ''; ?>">
                    <?php echo Text::_('COM_SANCTUARYSHOP_ALL_PRODUCTS'); ?>
                </a>
                <?php foreach ($this->categories as $cat) : ?>
                <a href="<?php echo $baseUrl . '&catid=' . (int) $cat->id . ($this->search ? '&search=' . urlencode($this->search) : ''); ?>"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $this->activeCatId === (int) $cat->id ? 'active' : ''; ?>">
                    <?php echo $this->escape($cat->title); ?>
                    <span class="badge rounded-pill <?php echo $this->activeCatId === (int) $cat->id ? 'bg-light text-dark' : 'bg-secondary'; ?>">
                        <?php echo (int) $cat->product_count; ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-10 col-md-9">
        <?php else : ?>
        <div class="col-12">
        <?php endif; ?>

            <?php if (empty($this->items)) : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('COM_SANCTUARYSHOP_NO_PRODUCTS_FOUND'); ?>
                    <?php if ($this->search || $this->activeCatId || $this->activeType) : ?>
                        <a href="<?php echo $baseUrl; ?>" class="alert-link"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
                    <?php endif; ?>
                </div>
            <?php else : ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-<?php echo !empty($this->categories) ? 'lg-3' : 'lg-4'; ?> g-4">
            <?php foreach ($this->items as $item) : ?>
                <?php
                $price       = $item->sale_price ? (float) $item->sale_price : (float) $item->price;
                $typeInfo    = $typeLabels[$item->product_type] ?? ['label' => ucfirst($item->product_type), 'class' => 'bg-secondary'];
                $isDigital   = $item->product_type === 'digital';
                $isService   = in_array($item->product_type, ['service', 'subscription']);
                $inStock     = (int) $item->stock > 0;
                $productUrl  = Route::_('index.php?option=com_sanctuaryshop&view=product&id=' . (int) $item->id . '&alias=' . $item->alias);
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <?php if ($item->image) : ?>
                                <a href="<?php echo $productUrl; ?>">
                                    <img src="<?php echo $this->escape($item->image); ?>"
                                         class="card-img-top" alt="<?php echo $this->escape($item->title); ?>"
                                         loading="lazy" style="height:220px;object-fit:cover;">
                                </a>
                            <?php else : ?>
                                <a href="<?php echo $productUrl; ?>" class="d-block bg-light text-center py-5" style="height:120px;line-height:70px;">
                                    <span class="text-muted"><?php echo $this->escape($item->title[0]); ?></span>
                                </a>
                            <?php endif; ?>
                            <span class="badge <?php echo $typeInfo['class']; ?> position-absolute top-0 start-0 m-2">
                                <?php echo $typeInfo['label']; ?>
                            </span>
                            <?php if ($item->sale_price) : ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">SALE</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">
                                <a href="<?php echo $productUrl; ?>" class="text-decoration-none text-dark stretched-link">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                            </h5>
                            <?php if ($item->category_title) : ?>
                                <p class="text-muted small mb-2"><?php echo $this->escape($item->category_title); ?></p>
                            <?php endif; ?>
                            <div class="mt-auto pt-2">
                                <?php if ($item->sale_price) : ?>
                                    <span class="text-muted text-decoration-line-through small"><?php echo $sym . number_format($item->price, 2); ?></span>
                                    <span class="fw-bold text-danger ms-1 fs-5"><?php echo $sym . number_format($item->sale_price, 2); ?></span>
                                <?php else : ?>
                                    <span class="fw-bold fs-5"><?php echo $sym . number_format($item->price, 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pb-3">
                            <?php if ($isDigital || $isService) : ?>
                                <a href="<?php echo $productUrl; ?>" class="btn btn-outline-primary btn-sm w-100">
                                    <?php echo Text::_('COM_SANCTUARYSHOP_VIEW_DETAILS'); ?>
                                </a>
                            <?php elseif ($inStock) : ?>
                                <form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=cart.add'); ?>" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo (int) $item->id; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <?php echo Text::_('COM_SANCTUARYSHOP_ADD_TO_CART'); ?>
                                    </button>
                                </form>
                            <?php else : ?>
                                <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                    <?php echo Text::_('COM_SANCTUARYSHOP_OUT_OF_STOCK'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <?php if ($this->pagination->get('pages.total') > 1) : ?>
                <div class="mt-4 d-flex justify-content-center">
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>
            <?php endif; ?>
        </div><!-- /col -->
    </div><!-- /row -->
</div>
