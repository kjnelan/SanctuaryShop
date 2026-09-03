-- v1.8.0: Square subscription plan and subscription reference
ALTER TABLE `#__sanctuaryshop_products` ADD COLUMN `subscription_plan_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `product_type`;
ALTER TABLE `#__sanctuaryshop_orders` ADD COLUMN `square_subscription_id` VARCHAR(255) NULL DEFAULT NULL AFTER `square_refund_id`;
