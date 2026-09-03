-- v1.9.0: subscription lifecycle status
ALTER TABLE `#__sanctuaryshop_orders` ADD COLUMN `square_subscription_status` VARCHAR(30) NULL DEFAULT NULL AFTER `square_subscription_id`;
