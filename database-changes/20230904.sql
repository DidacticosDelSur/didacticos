CREATE TABLE `carritos` (
    `id` int NOT NULL,
    `cliente_id` int NOT NULL,
    `vendedor_id` int,
    `detalle` text NOT NULL,
    `fecha_creacion` datetime NOT NULL,
    `fecha_ultima_actualizacion` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
