<?php defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_sanctuaryshop&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="row">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-body">
                    <?php echo $this->form->renderField('title'); ?>
                    <?php echo $this->form->renderField('alias'); ?>
                    <?php echo $this->form->renderField('description'); ?>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><?php echo Text::_('COM_SANCTUARYSHOP_PRICING_INVENTORY'); ?></h3></div>
                <div class="card-body">
                    <?php echo $this->form->renderField('price'); ?>
                    <?php echo $this->form->renderField('sale_price'); ?>
                    <?php echo $this->form->renderField('sku'); ?>
                    <?php echo $this->form->renderField('stock'); ?>
                </div>
            </div>

            <!-- Product Variants -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><?php echo Text::_('COM_SANCTUARYSHOP_VARIANTS'); ?></h3></div>
                <div class="card-body">
                    <div id="variants-container">
                        <?php foreach ($this->productVariants as $vi => $variant) : ?>
                        <div class="variant-group card mb-2" data-variant-idx="<?php echo $vi; ?>">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <input type="text" name="variants[<?php echo $vi; ?>][name]"
                                           value="<?php echo $this->escape($variant->name); ?>"
                                           class="form-control form-control-sm"
                                           placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_GROUP_NAME'); ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-variant-group">✕</button>
                                </div>
                                <table class="table table-sm mb-1 variant-options-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_OPTION_LABEL'); ?></th>
                                            <th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_PRICE_MOD'); ?></th>
                                            <th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_SKU_SUFFIX'); ?></th>
                                            <th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_STOCK'); ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($variant->options as $oi => $opt) : ?>
                                        <tr>
                                            <td><input type="text" name="variants[<?php echo $vi; ?>][options][<?php echo $oi; ?>][label]" value="<?php echo $this->escape($opt->label); ?>" class="form-control form-control-sm"></td>
                                            <td><input type="number" name="variants[<?php echo $vi; ?>][options][<?php echo $oi; ?>][price_modifier]" value="<?php echo (float) $opt->price_modifier; ?>" class="form-control form-control-sm" step="0.01"></td>
                                            <td><input type="text" name="variants[<?php echo $vi; ?>][options][<?php echo $oi; ?>][sku_suffix]" value="<?php echo $this->escape($opt->sku_suffix); ?>" class="form-control form-control-sm"></td>
                                            <td><input type="number" name="variants[<?php echo $vi; ?>][options][<?php echo $oi; ?>][stock]" value="<?php echo (int) $opt->stock; ?>" class="form-control form-control-sm" step="1"></td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-variant-option">✕</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-outline-secondary add-variant-option"><?php echo Text::_('COM_SANCTUARYSHOP_ADD_VARIANT_OPTION'); ?></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-variant-group"><?php echo Text::_('COM_SANCTUARYSHOP_ADD_VARIANT_GROUP'); ?></button>
                </div>
            </div>

            <?php if ($this->item->product_type === 'digital') : ?>
            <div class="card mb-3" id="digital-files-card">
                <div class="card-header"><h3 class="card-title"><?php echo Text::_('COM_SANCTUARYSHOP_DOWNLOADABLE_FILES'); ?></h3></div>
                <div class="card-body">
                    <p class="text-muted small"><?php echo Text::_('COM_SANCTUARYSHOP_DOWNLOADABLE_FILES_DESC'); ?></p>
                    <table class="table table-sm" id="files-table">
                        <thead>
                            <tr>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE_LABEL'); ?></th>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILENAME'); ?></th>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILESIZE'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="files-tbody">
                        <?php if (!empty($this->productFiles)) : ?>
                            <?php foreach ($this->productFiles as $f) : ?>
                            <tr data-file-id="<?php echo (int) $f->id; ?>">
                                <td><input type="text" name="files[<?php echo (int) $f->id; ?>][label]" value="<?php echo $this->escape($f->label); ?>" class="form-control form-control-sm" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE_LABEL'); ?>"></td>
                                <td><input type="text" name="files[<?php echo (int) $f->id; ?>][filename]" value="<?php echo $this->escape($f->filename); ?>" class="form-control form-control-sm" placeholder="relative/path/to/file.zip"></td>
                                <td><?php echo $f->filesize > 0 ? number_format($f->filesize / 1048576, 1) . ' MB' : '—'; ?></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-file-row">✕</button></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-file-row"><?php echo Text::_('COM_SANCTUARYSHOP_ADD_FILE'); ?></button>
                    <input type="hidden" name="files_deleted" id="files-deleted" value="">
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <?php echo $this->form->renderField('product_type'); ?>
                    <?php echo $this->form->renderField('state'); ?>
                    <?php echo $this->form->renderField('category_id'); ?>
                    <?php echo $this->form->renderField('image'); ?>
                </div>
            </div>
            <!-- Additional Images -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title fs-6"><?php echo Text::_('COM_SANCTUARYSHOP_ADDITIONAL_IMAGES'); ?></h3></div>
                <div class="card-body p-2">
                    <table class="table table-sm mb-1" id="images-table">
                        <thead>
                            <tr>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_IMAGE_URL'); ?></th>
                                <th><?php echo Text::_('COM_SANCTUARYSHOP_IMAGE_ALT'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="images-tbody">
                        <?php foreach ($this->productImages as $img) : ?>
                            <tr data-img-id="<?php echo (int) $img->id; ?>">
                                <td><input type="text" name="product_images[<?php echo (int) $img->id; ?>][image]" value="<?php echo $this->escape($img->image); ?>" class="form-control form-control-sm" placeholder="https://..."></td>
                                <td><input type="text" name="product_images[<?php echo (int) $img->id; ?>][alt_text]" value="<?php echo $this->escape($img->alt_text); ?>" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-image-row">✕</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-image-row"><?php echo Text::_('COM_SANCTUARYSHOP_ADD_IMAGE'); ?></button>
                    <input type="hidden" name="images_deleted" id="images-deleted" value="">
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
(function () {
    var newFileIdx = 0;
    var newImgIdx  = 0;
    var newVarIdx  = 1000;
    var newOptIdx  = 1000;

    // ---- Files ----
    document.getElementById('add-file-row')?.addEventListener('click', function () {
        newFileIdx--;
        var tbody = document.getElementById('files-tbody');
        var tr = document.createElement('tr');
        tr.dataset.fileId = newFileIdx;
        tr.innerHTML = '<td><input type="text" name="files[' + newFileIdx + '][label]" class="form-control form-control-sm" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE_LABEL'); ?>"></td>'
            + '<td><input type="text" name="files[' + newFileIdx + '][filename]" class="form-control form-control-sm" placeholder="relative/path/to/file.zip"></td>'
            + '<td>—</td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-danger remove-file-row">✕</button></td>';
        tbody.appendChild(tr);
    });

    document.getElementById('files-tbody')?.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-file-row')) {
            var tr = e.target.closest('tr');
            var id = parseInt(tr.dataset.fileId);
            if (id > 0) {
                var del = document.getElementById('files-deleted');
                del.value = del.value ? del.value + ',' + id : String(id);
            }
            tr.remove();
        }
    });

    // ---- Additional Images ----
    document.getElementById('add-image-row')?.addEventListener('click', function () {
        newImgIdx--;
        var tbody = document.getElementById('images-tbody');
        var tr = document.createElement('tr');
        tr.dataset.imgId = newImgIdx;
        tr.innerHTML = '<td><input type="text" name="product_images[' + newImgIdx + '][image]" class="form-control form-control-sm" placeholder="https://..."></td>'
            + '<td><input type="text" name="product_images[' + newImgIdx + '][alt_text]" class="form-control form-control-sm"></td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-danger remove-image-row">✕</button></td>';
        tbody.appendChild(tr);
    });

    document.getElementById('images-tbody')?.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-image-row')) {
            var tr = e.target.closest('tr');
            var id = parseInt(tr.dataset.imgId);
            if (id > 0) {
                var del = document.getElementById('images-deleted');
                del.value = del.value ? del.value + ',' + id : String(id);
            }
            tr.remove();
        }
    });

    // ---- Variants ----
    function makeOptionRow(vi, oi) {
        return '<tr>'
            + '<td><input type="text" name="variants[' + vi + '][options][' + oi + '][label]" class="form-control form-control-sm"></td>'
            + '<td><input type="number" name="variants[' + vi + '][options][' + oi + '][price_modifier]" value="0" class="form-control form-control-sm" step="0.01"></td>'
            + '<td><input type="text" name="variants[' + vi + '][options][' + oi + '][sku_suffix]" class="form-control form-control-sm"></td>'
            + '<td><input type="number" name="variants[' + vi + '][options][' + oi + '][stock]" value="-1" class="form-control form-control-sm" step="1"></td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-danger remove-variant-option">✕</button></td>'
            + '</tr>';
    }

    document.getElementById('add-variant-group')?.addEventListener('click', function () {
        var vi = newVarIdx++;
        var div = document.createElement('div');
        div.className = 'variant-group card mb-2';
        div.dataset.variantIdx = vi;
        div.innerHTML = '<div class="card-body p-2">'
            + '<div class="d-flex align-items-center mb-2 gap-2">'
            + '<input type="text" name="variants[' + vi + '][name]" class="form-control form-control-sm" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_GROUP_NAME'); ?>">'
            + '<button type="button" class="btn btn-sm btn-outline-danger remove-variant-group">✕</button>'
            + '</div>'
            + '<table class="table table-sm mb-1 variant-options-table"><thead><tr>'
            + '<th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_OPTION_LABEL'); ?></th>'
            + '<th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_PRICE_MOD'); ?></th>'
            + '<th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_SKU_SUFFIX'); ?></th>'
            + '<th><?php echo Text::_('COM_SANCTUARYSHOP_VARIANT_STOCK'); ?></th>'
            + '<th></th></tr></thead><tbody></tbody></table>'
            + '<button type="button" class="btn btn-sm btn-outline-secondary add-variant-option"><?php echo Text::_('COM_SANCTUARYSHOP_ADD_VARIANT_OPTION'); ?></button>'
            + '</div>';
        document.getElementById('variants-container').appendChild(div);
    });

    document.getElementById('variants-container')?.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-variant-group')) {
            e.target.closest('.variant-group').remove();
        } else if (e.target.classList.contains('add-variant-option')) {
            var group = e.target.closest('.variant-group');
            var vi    = group.dataset.variantIdx;
            var oi    = newOptIdx++;
            var tbody = group.querySelector('.variant-options-table tbody');
            var tr = document.createElement('tr');
            tr.innerHTML = makeOptionRow(vi, oi).replace(/^<tr>|<\/tr>$/g, '');
            tbody.appendChild(tr);
        } else if (e.target.classList.contains('remove-variant-option')) {
            e.target.closest('tr').remove();
        }
    });

    // Show/hide digital files card when type changes
    var typeField = document.querySelector('[name="jform[product_type]"]');
    typeField?.addEventListener('change', function () {
        var card = document.getElementById('digital-files-card');
        if (card) card.style.display = this.value === 'digital' ? '' : 'none';
    });
})();
</script>
