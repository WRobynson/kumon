<?php
include("../session.php");						//	dados da sessão
include("../header.php");						//	definiçÕes de cabeçalho PHP
include("../constantes.php");					//	variáveis de ambiente e informações sobre o servidor
include("../config/functions.php");
include("../config/db_functions.php");

$disc_id = filter_input(INPUT_POST, 'disc_id');

//	OPTIONS do campo ESTÁGIO

$op_est =  null;

$sql = "SELECT `pos`, `cod` FROM `t_estagios` WHERE `disciplina_id` = {$disc_id} ORDER BY `pos`";
$result = getSelect($sql);

if (!empty($result)) {
	foreach ($result as $linha) {
		$pos = $linha["pos"];
		$cod = $linha["cod"];

		$op_est .= "<option value='{$pos}'>{$cod}</option>";
	}
}

echo $op_est;

?>