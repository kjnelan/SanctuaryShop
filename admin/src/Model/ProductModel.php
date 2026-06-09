<?php
namespace SanctuaryShop\Component\Sanctuaryshop\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class ProductModel extends AdminModel
{
    public $typeAlias = 'com_sanctuaryshop.product';

    protected function canDelete($record): bool
    {
        if (!empty($record->id)) {
            return Factory::getApplication()->getIdentity()->authorise('core.delete', 'com_sanctuaryshop.product.' . (int) $record->id);
        }
        return false;
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_sanctuaryshop.product', 'product', ['control' => 'jform', 'load_data' => $loadData]);
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_sanctuaryshop.edit.product.data', []);
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    public function getTable($name = 'Product', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function getProductFiles(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_product_files'))
            ->where($db->quoteName('product_id') . ' = ' . $productId)
            ->order('ordering ASC, id ASC');
        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function saveProductFiles(int $productId, array $files, array $deleted): void
    {
        $db  = $this->getDatabase();
        $now = Factory::getDate()->toSql();

        // Delete removed files
        if (!empty($deleted)) {
            $deleted = array_map('intval', $deleted);
            $query   = $db->getQuery(true)
                ->delete($db->quoteName('#__sanctuaryshop_product_files'))
                ->whereIn($db->quoteName('id'), $deleted)
                ->where($db->quoteName('product_id') . ' = ' . $productId);
            $db->setQuery($query)->execute();
        }

        $ordering = 1;
        foreach ($files as $id => $data) {
            $filename = trim($data['filename'] ?? '');
            if ($filename === '') {
                continue;
            }
            $label    = trim($data['label'] ?? '');
            $fileId   = (int) $id;
            $fullPath = '';

            // Try to resolve filesize
            $filesize = 0;
            try {
                $params   = \Joomla\CMS\Component\ComponentHelper::getParams('com_sanctuaryshop');
                $basePath = rtrim($params->get('download_path', ''), '/\\');
                if ($basePath) {
                    $fullPath = $basePath . DIRECTORY_SEPARATOR . ltrim($filename, '/\\');
                    if (is_file($fullPath)) {
                        $filesize = filesize($fullPath);
                    }
                }
            } catch (\Exception $e) {}

            if ($fileId > 0) {
                // Update existing row
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__sanctuaryshop_product_files'))
                    ->set($db->quoteName('label') . ' = ' . $db->quote($label))
                    ->set($db->quoteName('filename') . ' = ' . $db->quote($filename))
                    ->set($db->quoteName('filesize') . ' = ' . (int) $filesize)
                    ->set($db->quoteName('ordering') . ' = ' . $ordering)
                    ->where($db->quoteName('id') . ' = ' . $fileId)
                    ->where($db->quoteName('product_id') . ' = ' . $productId);
                $db->setQuery($query)->execute();
            } else {
                // Insert new row
                $row = (object) [
                    'product_id' => $productId,
                    'label'      => $label,
                    'filename'   => $filename,
                    'filesize'   => (int) $filesize,
                    'ordering'   => $ordering,
                ];
                $db->insertObject('#__sanctuaryshop_product_files', $row);
            }
            $ordering++;
        }
    }

    public function save($data)
    {
        $app = Factory::getApplication();

        $result = parent::save($data);

        if ($result) {
            $productId = $this->getState($this->getName() . '.id');
            $files     = $app->getInput()->get('files', [], 'array');
            $deleted   = array_filter(explode(',', $app->getInput()->getString('files_deleted', '')));
            if (!empty($files) || !empty($deleted)) {
                $this->saveProductFiles((int) $productId, $files, $deleted);
            }
        }

        return $result;
    }
}
