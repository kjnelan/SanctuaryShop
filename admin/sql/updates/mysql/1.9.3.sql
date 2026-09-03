-- v1.9.3: save selected shipping method on orders
ALTER TABLE `#__sanctuaryshop_orders` ADD COLUMN `shipping_method` VARCHAR(100) NOT NULL DEFAULT 'standard' AFTER `shipping`;
