<?php

use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// carrego e instancio a classe

require SITE_ROOT."/lib/phpmailer/src/Exception.php";
require SITE_ROOT."/lib/phpmailer/src/PHPMailer.php";
require SITE_ROOT."/lib/phpmailer/src/SMTP.php";

/**
 * Configurações GLOBAIS para o envio de e-mail
 */

$MAIL = new PHPMailer(true);

//$MAIL->SMTPDebug = 3;
$MAIL->CharSet = "UTF-8";
$MAIL->IsSMTP();				// set mailer to use SMTP
$MAIL->IsHTML(true);			// set email format to HTML
$MAIL->SMTPAuth = true;			// set email format to HTML
$MAIL->AddReplyTo("no-reply@siop.cindacta1", "NÃO RESPONDA");

// se o server nao tiver um certificado SSL válido...

$MAIL->SMTPOptions = array(
	'ssl'	=> array (
					'verify_peer'		=> false,
					'verify_peer_name' 	=> false,
					'allow_self_signed' => true
				)
);

/*
 *	Parâmetros dos servidores de e-mail cadastrados
 *	presentes em `t_config_sys_mail`
*/ 

$result = getSelect("SELECT * FROM `t_config_sys_mail` WHERE NOT `excluso` ORDER BY `prioridade` ASC");

$config_sys_mail = [];				//	relação de todos os servidores cadastrados
$smtp_avail = [];					//	relação dos servidores (ID) disponíveis

foreach ($result as $linha) {

	$config_sys_mail[$linha["id"]] = [
		"perfil" 	=> $linha["perfil"],
		"server" 	=> $linha["mail_server"],
		"user" 		=> $linha["mail_user"],
		"pass" 		=> $linha["mail_pass"],
		"crypt" 	=> $linha["mail_crypt"],
		"port" 		=> $linha["mail_port"],
		"from" 		=> $linha["mail_from"],
		"from_name" => $linha["mail_from_name"],
		"obs" 		=> $linha["obs"],
		"ativo" 	=> $linha["ativo"],
		"teste" 	=> $linha["teste"]
	];

	if ($linha["ativo"] && ! $linha["teste"])
		$smtp_avail[] = $linha["id"];
}

/**
 * Função para escolha do servidor de envio de e-mail
 * --
 * @param int $server O ID do servidor em `t_config_sys_mail`
 * @return boolean False se o servidor escolhido não existir
 */

 function chooseSMTP($server)
 {
	 global $MAIL;
	 global $config_sys_mail;
 
	 if (array_key_exists($server, $config_sys_mail)) {
		 $MAIL->From 		= $config_sys_mail[$server]["from"];
		 $MAIL->FromName 	= $config_sys_mail[$server]["from_name"];
		 $MAIL->Host 		= $config_sys_mail[$server]["server"];	
		 $MAIL->Port 		= $config_sys_mail[$server]["port"]; 
		 $MAIL->Username 	= $config_sys_mail[$server]["user"];
		 $MAIL->Password 	= $config_sys_mail[$server]["pass"];
		 $MAIL->SMTPSecure	= $config_sys_mail[$server]["crypt"];				//	'' ou 'ssl' ou 'tls'
 
		 return true;
	 }
	 else
		 return false;
 }

/**
 * Testa uma conexão como um servidor SMTP
 */

function testSmtpConn($server_id) 
{
	global $MAIL;

	chooseSMTP($server_id);

	try {
		// Ativar o debug
		$MAIL->SMTPDebug = 3;

		//	Espere no máximo 3 segundos
		$MAIL->Timeout = 2;
		
		$debugOutput = '';
		$MAIL->Debugoutput = function($str) use (&$debugOutput) {
			$debugOutput .= $str;
		};

		// Conectar com o servidor SMTP
		$MAIL->smtpConnect();

		return ["condicao" => true, "debug" => "<pre>{$debugOutput}</pre>"];

	} catch (Exception $e) {
		return ["condicao" => false, "debug" => "<p>{$e->getMessage()}</p><pre>{$debugOutput}</pre>"];
	}
}

/**
 * Função que gera o corpo do email
 * --
 * @param string $conteudo O texto contendo a mensagem
 * @param string $style Folha de estilos para o conteúdo da mensagem
 * @return string Retorna o HTML da mensagem a ser enviada
 */

function EmailBody($conteudo, $style = null)
{
	return "<HTML lang='pt-br'>
				<HEAD>
					<META charset='UTF-8'>
					<META http-equiv='Content-Type' content='text/html; charset=UTF-8' />
					<style>
						{$style}
						.assinatura {
							margin: 5px 0;
						}
						
						.fnr {
							display: inline-block;
							border-radius: 10px;
							color: #f00; 
							background: #ff0;
							font-style: italic;
							padding: 4px;
						}
					</style>
				</HEAD>
				<BODY bgColor='#FFFFFF'>
					<div>
						{$conteudo}
					</div>
					<p>Cordialmente,<br>--</p>
					<img src='https://i.ibb.co/stSXdFk/mail-sign.png' alt='SIOp' border='0' height='90' class='assinatura'>
					<p><span class='fnr'>Esta é uma mensagem automática. Favor não responder.</span></p>
				</BODY>
			</HTML>";
}

