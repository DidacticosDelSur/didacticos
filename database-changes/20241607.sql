CREATE TABLE `busquedas_clientes` (
	`cliente_id` INT(11) NOT NULL,
	`busqueda` VARCHAR(100) NOT NULL,
	`resultado` INT(11) NOT NULL,
	`fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)
ENGINE=MyISAM
;

ALTER TABLE `busquedas_clientes`
	ADD COLUMN `link` VARCHAR(50) NOT NULL AFTER `resultado`;

ALTER TABLE `busquedas_clientes` ADD `es_vendedor` TINYINT DEFAULT 0 AFTER `link`;