<?php
require_once("../definicoes.php");

//	carrego a classe
require(CLASS_MYSQLDUMP);

/**
 * Parâmetros específicos para este script
 */
$DBHOST		= $_ENV["MYSQL_HOST"];
$DBNAME 	= $_ENV["MYSQL_DATABASE"];
$DBUSER 	= $_ENV["MYSQL_USER"];
$DBSENHA	= $_ENV["MYSQL_PASSWORD"];

$DIR_BKP = DIR_BACKUP."/database";      //  /config/definicoes.php

/////////////////////////////////////////////////////
//// Extensão da classe FPDF para esta impressão ////
/////////////////////////////////////////////////////

class MySQLDump extends BackupMySQL {
	public function __construct() {
		// Chame o construtor da classe pai (OriginalClass) se necessário
		parent::__construct();
	}
}

$connection = [
	'host'		=> $DBHOST,
	'database'	=> $DBNAME,
	'user'		=> $DBUSER,
	'password'	=> $DBSENHA,
];

$backup = new MySQLDump();
$backup->setConnection ($connection);
$backup->setFolder ($DIR_BKP);
$backup->zip();
$backup->download();
//$backup->run();


?>