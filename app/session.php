<?php

/*
	SESSÃO 
	Este script é chamado no início das páginas mostrada no sistema
*/

/*
	tempo máximo da sessão (segundos)
	default (php.ini) = 1440 (24 min)
*/

//ini_set( "session.gc_maxlifetime", 1800);	//	30 min

session_name("kumon");
session_start();

/**
 * Verifique se há instrução de LOGOUT
 */

if (isset($_POST["CLICOU"]) && $_POST["CLICOU"] == "SAIR") {
	// Remove todas as variáveis de sessão
	session_unset();

	// Destroi a sessão
	session_destroy();

	//	mostra a tela de login
	$_POST["page"] = "login";
}

////////////////////////////////////

$session_id = session_id();
$_SESSION["KEY"] = $session_id;	

if (! isset($_SESSION["LOGADO"]))
	$_SESSION["LOGADO"] = false;

if (! isset($_SESSION["LOGIN_ATTEMPT"]))
	$_SESSION["LOGIN_ATTEMPT"] = 0;

if (! isset($_SESSION["LOGIN_LAST_ATTEMPT"]))		//	data e hora do último insucesso no login
	$_SESSION["LOGIN_LAST_ATTEMPT"] = null;


if (! isset($_SESSION["LOGIN_ESPERA"]))
	$_SESSION["LOGIN_ESPERA"]	= 0;

?>