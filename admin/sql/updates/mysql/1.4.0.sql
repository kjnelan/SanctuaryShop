-- v1.4.0: Square refund tracking and webhook event deduplication
ALTER TABLE `#__sanctuaryshop_orders`
    ADD COLUMN IF NOT EXISTS `square_refund_id` VARCHAR(255) NULL DEFAULT NULL AFTER `square_order_id`;

CREATE TABLE IF NOT EXISTS `#__sanctuaryshop_webhook_events` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id`   VARCHAR(255) NOT NULL,
    `event_type` VARCHAR(100) NOT NULL DEFAULT '',
    `payload`    MEDIUMTEXT NULL,
    `received`   DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
