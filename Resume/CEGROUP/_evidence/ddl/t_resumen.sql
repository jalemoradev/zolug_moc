CREATE TABLE `t_resumen` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `fingreso` date DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(30) DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `canal` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contacto` varchar(5) DEFAULT NULL,
  `acuerdo` varchar(5) DEFAULT NULL,
  `ncuotas` varchar(5) DEFAULT NULL,
  `vcredito` varchar(20) DEFAULT NULL,
  `vnegociado` varchar(20) DEFAULT NULL,
  `condonado` varchar(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `fregistro` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=595448 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
