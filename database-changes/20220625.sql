ALTER TABLE `productos` ADD `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `fecha_alta`;
UPDATE `productos` SET fecha_actualizacion = fecha_alta;