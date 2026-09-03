-- v1.6.0: partial refund ledger
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
