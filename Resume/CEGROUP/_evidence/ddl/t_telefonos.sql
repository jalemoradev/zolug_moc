CREATE TABLE `t_telefonos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `detalle` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=119437 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
