CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_products` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id`  INT(11) NOT NULL DEFAULT 0,
    `product_type` VARCHAR(20) NOT NULL DEFAULT 'physical',
    `title`        VARCHAR(255) NOT NULL DEFAULT '',
    `alias`        VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `description`  MEDIUMTEXT,
    `price`        DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `sale_price`   DECIMAL(10,2) DEFAULT NULL,
    `sku`          VARCHAR(100) NOT NULL DEFAULT '',
    `stock`        INT(11) NOT NULL DEFAULT 0,
    `image`        VARCHAR(1024) DEFAULT NULL,
    `state`        TINYINT(3) NOT NULL DEFAULT 0,
    `ordering`     INT(11) NOT NULL DEFAULT 0,
    `created`      DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by`   INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `modified`     DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by`  INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out`  INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` DATETIME DEFAULT NULL,
    `params`       TEXT,
    PRIMARY KEY (`id`),
    KEY `idx_state` (`state`),
    KEY `idx_category` (`category_id`),
    KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_orders` (
    `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`          INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `guest_token`      CHAR(64) NOT NULL DEFAULT '',
    `status`           VARCHAR(50) NOT NULL DEFAULT 'pending',
    `subtotal`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `discount`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `coupon_code`      VARCHAR(50) DEFAULT NULL,
    `tax`              DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `shipping`         DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `total`            DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `currency`         VARCHAR(10) NOT NULL DEFAULT 'USD',
    `billing_name`     VARCHAR(255) NOT NULL DEFAULT '',
    `billing_email`    VARCHAR(255) NOT NULL DEFAULT '',
    `billing_address`  TEXT,
    `shipping_address` TEXT,
    `payment_method`   VARCHAR(50) NOT NULL DEFAULT 'square',
    `payment_id`       VARCHAR(255) DEFAULT NULL,
    `square_order_id`  VARCHAR(255) DEFAULT NULL,
    `square_refund_id` VARCHAR(255) DEFAULT NULL,
    `notes`            TEXT,
    `created`          DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified`         DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_order_items` (
    `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`    INT(11) UNSIGNED NOT NULL,
    `product_id`  INT(11) UNSIGNED NOT NULL,
    `title`       VARCHAR(255) NOT NULL DEFAULT '',
    `sku`         VARCHAR(100) NOT NULL DEFAULT '',
    `quantity`    INT(11) NOT NULL DEFAULT 1,
    `unit_price`  DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `total_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_product_files` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `label`      VARCHAR(255) NOT NULL DEFAULT '',
    `filename`   VARCHAR(500) NOT NULL DEFAULT '',
    `filesize`   BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `ordering`   INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_refunds` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `square_refund_id` VARCHAR(255) NOT NULL DEFAULT '',
    `amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `status` VARCHAR(30) NOT NULL DEFAULT 'COMPLETED',
    `created` DATETIME NOT NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `idx_square_refund` (`square_refund_id`), KEY `idx_refund_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_download_tokens` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `token`       CHAR(64) NOT NULL,
    `order_id`    INT UNSIGNED NOT NULL,
    `order_item_id` INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `file_id`     INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NOT NULL DEFAULT 0,
    `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `max_downloads` INT UNSIGNED NOT NULL DEFAULT 5,
    `expires`     DATETIME NULL DEFAULT NULL,
    `revoked`     TINYINT NOT NULL DEFAULT 0,
    `created`     DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
    `last_used`   DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_token` (`token`),
    KEY `idx_order` (`order_id`),
    KEY `idx_user`  (`user_id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_coupons` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_product_variants` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `name`       VARCHAR(100) NOT NULL DEFAULT '',
    `ordering`   INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_product_variant_options` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `variant_id`     INT UNSIGNED NOT NULL,
    `product_id`     INT UNSIGNED NOT NULL,
    `label`          VARCHAR(100) NOT NULL DEFAULT '',
    `price_modifier` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
    `sku_suffix`     VARCHAR(50) NOT NULL DEFAULT '',
    `stock`          INT NOT NULL DEFAULT -1,
    `ordering`       INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_variant`  (`variant_id`),
    KEY `idx_product`  (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_product_images` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `image`      VARCHAR(1024) NOT NULL DEFAULT '',
    `alt_text`   VARCHAR(255) NOT NULL DEFAULT '',
    `ordering`   INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
