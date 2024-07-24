ALTER TABLE `clientes` CHANGE `descuento` `descuento_libros` FLOAT NULL DEFAULT NULL;
ALTER TABLE `clientes` ADD `descuento_didacticos` INT NOT NULL AFTER `descuento_libros`, ADD `descuento_juguetes` INT NOT NULL AFTER `descuento_didacticos`;
ALTER TABLE `pedidos` CHANGE `client_discount` `client_discount_books` FLOAT NULL DEFAULT NULL;
ALTER TABLE `pedidos` ADD `client_discount_games` FLOAT NOT NULL AFTER `client_discount_books`, ADD `client_discount_toys` FLOAT NOT NULL AFTER `client_discount_games`;