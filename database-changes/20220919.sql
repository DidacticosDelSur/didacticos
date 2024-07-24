ALTER TABLE `clientes` ADD `last_ip` VARCHAR(45) NULL AFTER `last_login`;

ALTER TABLE `clientes` CHANGE `descuento_escolares` `descuento_escolares` INT(11) NULL DEFAULT '0';