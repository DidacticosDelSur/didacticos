ALTER TABLE `busquedas_clientes` ADD `vendedor_id` INT NULL DEFAULT NULL AFTER `cliente_id`;
ALTER TABLE `busquedas_clientes` ADD `id` INT NOT NULL FIRST;
ALTER TABLE `busquedas_clientes` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);