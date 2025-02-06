<?php

/**
 *  v_desempenho = 
 * 
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_desempenho` AS
	SELECT
		`td`.`id` AS `id`,
		`td`.`usuario_id` AS `usu_id`,
		`td`.`disciplina_id` AS `disc_id`,
		`td`.`dia` AS `dia`,
		DATE_FORMAT(`td`.`dia`, '%d/%m') AS `dia2`,
		UNIX_TIMESTAMP(`dia`)*1000 AS `ts`,
		`td`.`qtde` AS `qtde`,
		`td`.`folha` AS `folha`,
		`td`.`estagio` AS `estagio`,
		(
			(
				(`td`.`estagio` - 1) * 200
			) + `td`.`folha`
		) AS `valor`
	FROM
		`t_desempenho` `td`;
*/

/**
 *  v_meta = 
 * 
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_meta` AS
	SELECT
		`tm`.`id`,
		`tm`.`usuario_id` AS `usu_id`,
		`tm`.`disciplina_id` AS `disc_id`,
		`tm`.`dia`,
		UNIX_TIMESTAMP(`tm`.`dia`) * 1000 AS `ts`,
		`tm`.`estagio`,
		`tm`.`folha`,
		(((`estagio` - 1) * 200) + folha) AS `valor`
	FROM
		`t_meta` `tm`;
*/

/**
 * v_series = 
 * 
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_series` AS
	SELECT
		`ts`.`id`,
		`ts`.`usuario_id` AS `usu_id`,
		`ts`.`disciplina_id` AS `disc_id`,
		`ts`.`legenda`,
		`ts`.`dia_ini`,
		UNIX_TIMESTAMP(`ts`.`dia_ini`) * 1000 `ts_ini`,
		`ts`.`folha_ini`,
		`ts`.`estagio_ini`,
		`ts`.`estagio_ini` * 200 + `ts`.`folha_ini` `valor_ini`,
		`ts`.`dia_fim`,
		UNIX_TIMESTAMP(`ts`.dia_fim) * 1000 `ts_fim`,
		`ts`.`estagio_fim`,
		`ts`.`estagio_fim` * 200 + `ts`.`folha_fim` `valor_fim`,
		`ts`.`cor`,
		`ts`.`estilo`
	FROM
		`t_series` `ts`
	WHERE
		`ts`.`mostrar` = 1
	ORDER BY
		`ts`.`id`;
*/

/**
 *  v_usuarios = 
 * 
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuarios` AS
	SELECT
		*
	FROM
		`t_usuarios`
    WHERE
        NOT `excluso`;
*/

/**
 * 	v_usu_vs_disciplina
 * 
  	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usu_vs_disciplina` AS
  	SELECT 
		`tud`.`usuario_id` `usu_id`, 
		`tud`.`disciplina_id` `disc_id`, 
		`td`.`nome` `disc_n`,
		`td`.`legenda` `disc_l`,
		`td`.`abrev` `disc_a`,
		`td`.`descricao` `disc_d`
	FROM 
		`t_usu_vs_disciplina` `tud`
	LEFT JOIN
		`t_disciplinas` `td` ON `tud`.`disciplina_id` = `td`.`id`;
 * 
 */


/**
 * 	v_devedores
 * 	--
 * 	Esta view lista os alunos que não lançaram o resultado na data corrente
 * 	
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_devedores` AS
	SELECT
	    `usuario_id`
	FROM
		`t_desempenho`
	GROUP BY
		`usuario_id`
	HAVING
		SUM(CASE WHEN DATE(`dia`) = CURDATE() THEN 1 ELSE 0 END) < COUNT(DISTINCT `disciplina_id`);
 */
?>