CREATE TABLE `r_acuerdo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(40) DEFAULT NULL,
  `ope` varchar(40) DEFAULT NULL,
  `acu` varchar(10) DEFAULT NULL,
  `valor` varchar(20) DEFAULT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
