-- v1.9.2: product weight and shipping method support
ALTER TABLE `#__sanctuaryshop_products` ADD COLUMN `weight` DECIMAL(10,3) NOT NULL DEFAULT '0.000' AFTER `stock`;
