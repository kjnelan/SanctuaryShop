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
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
(function () {
    var newIdx = 0;

    document.getElementById('add-file-row')?.addEventListener('click', function () {
        newIdx--;
        var tbody = document.getElementById('files-tbody');
        var tr = document.createElement('tr');
        tr.dataset.fileId = newIdx;
        tr.innerHTML = '<td><input type="text" name="files[' + newIdx + '][label]" class="form-control form-control-sm" placeholder="<?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE_LABEL'); ?>"></td>'
            + '<td><input type="text" name="files[' + newIdx + '][filename]" class="form-control form-control-sm" placeholder="relative/path/to/file.zip"></td>'
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

    // Show/hide digital files card when type changes
    var typeField = document.querySelector('[name="jform[product_type]"]');
    typeField?.addEventListener('change', function () {
        var card = document.getElementById('digital-files-card');
        if (card) card.style.display = this.value === 'digital' ? '' : 'none';
    });
})();
</script>
