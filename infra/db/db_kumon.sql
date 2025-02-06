--
--  Estrutura da Base de Dados
--  db_kumon
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `db_kumon`
--
CREATE DATABASE IF NOT EXISTS `db_kumon` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_kumon`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_desempenho`
--

DROP TABLE IF EXISTS `t_desempenho`;
CREATE TABLE `t_desempenho` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `disciplina_id` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `dia` date NOT NULL,
  `qtde` tinyint NOT NULL,
  `folha` tinyint UNSIGNED DEFAULT NULL,
  `estagio` tinyint UNSIGNED DEFAULT NULL,
  `qda` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_disciplinas`
--

DROP TABLE IF EXISTS `t_disciplinas`;
CREATE TABLE `t_disciplinas` (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `legenda` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `abrev` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'abreviatura',
  `descricao` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `t_disciplinas`
--

INSERT INTO `t_disciplinas` (`id`, `nome`, `legenda`, `abrev`, `descricao`) VALUES
(1, 'Português', 'P', 'Port', 'Língua Pátria'),
(2, 'Matemática', 'M', 'Mat', 'Matemática'),
(3, 'Inglês', 'I', 'Ing', 'Inglês'),
(4, 'Japonês', 'J', 'Jap', 'Japonês');

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_meta`
--

DROP TABLE IF EXISTS `t_meta`;
CREATE TABLE `t_meta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int UNSIGNED NOT NULL,
  `disciplina_id` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `dia` date NOT NULL,
  `estagio` tinyint UNSIGNED DEFAULT NULL,
  `folha` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `qda` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_series`
--

DROP TABLE IF EXISTS `t_series`;
CREATE TABLE `t_series` (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int UNSIGNED NOT NULL,
  `disciplina_id` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `legenda` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dia_ini` date NOT NULL,
  `folha_ini` tinyint UNSIGNED NOT NULL,
  `estagio_ini` tinyint UNSIGNED NOT NULL,
  `dia_fim` date NOT NULL,
  `folha_fim` tinyint UNSIGNED NOT NULL,
  `estagio_fim` tinyint UNSIGNED NOT NULL,
  `cor` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estilo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mostrar` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_usuarios`
--

DROP TABLE IF EXISTS `t_usuarios`;
CREATE TABLE `t_usuarios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '0',
  `excluso` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_usu_vs_disciplina`
--

DROP TABLE IF EXISTS `t_usu_vs_disciplina`;
CREATE TABLE `t_usu_vs_disciplina` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int UNSIGNED NOT NULL,
  `disciplina_id` tinyint UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_subscriptions`
--

CREATE TABLE `t_subscriptions` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usu_id` int UNSIGNED NOT NULL DEFAULT '0',
  `endpoint` text COLLATE utf8mb4_general_ci NOT NULL,
  `p256dh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'chave pública',
  `auth` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'chave de autenticação',
  `qc` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para view `v_desempenho`
--

DROP VIEW IF EXISTS `v_desempenho`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_desempenho`  AS SELECT `td`.`id` AS `id`, `td`.`usuario_id` AS `usu_id`, `td`.`disciplina_id` AS `disc_id`, `td`.`dia` AS `dia`, date_format(`td`.`dia`,'%d/%m') AS `dia2`, (unix_timestamp(`td`.`dia`) * 1000) AS `ts`, `td`.`qtde` AS `qtde`, `td`.`folha` AS `folha`, `td`.`estagio` AS `estagio`, (((`td`.`estagio` - 1) * 200) + `td`.`folha`) AS `valor` FROM `t_desempenho` AS `td` ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_meta`
--

DROP VIEW IF EXISTS `v_meta`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_meta`  AS SELECT `tm`.`id` AS `id`, `tm`.`usuario_id` AS `usu_id`, `tm`.`disciplina_id` AS `disc_id`, `tm`.`dia` AS `dia`, (unix_timestamp(`tm`.`dia`) * 1000) AS `ts`, `tm`.`estagio` AS `estagio`, `tm`.`folha` AS `folha`, (((`tm`.`estagio` - 1) * 200) + `tm`.`folha`) AS `valor` FROM `t_meta` AS `tm` ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_series`
--

DROP VIEW IF EXISTS `v_series`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_series`  AS SELECT `ts`.`id` AS `id`, `ts`.`usuario_id` AS `usu_id`, `ts`.`disciplina_id` AS `disc_id`, `ts`.`legenda` AS `legenda`, `ts`.`dia_ini` AS `dia_ini`, (unix_timestamp(`ts`.`dia_ini`) * 1000) AS `ts_ini`, `ts`.`folha_ini` AS `folha_ini`, `ts`.`estagio_ini` AS `estagio_ini`, ((`ts`.`estagio_ini` * 200) + `ts`.`folha_ini`) AS `valor_ini`, `ts`.`dia_fim` AS `dia_fim`, (unix_timestamp(`ts`.`dia_fim`) * 1000) AS `ts_fim`, `ts`.`estagio_fim` AS `estagio_fim`, ((`ts`.`estagio_fim` * 200) + `ts`.`folha_fim`) AS `valor_fim`, `ts`.`cor` AS `cor`, `ts`.`estilo` AS `estilo` FROM `t_series` AS `ts` WHERE (`ts`.`mostrar` = 1) ORDER BY `ts`.`id` ASC ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_usuarios`
--

DROP VIEW IF EXISTS `v_usuarios`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuarios`  AS SELECT `t_usuarios`.`id` AS `id`, `t_usuarios`.`login` AS `login`, `t_usuarios`.`nome` AS `nome`, `t_usuarios`.`email` AS `email`, `t_usuarios`.`senha` AS `senha`, `t_usuarios`.`ativo` AS `ativo`, `t_usuarios`.`excluso` AS `excluso` FROM `t_usuarios` WHERE (0 = `t_usuarios`.`excluso`) ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_usu_vs_disciplina`
--

DROP VIEW IF EXISTS `v_usu_vs_disciplina`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usu_vs_disciplina`  AS SELECT `tud`.`usuario_id` AS `usu_id`, `tud`.`disciplina_id` AS `disc_id`, `td`.`nome` AS `disc_n`, `td`.`legenda` AS `disc_l`, `td`.`abrev` AS `disc_a`, `td`.`descricao` AS `disc_d` FROM (`t_usu_vs_disciplina` `tud` left join `t_disciplinas` `td` on((`tud`.`disciplina_id` = `td`.`id`))) ;


DROP VIEW IF EXISTS `v_usu_vs_disciplina`;
CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_devedores` AS SELECT `usuario_id` FROM `t_desempenho` GROUP BY `usuario_id` HAVING SUM(CASE WHEN DATE(`dia`) = CURDATE() THEN 1 ELSE 0 END) < COUNT(DISTINCT `disciplina_id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
