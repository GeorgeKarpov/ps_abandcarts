DROP TABLE IF EXISTS `PREFIX_abandcart_email`;
DROP TABLE IF EXISTS `PREFIX_abandcart_customer`;
DELETE FROM `PREFIX_translation` WHERE `domain` = 'DOMAIN_Email';
DELETE FROM `PREFIX_translation` WHERE `domain` = 'DOMAIN_Admin';
DELETE FROM `PREFIX_translation` WHERE `domain` = 'DOMAIN_Carts';