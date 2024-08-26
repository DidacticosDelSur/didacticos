ALTER TABLE `busquedas_clientes` ADD `vendedor_id` INT NULL DEFAULT NULL AFTER `cliente_id`;
ALTER TABLE `busquedas_clientes` ADD `id` INT NOT NULL FIRST;
ALTER TABLE `busquedas_clientes` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);


ALTER TABLE `productos` ADD `codigo_barras` VARCHAR(50) CHARACTER SET utf16 COLLATE utf16_spanish_ci NULL DEFAULT NULL AFTER `sku`;