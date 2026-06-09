-- v1.2.0: coupons, discount/coupon_code columns on orders
-- Note: discount + coupon_code columns are added via script.php (uses SHOW COLUMNS guard for compatibility)

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