/**
 * Envia uma mensagem de e-mail para um ou mais destinatários
 * --
 * @param array $para_emails_arr Um array com o endereço de e-mail dos destinatários
 *   - [0] => "nome1@mail.com, NOME_1"
 *   - [1] => "nome2@mail.com, NOME_2 [(cc) | (cco)]"
 * @param string $assunto O assunto da mensagem
 * @param string $conteudo O conteúdo da mensagem (HTML)
 * @param string $style A folha de estilos do conteúdo (sem a TAG <style>)
 * @param int $server O número do servidor de e-mail.
 *   - Se zero, o envio será tentado em cada servidor cadastrado.
 * @return array[] Retorna um array associativo contendo os seguintes índices:
 *   - 'condicao' (boolean): Indica se o envio do e-mail foi bem-sucedido ou não.
 *   - 'conteudo' (string): O conteúdo (HTML) da mensagem que foi enviada.
 *   - 'debug' (string): O debug da tentativa de envio.
 */

function SendEmail($para_emails_arr, $assunto, $conteudo, $style = null, $server = 0) 
{
	global $MAIL;
	global $smtp_avail;

	$debug = "<div>";
		
	$debug .= "&lt;&lt;----<br />Iniciando função de envio de e-mail...<br />";
	
	$MAIL->Subject = $assunto;
	$MAIL->Body = EmailBody($conteudo, $style);

	$debug .= "&nbsp;Adicionando endereços:<br />";
	
	//	adicione os endereços dos destinatários
	foreach ($para_emails_arr as $value) {
		/**
		 * Exeplos de linhas...
		 * --
		   "nome1@mail.com, NOME_1"
		   "nome2@mail.com, NOME_2 [cc | cco]"
		 */
		
		//	retiro eventuais espaços em branco 
		$value = trim2($value);	

		//	trato os possíveis separadores (coloco como ',')
		$value = preg_replace('/ ?[,;:|\/]+/', ',', $value);

		/**
		 * Verificação se é para adicionar o endereço como Cc ou Cco
		 */

		//	a princípio, considere AddAdress
		$addCC = $addCCO = false;

		//	confira se há "(cc)" no final
		$value2 = preg_replace('/\s?\(cc\)$/i', '', $value);
		if ($value != $value2) {
			$addCC = true;
			$value = $value2;
		}
		else {
			//	confira se há "(cco)" no final
			$value2 = preg_replace('/\s?\(cco\)$/i', '', $value);
			if ($value != $value2) {
				$addCCO = true;
				$value = $value2;
			}
		}
		
		//	se tem o NOME após o endereço (separado por ',')
		if (strpos($value, ",") !== false) {
			list($para_email, $para_nome, ) = explode (",", $value);
			
			$para_email = trim($para_email);
			$para_nome = trim2($para_nome);				
		}
		else {
			$para_email = trim($value);
			$para_nome = null;
		}
		
		//	adicione o destinatário
		if ($addCC) {
			$MAIL->AddCC($para_email, $para_nome);
			$debug .= "&nbsp;&nbsp;{$para_email}, {$para_nome} (cc) <br />";
		}
		elseif ($addCCO) {
			$MAIL->AddBCC($para_email, $para_nome);
			$debug .= "&nbsp;&nbsp;{$para_email}, {$para_nome} (cco) <br />";
		}
		else {
			$MAIL->AddAddress($para_email, $para_nome);
			$debug .= "&nbsp;&nbsp;{$para_email}, {$para_nome} <br />";
		}
	}

	//	se um servidor foi informado
	if ($server != 0) {
		
		//	se ele existe cadastrado e está disponível (ou é o de testes)
		if (in_array($server, $smtp_avail) || $server == 1) {
			$debug .= "&nbsp;Servidor escolhido: (#{$server}) está entre os disponíveis.<br />";

			//	pegue os parâmetros do servidor escolhido
			chooseSMTP($server);

			//	tente enviar o e-mail
			try {
				$MAIL->Send();
		
				$debug .= "&nbsp;E-mail enviado com sucesso.<br />";
				$debug .= "&nbsp;&nbsp;Assunto: {$assunto}<br />";
				
				$Enviou = true;
			}
			catch (Exception $e) {
				$debug .= "&nbsp;Não foi possível o envio do email:<br />&nbsp;&nbsp;{$MAIL->ErrorInfo}<br />";
				$Enviou = false;
			}
			
			$debug .= "&nbsp;Fim da função de envio de e-mail.<br />----&gt;&gt;<br />";
			$debug .= "&nbsp;Servidor: [{$MAIL->Host}]. Porta: [{$MAIL->Port}]. Username = [{$MAIL->Username}]. Senha: [{$MAIL->Password}]. Secure: [{$MAIL->SMTPSecure}] <br />";
			$debug .= "</div>";
	
			return array('condicao' => $Enviou, 'conteudo' => $MAIL->Body, 'debug' => $debug);

		}
		else {
			$debug .= "&nbsp;Servidor escolhido (#{$server}) não disponível.<br />";
			$debug .= "&nbsp;&nbsp;(Deve estar ativado e não ser de testes.)<br />";
			$debug .= "</div>";

			return ["condicao" => false, "conteudo" => null, "debug" => $debug];
		}
	}
	
	//	não escolhi um servidor. O sistema tentará um por um
	else {
		//	servidores de email cadastrados
		//	ver (/admin/inc/config_sys/config_sys_mail.php)

		foreach( $smtp_avail as $servidor) {
			//	pegue os parâmetros...
			chooseSMTP($servidor);
			$debug .= "&nbsp;Tentando enviar via servidor #{$servidor}.<br />";

			try {
				$MAIL->Send();
				
				$debug .= "&nbsp;E-mail enviado com sucesso pelo serivodr #{$servidor}.<br />";
				$debug .= "&nbsp;Servidor: [{$MAIL->Host}]. Porta: [{$MAIL->Port}]. Username = [{$MAIL->Username}]. Senha: [{$MAIL->Password}]. Secure: [{$MAIL->SMTPSecure}] <br />";
				$debug .= "&nbsp;&nbsp;Assunto: {$assunto}<br />";

				//	E-mail enviado. Saia do laço.
				$Enviou = true;
				break;
			}
			catch (Exception $e) {
				$debug .= "&nbsp;Não foi possível o envio do email pelo servidor #{$servidor}:<br />&nbsp;&nbsp;{$MAIL->ErrorInfo}<br />";
				$debug .= "&nbsp;Servidor: [{$MAIL->Host}]. Porta: [{$MAIL->Port}]. Username = [{$MAIL->Username}]. Senha: [{$MAIL->Password}]. Secure: [{$MAIL->SMTPSecure}] <br />";
				$Enviou = false;
			}
		}
			
		//	se o e-mail foi enviado, o laço é quebrado e segue daqui...
		$debug .= "&nbsp;Fim da função de envio de e-mail.<br />----&gt;&gt;<br />";
		
		$debug .= "</div>";

		return array('condicao' => $Enviou, 'conteudo' => $MAIL->Body, 'debug' => $debug);
		
	}
}


