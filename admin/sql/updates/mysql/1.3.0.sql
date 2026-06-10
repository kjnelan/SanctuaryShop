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
