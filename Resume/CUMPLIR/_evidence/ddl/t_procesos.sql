CREATE TABLE `t_procesos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `estado` varchar(60) DEFAULT NULL,
  `sub` varchar(60) DEFAULT NULL,
  `fgestion` date DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=109867 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
