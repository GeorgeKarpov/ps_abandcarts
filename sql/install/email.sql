CREATE TABLE IF NOT EXISTS `PREFIX_abandcart_email` (
  `id_cart` INT(10),
  `date_sent` DATETIME,
  `status` INT(1),
  `id_customer` INT,
  `id_cart_rule` INT,
  PRIMARY KEY (`id_cart`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;