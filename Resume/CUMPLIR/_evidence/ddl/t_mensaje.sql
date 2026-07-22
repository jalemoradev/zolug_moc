CREATE TABLE `t_mensaje` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(30) DEFAULT NULL,
  `mensaje` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
