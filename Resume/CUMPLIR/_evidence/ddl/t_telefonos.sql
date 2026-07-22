CREATE TABLE `t_telefonos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `detalle` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133950 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
