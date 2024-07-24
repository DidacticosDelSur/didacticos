
CREATE TABLE IF NOT EXISTS `tipo_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `link` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `dto_tag` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `categoria` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `descuento` FLOAT NULL DEFAULT '0',
  `icon` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `clase_estilo` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exento_iva` tinyint(4) DEFAULT '0',
  `eliminado` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `tipo_categorias` (`id`, `tipo`, `link`, `dto_tag`, `categoria`, `descuento`, `icon`, `clase_estilo`, `exento_iva`, `eliminado`) VALUES
	(1, 'libro', 'libros', 'libros', 'Libros', 0.3, 'icon-books', '', 1, 0),
	(2, 'juego', 'didacticos', 'didacticos', 'Didácticos', 0, 'icon-didacticos', 'blue', 0, 0),
	(3, 'juguete', 'juguetes', 'juguetes', 'Juguetes', 0, 'icon-toys', 'green', 0, 0),
	(4, 'escolar', 'escolares', 'escolares', 'Escolar', 0, 'icon-school-bag', 'purple', 0, 0)

ALTER TABLE `carritos`
	CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`id`);

ALTER TABLE `pedidos`
	CHANGE COLUMN `client_discount_books` `client_discount_libros` FLOAT NOT NULL DEFAULT '0' AFTER `ciudad_id`,
	CHANGE COLUMN `client_discount_games` `client_discount_didacticos` FLOAT NOT NULL DEFAULT '0' AFTER `client_discount_libros`,
	CHANGE COLUMN `client_discount_toys` `client_discount_juguetes` FLOAT NOT NULL DEFAULT '0' AFTER `client_discount_didacticos`,
	CHANGE COLUMN `client_discount_schools` `client_discount_escolares` FLOAT NOT NULL DEFAULT '0' AFTER `client_discount_juguetes`;

ALTER TABLE `categorias`
	CHANGE COLUMN `tipo` `tipo` VARCHAR(50) NOT NULL DEFAULT 'libro' COLLATE 'utf8_general_ci' AFTER `descripcion`;

	ALTER TABLE `productos`
	CHANGE COLUMN `tipo` `tipo` VARCHAR(50) NOT NULL COLLATE 'utf8_general_ci' AFTER `descripcion`;

ALTER TABLE `vendedores`
	ADD COLUMN `last_login` DATETIME NULL DEFAULT NULL AFTER `borrado`;

CREATE TABLE `carousel_items` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`imagen_desktop` VARCHAR(100) NOT NULL DEFAULT '\'\'' COLLATE 'utf8_unicode_ci',
	`imagen_mobile` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`url` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`eliminado` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=MyISAM

INSERT INTO `opciones` (`option_name`, `option_value`, `type`, `title`) VALUES ('tematica', '0', 'select', 'Tema de estilos en la web');

CREATE TABLE `tematica_estilos` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
	`nombre_clase` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=MyISAM

INSERT INTO `tematica_estilos` (`id`, `nombre`, `nombre_clase`) VALUES
	(1, 'D', 'brand-theme'),
	(2, 'Navidad', 'christmas'),
	(3, 'Reyes Magos', 'three-wise-men'),
	(4, 'Jueguetes', 'toys');

CREATE TABLE `rango_edades` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`rango` VARCHAR(150) NOT NULL COLLATE 'utf8_unicode_ci',
	`eliminado` TINYINT(4) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=MyISAM

/****/

ALTER TABLE `productos`
	CHANGE COLUMN `rango_edad` `rango_edad_text` VARCHAR(32) NULL DEFAULT NULL AFTER `estado`;

ALTER TABLE `productos`
	ADD COLUMN `rango_edad` INT(10) NULL DEFAULT NULL AFTER `rango_edad_text`;