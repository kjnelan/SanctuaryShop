-- v1.5.0: secure guest order access
ALTER TABLE `#__sanctuaryshop_orders`
    ADD COLUMN `guest_token` CHAR(64) NOT NULL DEFAULT '' AFTER `user_id`;
