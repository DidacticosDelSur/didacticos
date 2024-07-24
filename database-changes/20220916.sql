ALTER TABLE `categorias` CHANGE `tipo` `tipo` ENUM('libro', 'juego', 'juguete', 'escolar') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'libro';

ALTER TABLE `productos` CHANGE `tipo` `tipo` ENUM('libro','juego','juguete', 'escolar') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `clientes` ADD `descuento_escolares` INT NULL AFTER `descuento_juguetes`;

ALTER TABLE `pedidos` ADD `client_discount_schools` FLOAT NOT NULL AFTER `client_discount_toys`;