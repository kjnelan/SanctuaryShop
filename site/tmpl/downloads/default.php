<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="com-sanctuaryshop-downloads">
    <h2><?php echo Text::_('COM_SANCTUARYSHOP_MY_DOWNLOADS'); ?></h2>

    <?php if (empty($this->downloads)) : ?>
        <p class="text-muted"><?php echo Text::_('COM_SANCTUARYSHOP_NO_DOWNLOADS'); ?></p>
    <?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_PRODUCT'); ?></th>
                    <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILE'); ?></th>
                    <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_FILESIZE'); ?></th>
                    <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_DOWNLOADS'); ?></th>
                    <th><?php echo Text::_('COM_SANCTUARYSHOP_FIELD_EXPIRES'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->downloads as $dl) : ?>
                <?php
                $expired   = $dl->expires && strtotime($dl->expires) < time();
                $exhausted = $dl->download_count >= $dl->max_downloads;
                $available = !$expired && !$exhausted;
                ?>
                <tr class="<?php echo !$available ? 'text-muted' : ''; ?>">
                    <td><?php echo $this->escape($dl->product_title); ?></td>
                    <td><?php echo $this->escape($dl->file_label ?: basename($dl->filename)); ?></td>
                    <td><?php echo $dl->filesize > 0 ? number_format($dl->filesize / 1048576, 1) . ' MB' : '—'; ?></td>
                    <td><?php echo (int) $dl->download_count; ?> / <?php echo (int) $dl->max_downloads; ?></td>
                    <td><?php echo $dl->expires ? HTMLHelper::_('date', $dl->expires, Text::_('DATE_FORMAT_LC2')) : Text::_('COM_SANCTUARYSHOP_NEVER_EXPIRES'); ?></td>
                    <td>
                        <?php if ($available) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&task=download.get&token=' . urlencode($dl->token)); ?>"
                               class="btn btn-sm btn-primary">
                                <?php echo Text::_('COM_SANCTUARYSHOP_DOWNLOAD'); ?>
                            </a>
                        <?php elseif ($expired) : ?>
                            <span class="badge bg-warning text-dark"><?php echo Text::_('COM_SANCTUARYSHOP_STATUS_EXPIRED'); ?></span>
                        <?php else : ?>
                            <span class="badge bg-secondary"><?php echo Text::_('COM_SANCTUARYSHOP_LIMIT_REACHED'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <p><a href="<?php echo Route::_('index.php?option=com_sanctuaryshop&view=account'); ?>" class="btn btn-outline-secondary">&larr; <?php echo Text::_('COM_SANCTUARYSHOP_BACK_TO_ORDERS'); ?></a></p>
</div>
