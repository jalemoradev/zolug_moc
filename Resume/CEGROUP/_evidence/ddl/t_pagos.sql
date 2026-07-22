CREATE TABLE `t_pagos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `pago` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
