<?php
defined('_JEXEC') or die;

// Old-style installer script — no interface, plain class, works on all Joomla 4/5/6 installs.
class Com_SanctuaryshopInstallerScript
{
    public function preflight($type, $parent)
    {
        return true;
    }

    public function install($parent)
    {
        $this->createTables();
        return true;
    }

    public function update($parent)
    {
        $this->createTables();
        return true;
    }

    public function uninstall($parent)
    {
        return true;
    }

    public function postflight($type, $parent)
    {
        $this->createTables();
        return true;
    }

    private function createTables()
    {
        $db     = \Joomla\CMS\Factory::getContainer()->get('db');
        $prefix = $db->getPrefix();

        $tables = [
            "{$prefix}sanctuaryshop_products" => "
                CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_products` (
                    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `category_id`      INT NOT NULL DEFAULT 0,
                    `product_type`     VARCHAR(20) NOT NULL DEFAULT 'physical',
                    `title`            VARCHAR(255) NOT NULL DEFAULT '',
                    `alias`            VARCHAR(400) NOT NULL DEFAULT '',
                    `description`      MEDIUMTEXT NULL,
                    `price`            DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `sale_price`       DECIMAL(10,2) NULL DEFAULT NULL,
                    `sku`              VARCHAR(100) NOT NULL DEFAULT '',
                    `stock`            INT NOT NULL DEFAULT 0,
                    `image`            VARCHAR(1024) NULL DEFAULT NULL,
                    `state`            TINYINT NOT NULL DEFAULT 0,
                    `ordering`         INT NOT NULL DEFAULT 0,
                    `created`          DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `created_by`       INT UNSIGNED NOT NULL DEFAULT 0,
                    `modified`         DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `modified_by`      INT UNSIGNED NOT NULL DEFAULT 0,
                    `checked_out`      INT UNSIGNED NOT NULL DEFAULT 0,
                    `checked_out_time` DATETIME NULL DEFAULT NULL,
                    `params`           TEXT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_state`    (`state`),
                    KEY `idx_category` (`category_id`),
                    KEY `idx_alias`    (`alias`(191))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",

            "{$prefix}sanctuaryshop_orders" => "
                CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_orders` (
                    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id`          INT UNSIGNED NOT NULL DEFAULT 0,
                    `status`           VARCHAR(50) NOT NULL DEFAULT 'pending',
                    `subtotal`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `discount`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `coupon_code`      VARCHAR(50) NULL DEFAULT NULL,
                    `tax`              DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `shipping`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `total`            DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `currency`         VARCHAR(10) NOT NULL DEFAULT 'USD',
                    `billing_name`     VARCHAR(255) NOT NULL DEFAULT '',
                    `billing_email`    VARCHAR(255) NOT NULL DEFAULT '',
                    `billing_address`  TEXT NULL,
                    `shipping_address` TEXT NULL,
                    `payment_method`   VARCHAR(50) NOT NULL DEFAULT 'square',
                    `payment_id`       VARCHAR(255) NULL DEFAULT NULL,
                    `square_order_id`  VARCHAR(255) NULL DEFAULT NULL,
                    `notes`            TEXT NULL,
                    `created`          DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `modified`         DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`id`),
                    KEY `idx_user`   (`user_id`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",

            "{$prefix}sanctuaryshop_order_items" => "
                CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_order_items` (
                    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `order_id`    INT UNSIGNED NOT NULL,
                    `product_id`  INT UNSIGNED NOT NULL,
                    `title`       VARCHAR(255) NOT NULL DEFAULT '',
                    `sku`         VARCHAR(100) NOT NULL DEFAULT '',
                    `quantity`    INT NOT NULL DEFAULT 1,
                    `unit_price`  DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `total_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    PRIMARY KEY (`id`),
                    KEY `idx_order`   (`order_id`),
                    KEY `idx_product` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",

            "{$prefix}sanctuaryshop_product_files" => "
                CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_product_files` (
                    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `product_id` INT UNSIGNED NOT NULL,
                    `label`      VARCHAR(255) NOT NULL DEFAULT '',
                    `filename`   VARCHAR(500) NOT NULL DEFAULT '',
                    `filesize`   BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    `ordering`   INT NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY `idx_product` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",

            "{$prefix}sanctuaryshop_download_tokens" => "
                CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_download_tokens` (
                    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `token`          CHAR(64) NOT NULL,
                    `order_id`       INT UNSIGNED NOT NULL,
                    `order_item_id`  INT UNSIGNED NOT NULL,
                    `product_id`     INT UNSIGNED NOT NULL,
                    `file_id`        INT UNSIGNED NOT NULL,
                    `user_id`        INT UNSIGNED NOT NULL DEFAULT 0,
                    `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
                    `max_downloads`  INT UNSIGNED NOT NULL DEFAULT 5,
                    `expires`        DATETIME NULL DEFAULT NULL,
                    `revoked`        TINYINT NOT NULL DEFAULT 0,
                    `created`        DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `last_used`      DATETIME NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_token`   (`token`),
                    KEY `idx_order`   (`order_id`),
                    KEY `idx_user`    (`user_id`),
                    KEY `idx_product` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",

            "{$prefix}sanctuaryshop_coupons" => "
                CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_coupons` (
                    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `code`          VARCHAR(50) NOT NULL,
                    `type`          VARCHAR(20) NOT NULL DEFAULT 'percentage',
                    `value`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `min_subtotal`  DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                    `usage_limit`   INT UNSIGNED NOT NULL DEFAULT 0,
                    `used_count`    INT UNSIGNED NOT NULL DEFAULT 0,
                    `expires`       DATETIME NULL DEFAULT NULL,
                    `published`     TINYINT NOT NULL DEFAULT 1,
                    `created`       DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_code` (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
        ];

        foreach ($tables as $name => $sql) {
            try {
                $db->setQuery($sql)->execute();
            } catch (\Exception $e) {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage(
                    'SanctuaryShop installer: ' . $e->getMessage(), 'warning'
                );
            }
        }

        // v1.1 — add product_type column if it doesn't exist
        try {
            $cols = $db->setQuery("SHOW COLUMNS FROM `{$prefix}sanctuaryshop_products` LIKE 'product_type'")->loadResult();
            if (!$cols) {
                $db->setQuery("ALTER TABLE `{$prefix}sanctuaryshop_products` ADD COLUMN `product_type` VARCHAR(20) NOT NULL DEFAULT 'physical' AFTER `category_id`")->execute();
            }
        } catch (\Exception $e) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(
                'SanctuaryShop installer (product_type): ' . $e->getMessage(), 'warning'
            );
        }

        // v1.2 — add discount/coupon_code columns if they don't exist
        try {
            $cols = $db->setQuery("SHOW COLUMNS FROM `{$prefix}sanctuaryshop_orders` LIKE 'discount'")->loadResult();
            if (!$cols) {
                $db->setQuery("ALTER TABLE `{$prefix}sanctuaryshop_orders` ADD COLUMN `discount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `subtotal`, ADD COLUMN `coupon_code` VARCHAR(50) DEFAULT NULL AFTER `discount`")->execute();
            }
        } catch (\Exception $e) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage(
                'SanctuaryShop installer (discount): ' . $e->getMessage(), 'warning'
            );
        }
    }
}
