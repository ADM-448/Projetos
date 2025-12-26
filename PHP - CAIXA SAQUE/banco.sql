CREATE TABLE `caixa_notas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nota` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nota_unica` (`nota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `caixa_notas` (`nota`, `quantidade`) VALUES
(100, 10),
(50, 20),
(20, 50),
(10, 100),
(5, 200)
ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade);