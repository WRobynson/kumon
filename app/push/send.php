<?php

/**
 * A execução deste script deve ser agendada no CRON
 */

$raiz = "../";

include_once($raiz . "constantes.php");
include_once($raiz . "config/functions.php");
include_once($raiz . "config/db_functions.php");

//  relaciona os alunos devederoes 
//  ainda não registraram o desempenho hoje

$devedores = getSelect("SELECT `usuario_id` FROM `v_devedores`");

if (count($devedores) == 0) {
	_log("NOTIF: Sem notificações para enviar. Todos em dia!");
	die();
}

//  segue o jogo

require_once($raiz . "vendor/autoload.php");

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;


$notify = [
	'VAPID' => [
		'subject' => 'kumon.xmatrix.com.br', // can be a mailto: or your website address
		'publicKey' => $_ENV["VAPID_PUBLIC_KEY"], // (recommended) uncompressed public key P-256 encoded in Base64-URL
		'privateKey' => $_ENV["VAPID_PRIVATE_KEY"], // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL 
	],
];

//	Dados da notificação

$title	= "Já fez seu KUMON hoje?";
$icon   = URL . "img/kumon-piscando.png";
$msg 	= "Não esqueça de registrar seu desempenho.";
$url 	= "./";

$notify_arr = [
	"title" => $title,
	"body" => $msg,
	"url" => $url,
	"icon" => $icon
];


foreach ($devedores as $devedor) {   //  para cada usuário
	$usu_id = $devedor["usuario_id"];

	//  lista os dispositivos do usuário
	$resut = getSelect("SELECT * FROM `t_subscriptions` WHERE `usu_id` = {$usu_id}");

	foreach ($resut as $linha) {
		$endpoint = $linha["endpoint"];
		$p256dh = $linha["p256dh"];
		$auth = $linha["auth"];

		$subscription_data = [
			"endpoint" => $endpoint,
			"expirationTime" => null,
			"keys" => [
				"p256dh" => $p256dh,
				"auth" => $auth
			]
		];

		$webPush = new WebPush($notify);

		$report = $webPush->sendOneNotification(
			Subscription::create($subscription_data), 
			json_encode($notify_arr),
			["TTL" => 5000]
		);

		if ($report->isSuccess()) {
			_log("Notificação enviada com sucesso! [Usuário: #{$usu_id}]");
			echo "Notificação enviada com sucesso! [Usuário: #{$usu_id}]<br>endpoint=[{$endpoint}]<br>aut=[{$auth}]";
		} else {
			 // Obtenha o motivo do erro
			 $errorReason = $report->getReason();

			//  $statusCode = $report->response->getStatusCode();

			//  if ($report->getStatus() == 410) {

			//  }

			_log("Erro ao enviar notificação: {$errorReason}", 1);
			echo "Erro ao enviar notificação: {$errorReason}";


		}

	}
}


// $endpoint = "https://wns2-bl2p.notify.windows.com/w/?token=BQYAAABscHUPu2%2f46tLVvxedLX7M2pXe4Rc0IyrNmYD00Tz3dOObxqKg3S3iAdJAY%2fPQdTGdldXS9cJMr7c3jCoW5I%2fyMnhlBocaIPqr3w2%2fnWh3Di5B%2f3nVByWefM%2bb67y2P2CFnu%2ftyTd02gmBY9FSvDHBb5gSvVCA7MtWosYutB3dWomtpGEHUTGKzwe7r04UURfrTAk%2f%2fcSvoKukFct4lt9wWoCND%2fAW8rBU7E1qVjnGgffcwJ8VfrGtIyh43saLCXlQhgD0tf5xPfiH%2bTvxBZlKn7%2fl2cfaXb9ufSUC%2f%2bMeVfFq0ndx3mtcfK7HydHPKF5vxTYsihPbV%2fGgoH%2b2uFj4";

// $p256dh = "BGgso1GJSiAzi0nTjsvbWtRTbP4L-62H07leow4NoW7GHcijoG-V1SfLd1sRjaaTEeVc1PlR8iW4u4y12GC-4Io";

// $auth = "ABGSaronBy7V_-yCD45FSg";

// $subscriptionData = [
// 	"endpoint" => $endpoint,
// 	"expirationTime" => null,
// 	"keys" => [
// 		"p256dh" => $p256dh,
// 		"auth" => $auth
// 	]
// ];



// $webPush = new WebPush($notify);

// $report = $webPush->sendOneNotification(
// 	Subscription::create($subscriptionData), 
// 	json_encode($notify_arr),
// 	["TTL" => 5000]
// );

// if ($report->isSuccess()) {
// 	echo "Notificação enviada com sucesso!";
// 	_log("Notificação enviada com sucesso!");
// } else {
// 	_log("Erro ao enviar notificação: {$report->getReason()}");
// 	echo "Erro ao enviar notificação: " . $report->getReason();
// }

// var_dump($report);