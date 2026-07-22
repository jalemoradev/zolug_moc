CREATE TABLE `t_usuarios` (
  `cedula` bigint(20) NOT NULL,
  `nombre` varchar(40) DEFAULT NULL,
  `telefono` bigint(20) DEFAULT NULL,
  `avatar` varchar(6) DEFAULT NULL,
  `userpass` varchar(100) DEFAULT NULL,
  `username` varchar(30) NOT NULL,
  `usertype` int(1) DEFAULT NULL,
  `posicion` varchar(10) NOT NULL DEFAULT '0',
  `estado` varchar(10) NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
