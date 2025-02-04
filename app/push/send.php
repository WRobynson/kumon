<?php

$raiz = "../";

include_once($raiz . "config/functions.php");
include_once($raiz . "constantes.php");


require_once($raiz . "vendor/autoload.php");

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;


$notify = [
	'VAPID' => [
		'subject' => 'mailto:admin@xmatrix.com.br', // can be a mailto: or your website address
		'publicKey' => $_ENV["VAPID_PUBLIC_KEY"], // (recommended) uncompressed public key P-256 encoded in Base64-URL
		'privateKey' => $_ENV["VAPID_PRIVATE_KEY"], // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL 
	],
];

$endpoint = "https://updates.push.services.mozilla.com/wpush/v2/gAAAAABnohJ2y7s5wluj9akradO2mNjXIUZX4wOCWig993qoCk1qRJG_5KmDQApzz2Se4_f3JuA2oJV4DFt7LGqVJoY-wk-CEMxIjgmPEQIgPZrd9x5yA4ZMy6T6sS7sJfe-2nSxlIdVnB73XAnAITnmzO3L1pCbcexKscFFFgfRPHa8NtRgXFc";

$p256dh = "BNTrm_HQPY9GS762kycM8XBduBULRCQj8eaU17lUiG7TKP-XTBj-PNu9DXfxnsMd7JpCDDGRWzw_jdJ0x5ZIX3k";

$auth = "FWQvYD0zwexF-oMvEpU1Lw";

$subscriptionData = [
    "endpoint" => $endpoint,
    "expirationTime" => null,
    "keys" => [
        "p256dh" => $p256dh,
        "auth" => $auth
    ]
];

//	Texto da notificação

$title	= "Título da Notificação";
$msg 	= "Esta é a mensagem da notificação.";
$url 	= "./";

$notify_arr = [
    "title" => $title,
    "body" => $msg,
    "url" => $url,
    "icon" => URL . "img/kumon-feliz.png"
];

$webPush = new WebPush($notify);

$report = $webPush->sendOneNotification(
    Subscription::create($subscriptionData), 
    json_encode($notify_arr),
    ["TTL" => 5000]
);

if ($report->isSuccess()) {
    echo "Notificação enviada com sucesso!";
    _log("Notificação enviada com sucesso!");
} else {
    _log("Erro ao enviar notificação: {$report->getReason()}");
	echo "Erro ao enviar notificação: " . $report->getReason();
}

print_r2($report);