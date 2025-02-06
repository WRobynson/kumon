<?php

$raiz = "../";

include($raiz."session.php");						//	dados da sessão
include($raiz."header.php");						//	definiçÕes de cabeçalho PHP
include($raiz."constantes.php");					//	variáveis de ambiente e informações sobre o servidor
include($raiz."config/functions.php");

//print_r2($_SESSION);

// Recebe os dados enviados via POST (a inscrição)
$subscription = json_decode(file_get_contents('php://input'), true);

// Verifica se os dados foram recebidos corretamente
if (!$subscription) {
	_log("Erro ao obter os dados VAPID! [Dados inválidos]. Abortando registro da inscrição.");
	exit;
}
else {
	_log("Solicitação de notificação aceita. Dados VAPID válidos. Registrando inscrição...");
}

// Conexão com o banco de dados
include($raiz."config/db_conn_KUMON.php");
include($raiz."config/db_functions.php");

$logado_id = (isset($_SESSION["USU"]["ID"]) ? $_SESSION["USU"]["ID"] : 0);

$endpoint = $subscription['endpoint'];
$p256dh = $subscription['keys']['p256dh'];
$auth = $subscription['keys']['auth'];

//	 antes de gravar, verifico se já não exeiste esta inscrição

$result = getSelect("SELECT `id` FROM `t_subscriptions` WHERE `usu_id` = {$logado_id} AND `endpoint` = '{$endpoint}' AND `p256dh` = '{$p256dh}' AND `auth` = '{$auth}'");

if ($result !== false && count($result) == 0) {
	//	grava
	$dados_arr = array (
					0 => "t_subscriptions", 
					1 => array (
						"usu_id" => $logado_id,
						"endpoint" => $endpoint,
						"p256dh" => $p256dh, 
						"auth" => $auth, 
					)
				);

	$resp = sqlInsert($dados_arr);

	if (is_numeric($resp)) {
		_log("Inscrição para notificaçao registrada! [ENDPOINT: {$subscription['endpoint']}]");
	}
	else {
		_log_sql($resp[0], $resp[1], "Erro na tentativa de registrar inscrição.");
	}
}

else {
	_log("Inscrição já existe para este usuário [#{$logado_id}].");
}

?>
