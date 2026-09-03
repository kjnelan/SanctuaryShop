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
            if (str_contains($filename, '..') || str_starts_with($filename, '/') || str_starts_with($filename, '\\')) {
                throw new \RuntimeException('Download filenames must stay inside the configured download folder.');
            }
            $label    = trim($data['label'] ?? '');
            $fileId   = (int) $id;

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

    public function getProductVariants(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_product_variants'))
            ->where($db->quoteName('product_id') . ' = ' . $productId)
            ->order('ordering ASC, id ASC');
        $variants = $db->setQuery($query)->loadObjectList() ?: [];

        foreach ($variants as $variant) {
            $oq = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__sanctuaryshop_product_variant_options'))
                ->where($db->quoteName('variant_id') . ' = ' . (int) $variant->id)
                ->order('ordering ASC, id ASC');
            $variant->options = $db->setQuery($oq)->loadObjectList() ?: [];
        }

        return $variants;
    }

    public function saveProductVariants(int $productId, array $variants): void
    {
        $db = $this->getDatabase();

        // Delete all existing variants and options for this product
        $variantIds = $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName('#__sanctuaryshop_product_variants'))
                ->where($db->quoteName('product_id') . ' = ' . $productId)
        )->loadColumn() ?: [];

        if (!empty($variantIds)) {
            $db->setQuery(
                $db->getQuery(true)
                    ->delete($db->quoteName('#__sanctuaryshop_product_variant_options'))
                    ->whereIn($db->quoteName('variant_id'), array_map('intval', $variantIds))
            )->execute();
        }

        $db->setQuery(
            $db->getQuery(true)
                ->delete($db->quoteName('#__sanctuaryshop_product_variants'))
                ->where($db->quoteName('product_id') . ' = ' . $productId)
        )->execute();

        // Re-insert
        $vOrdering = 1;
        foreach ($variants as $vData) {
            $name = trim($vData['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $vRow = (object) [
                'product_id' => $productId,
                'name'       => $name,
                'ordering'   => $vOrdering,
            ];
            $db->insertObject('#__sanctuaryshop_product_variants', $vRow);
            $variantId = (int) $db->insertid();

            $oOrdering = 1;
            foreach ($vData['options'] ?? [] as $oData) {
                $label = trim($oData['label'] ?? '');
                if ($label === '') {
                    continue;
                }
                $oRow = (object) [
                    'variant_id'     => $variantId,
                    'product_id'     => $productId,
                    'label'          => $label,
                    'price_modifier' => (float) ($oData['price_modifier'] ?? 0),
                    'sku_suffix'     => trim($oData['sku_suffix'] ?? ''),
                    'stock'          => (int) ($oData['stock'] ?? -1),
                    'ordering'       => $oOrdering,
                ];
                $db->insertObject('#__sanctuaryshop_product_variant_options', $oRow);
                $oOrdering++;
            }
            $vOrdering++;
        }
    }

    public function getProductImages(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sanctuaryshop_product_images'))
            ->where($db->quoteName('product_id') . ' = ' . $productId)
            ->order('ordering ASC, id ASC');
        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function saveProductImages(int $productId, array $images, array $deleted): void
    {
        $db = $this->getDatabase();

        if (!empty($deleted)) {
            $deleted = array_map('intval', $deleted);
            $db->setQuery(
                $db->getQuery(true)
                    ->delete($db->quoteName('#__sanctuaryshop_product_images'))
                    ->whereIn($db->quoteName('id'), $deleted)
                    ->where($db->quoteName('product_id') . ' = ' . $productId)
            )->execute();
        }

        $ordering = 1;
        foreach ($images as $id => $data) {
            $image = trim($data['image'] ?? '');
            if ($image === '') {
                continue;
            }
            if (!preg_match('#^(https?://|images/)#i', $image) || preg_match('/[\r\n]|javascript:/i', $image)) {
                throw new \RuntimeException('Product images must be safe HTTPS/HTTP or Joomla images paths.');
            }
            $altText = trim($data['alt_text'] ?? '');
            $imgId   = (int) $id;

            if ($imgId > 0) {
                $db->setQuery(
                    $db->getQuery(true)
                        ->update($db->quoteName('#__sanctuaryshop_product_images'))
                        ->set($db->quoteName('image') . ' = ' . $db->quote($image))
                        ->set($db->quoteName('alt_text') . ' = ' . $db->quote($altText))
                        ->set($db->quoteName('ordering') . ' = ' . $ordering)
                        ->where($db->quoteName('id') . ' = ' . $imgId)
                        ->where($db->quoteName('product_id') . ' = ' . $productId)
                )->execute();
            } else {
                $row = (object) [
                    'product_id' => $productId,
                    'image'      => $image,
                    'alt_text'   => $altText,
                    'ordering'   => $ordering,
                ];
                $db->insertObject('#__sanctuaryshop_product_images', $row);
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

            // Save files
            $files   = $app->getInput()->get('files', [], 'array');
            $deleted = array_filter(explode(',', $app->getInput()->getString('files_deleted', '')));
            if (!empty($files) || !empty($deleted)) {
                $this->saveProductFiles((int) $productId, $files, $deleted);
            }

            // Save variants
            $variants = $app->getInput()->get('variants', [], 'array');
            $this->saveProductVariants((int) $productId, $variants);

            // Save additional images
            $images        = $app->getInput()->get('product_images', [], 'array');
            $imagesDeleted = array_filter(explode(',', $app->getInput()->getString('images_deleted', '')));
            if (!empty($images) || !empty($imagesDeleted)) {
                $this->saveProductImages((int) $productId, $images, $imagesDeleted);
            }
        }

        return $result;
    }
}
