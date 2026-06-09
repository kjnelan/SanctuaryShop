<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="com-sanctuaryshop-confirmation">

    <div class="text-center py-4 mb-4">
        <div class="mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" fill="currentColor" class="text-success" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
            </svg>
        </div>
        <h1 class="text-success"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_CONFIRMED_TITLE'); ?></h1>
        <p class="lead"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_CONFIRMED_DESC'); ?></p>
        <?php if ($this->orderId) : ?>
            <p class="fs-5"><?php echo Text::_('COM_SANCTUARYSHOP_ORDER_NUMBER'); ?>: <strong>#<?php echo str_pad((int) $this->orderId, 5, '0', STR_PAD_LEFT); ?></strong></p>
        <?php endif; ?>
        <p class="text-muted"><?php echo Text::_('COM_SANCTUARYSHOP_CONFIRMATION_EMAIL_NOTE'); ?></p>
    </div>

    <?php if (!empty($this->orderDownloads)) : ?>
    <div class="card mb-4 border-info">
        <div class="card-header bg-info bg-opacity-10 fw-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            <?php echo Text::_('COM_SANCTUARYSHOP_YOUR_DOWNLOADS'); ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT'); ?></th>
                        <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE'); ?></th>
                        <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILESIZE'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->orderDownloads as $dl) : ?>
                    <tr>
                        <td><?php echo $this->escape($dl->product_title); ?></td>
                        <td><?php echo $this->escape($dl->file_label ?: basename($dl->filename)); ?></td>
                        <td><?php echo $dl->filesize > 0 ? number_format($dl->filesize / 1048576, 1) . ' MB' : '—'; ?></td>
                        <td>
                            <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=download.get&token=' . urlencode($dl->token)); ?>"
                               class="btn btn-sm btn-primary">
                                <?php echo Text::_('COM_SANCTUARYSHOP_DOWNLOAD'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            <?php echo Text::_('COM_SANCTUARYSHOP_DOWNLOADS_ALSO_IN_ACCOUNT'); ?>
            <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=downloads'); ?>"><?php echo Text::_('COM_SANCTUARYSHOP_MY_DOWNLOADS'); ?></a>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=products'); ?>" class="btn btn-primary">
            <?php echo Text::_('COM_SANCTUARYSHOP_CONTINUE_SHOPPING'); ?>
        </a>
        <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=account'); ?>" class="btn btn-outline-secondary">
            <?php echo Text::_('COM_SANCTUARYSHOP_MY_ORDERS'); ?>
        </a>
    </div>
</div>
