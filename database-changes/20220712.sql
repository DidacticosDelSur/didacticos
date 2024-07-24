ALTER TABLE `opciones` ADD `type` VARCHAR(16) NOT NULL AFTER `option_value`;

UPDATE opciones SET type = 'number' WHERE option_id = 1;
UPDATE opciones SET type = 'checkbox' WHERE option_id = 2;
UPDATE opciones SET type = 'checkbox' WHERE option_id = 3;
UPDATE opciones SET type = 'checkbox' WHERE option_id = 4;
UPDATE opciones SET type = 'text' WHERE option_id = 5;

ALTER TABLE `opciones` ADD `title` VARCHAR(64) NOT NULL AFTER `type`;

UPDATE opciones SET title = 'Monto mínimo para alerta de compra' WHERE option_id = 1;
UPDATE opciones SET title = 'Mostrar precios cuando el cliente no está logueado' WHERE option_id = 2;
UPDATE opciones SET title = 'Mostrar precios de libros cuando el cliente no está logueado' WHERE option_id = 3;
UPDATE opciones SET title = 'Mostrar barra superior de anuncios' WHERE option_id = 4;
UPDATE opciones SET title = 'Contenido de la barra superior de anuncios' WHERE option_id = 5;