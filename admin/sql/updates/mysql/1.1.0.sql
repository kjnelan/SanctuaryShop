-- v1.1.0: product types, downloadable files, download tokens

ALTER TABLE `#__sanctuaryshop_products`
    ADD COLUMN IF NOT EXISTS `product_type` VARCHAR(20) NOT NULL DEFAULT 'physical' AFTER `category_id`;

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