/**
 * Função usada para o envio de email de teste
 * --
 * @param string $para_email O endereço de e-mail do desdinatário
 * @param int $server O ID do servidor SMTP em `t_config_sys_mail`
 * @return array[] Retorna um array associativo contendo os seguintes índices:
 *   - 'condicao' (boolean): Indica se o envio do e-mail foi bem-sucedido ou não.
 *   - 'conteudo' (string): O conteúdo (HTML) da mensagem que foi enviada.
 *   - 'debug' (string): O debug da tentativa de envio.
 */

function sendEmailTest($para_email, $server) 
{
	global $MAIL;

	//	pegue os parâmetros do servidor escolhido
	if (chooseSMTP($server)) {
		$debug = "<div>";
			
		$debug .= "&lt;&lt;----debug<br />&nbsp;Iniciando função de envio de e-mail...<br />";
		
		$conteudo = EmailBody("<p>Esta é uma mensagem de teste.</p>");
		
		try {
			$MAIL->Subject = "Teste de envio de e-mail";
			$MAIL->Body = $conteudo;
			$MAIL->AddAddress($para_email);
			$MAIL->AddReplyTo("no-reply@siop.cindacta1", "NAO RESPONDA");

			$MAIL->Send();
			$debug .= "&nbsp;E-mail enviado com sucesso para <b><i>{$para_email}</i></b>.<br />";
			
			$Enviou = true;
		}
		catch (Exception $e) {
			$debug .= "&nbsp;Não foi possível o envio do e-mail para <b><i>{$para_email}</i></b>...<br>&nbsp{$MAIL->ErrorInfo}.<br />";

			$debug .= "&nbsp;Servidor: [{$MAIL->Host}]. Porta: [{$MAIL->Port}]. Username = [{$MAIL->Username}]. Senha: [{$MAIL->Password}]. Secure: [{$MAIL->SMTPSecure}] <br />";
			
			$Enviou = false;
		}

		$debug .= "&nbsp;Fim da função de envio de e-mail.<br />----&gt;&gt;<br />";

		$debug .= "</div>";
		
		//return $Enviou;
		return array('condicao' => $Enviou, 'conteudo' => $MAIL->Body, 'debug' => $debug);
	}
	else {
		$debug = "<div>
					&lt;&lt;----debug<br />&nbsp;Iniciando função de envio de e-mail...<br />
					&nbsp;O servidor de -mail selecionado (id #{$server}) não existe.<br />
					&nbsp;Fim da função de envio de e-mail.<br />----&gt;&gt;<br />
				</div>";

		return array('condicao' => false, 'conteudo' => null, 'debug' => $debug);
	}
}

?>
