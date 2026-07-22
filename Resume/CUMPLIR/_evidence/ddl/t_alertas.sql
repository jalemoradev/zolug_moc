CREATE TABLE `t_alertas` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` varchar(20) NOT NULL,
  `num` varchar(5) NOT NULL,
  `alerta` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43120 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
