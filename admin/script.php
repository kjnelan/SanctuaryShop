<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class Com_SanctuaryshopInstallerScript implements InstallerScriptInterface
{
    private DatabaseInterface $db;

    public function __construct()
    {
        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
    }

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        $this->createTables();
        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    private function createTables(): void
    {
        $db     = $this->db;
        $prefix = $db->getPrefix();

        // Products
        $db->setQuery("
            CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_products` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `category_id`       INT NOT NULL DEFAULT 0,
                `title`             VARCHAR(255) NOT NULL DEFAULT '',
                `alias`             VARCHAR(400) NOT NULL DEFAULT '',
                `description`       MEDIUMTEXT NULL,
                `price`             DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                `sale_price`        DECIMAL(10,2) NULL DEFAULT NULL,
                `sku`               VARCHAR(100) NOT NULL DEFAULT '',
                `stock`             INT NOT NULL DEFAULT 0,
                `image`             VARCHAR(1024) NULL DEFAULT NULL,
                `state`             TINYINT NOT NULL DEFAULT 0,
                `ordering`          INT NOT NULL DEFAULT 0,
                `created`           DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                `created_by`        INT UNSIGNED NOT NULL DEFAULT 0,
                `modified`          DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                `modified_by`       INT UNSIGNED NOT NULL DEFAULT 0,
                `checked_out`       INT UNSIGNED NOT NULL DEFAULT 0,
                `checked_out_time`  DATETIME NULL DEFAULT NULL,
                `params`            TEXT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_state`    (`state`),
                KEY `idx_category` (`category_id`),
                KEY `idx_alias`    (`alias`(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ")->execute();

        // Orders
        $db->setQuery("
            CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_orders` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id`           INT UNSIGNED NOT NULL DEFAULT 0,
                `status`            VARCHAR(50) NOT NULL DEFAULT 'pending',
                `subtotal`          DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                `tax`               DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                `shipping`          DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                `total`             DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                `currency`          VARCHAR(10) NOT NULL DEFAULT 'USD',
                `billing_name`      VARCHAR(255) NOT NULL DEFAULT '',
                `billing_email`     VARCHAR(255) NOT NULL DEFAULT '',
                `billing_address`   TEXT NULL,
                `shipping_address`  TEXT NULL,
                `payment_method`    VARCHAR(50) NOT NULL DEFAULT 'square',
                `payment_id`        VARCHAR(255) NULL DEFAULT NULL,
                `square_order_id`   VARCHAR(255) NULL DEFAULT NULL,
                `notes`             TEXT NULL,
                `created`           DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                `modified`          DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
                PRIMARY KEY (`id`),
                KEY `idx_user`   (`user_id`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ")->execute();

        // Order items
        $db->setQuery("
            CREATE TABLE IF NOT EXISTS `{$prefix}sanctuaryshop_order_items` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id`     INT UNSIGNED NOT NULL,
                `product_id`   INT UNSIGNED NOT NULL,
                `title`        VARCHAR(255) NOT NULL DEFAULT '',
                `sku`          VARCHAR(100) NOT NULL DEFAULT '',
                `quantity`     INT NOT NULL DEFAULT 1,
                `unit_price`   DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                `total_price`  DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (`id`),
                KEY `idx_order`   (`order_id`),
                KEY `idx_product` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ")->execute();
    }
}
