CREATE TABLE `t_gestiones` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `nombre` varchar(40) NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora` varchar(20) DEFAULT NULL,
  `gestion` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=801070 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
