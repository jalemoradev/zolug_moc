CREATE TABLE `t_acuerdos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `cliente` varchar(30) DEFAULT NULL,
  `nombre` varchar(60) DEFAULT NULL,
  `facuerdo` date DEFAULT NULL,
  `fregistro` date DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `valor` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14839 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
