<?php

$diaSemana_arr = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');

/////////////////////

function trim2_old(string|null $str): string | null
 {
	if (empty($str)) 
		return null;

	$out = trim($str);   									// espacos antes e depois
	$out = preg_replace('/[^\S\r\n]+/', ' ', $out);			// espacos no meio (preserva os lines breaks)
	$out = preg_replace("/(\r?\n){3,}/", "\n\n", $out);		// retiro o excesso de linhas em branco (para textarea)

	return $out;
}

/**
 * Elimina espaços e quebras de linhas em excesso na string ou array.
 *
 * Retira espaços no início e no fim da string, substitui múltiplos espaços por um único espaço e
 * remove quebras de linha excessivas. Se o parâmetro for um array, a função aplicará a limpeza
 * recursivamente a todos os elementos.
 *
 * @param string|array|null $input A string ou array a ser tratado. Pode ser null.
 * @return string|array|null A string ou array livre de espaços e quebras de linhas desnecessários, ou null se o input for vazio.
 */

 function trim2(string|null|array $input): string | array | null
 {
	if (empty($input)) 
		return null;
	
	if (is_string($input)) {
		// Remover espaços extras em strings
		$input = preg_replace("/[^\S\r\n]+/", " ", trim($input));		// retiro os espacos (preserva os lines breaks)
		$input = preg_replace("/(\r?\n){3,}/", "\n\n", $input);		// retiro o excesso de linhas em branco (para textarea)
		
		return $input;
	}
	elseif (is_array($input)) {
		// Se for um array, percorre e aplica a função recursiva
		foreach ($input as $key => $value) {
			$input[$key] = trim2($value);		//	considere "$input[trim($key)] = trim2($value);" para limpar os índices também...
		}
		return $input;		// Retorna o array modificado
	}
	return $input;			// Retorna o valor original se não for string nem array
}


/**
 * Coloca um ponto final na string
 * 
 * Mantém reticências no final
 * @param string $str A string a ser pontuada
 * @return string
 */

function PtFinal($str)
{
	//	em construção...
	return preg_replace("/\.{0,2}$/", ".", $str);
}

/**
 * Mostra (ou retorna) um alerta em forma de mensagem
 * 
 * Pode escrever a mensagem ou retorná-la em uma variável
 * @param string $msg A mensagem a ser mostrada
 * @param string $tipo Seleciona a cor, de acordo com os padrões do bootstrap
 * @param bool $close Se mostra ou não o botão pra fechar a mensagem
 * @param string $type Se mostra o alerta (null) ou retorna pra uma variável ("return") 
 * @param string $classes Classes adicionais para a DIV com o alerta 
 * @return string DIV formatada com bootstrap
 */

function shAlert($msg, $tipo = "success", $close = true, $type = null, $classes = "mb-0")
{
	if ($close) {
		$bot = "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>";
		$dismissible = "alert-dismissible";
	} else {
		$bot = null;
		$dismissible = null;
	}

	$output = "
		<div class='alert alert-{$tipo} {$dismissible} fade show text-center {$classes}' role='alert'>
		{$msg}{$bot}
		</div>";

	if ($type == null)
		echo $output;
	else
		return $output;
}

/**
 * Avisa que a página está em construção
 * 
 * Pode escrever a mensagem ou retorná-la em uma variável
 * @param string $type Se mostra o alerta (null) ou retorna para uma variável ("return") 
 * @return string DIV formatada com bootstrap
 */

function construindo($type = null)
{
	shAlert("Em contrução... Favor não mexer enquanto esta mensagem estiver aparecendo. &emsp; &#128521;", "danger", 0, $type);
}

/**
 * Retorna o nome de uma variável
 * 
 * @param string $var Variável que se deseja pegar o nome 
 * @return string O nome da variável
 */

/**
 * Retorna o nome de uma variável
 * 
 * @param string $var Variável que se deseja pegar o nome 
 * @return string O nome da variável
 */

 function getVariableName($var)
 {
	 foreach ($GLOBALS as $varName => $value) {
		 // Melhorar a comparação para lidar com arrays e objetos
		 if (is_array($value) && is_array($var)) {
			 if ($value == $var) {
				 return $varName;
			 }
		 } elseif ($value === $var) {
			 return $varName;
		 }
	 }
	 return false;
 }

/**
 * Mostra o conteúdo de um array de forma amigável
 * 
 * @param array $arr Array a ser mostrado
 * @return string Conteúdo do array formatado
 */

function print_r2($arr, $var_name = null)
{
	if ($var_name == null)
		$var_name = getVariableName($arr);

	echo "<b>Conteúdo de \${$var_name}</b><br>";
	
	if ($var_name === false || ! is_array($arr)) {
		echo "Variável indefinida ou não é um Array!";
		return;
	}

	echo "<pre>";
	echo htmlspecialchars(print_r($arr, true));
	echo "</pre>";
}

/**
 * Mostra o título de uma página
 * 
 * @param string $tit O texto do título
 * @param int $op Opção
 * 	1 - título simples
 * 	2 - título com botão de opção à direita (ADMIN)
 * 	3 - título com botão para download de arquivo
 * 	4 - título com botão para executar um jaascript
 * @param string $target Página a ser mostrada (ex.: /inc/page)
 * @param string $bot_txt Texto do botão
 * @param string $bot_tit Título do botão (ao passar o mouse)
 * @param string $bot_img Imagem do botão (halflings)
 * @param string $bot_tipo Formato do botão (bootstrap)
 * @param bool $return Se retorna ou printa o título
 * @return string O título da página formatado
 */

function shTitle($tit, $op = 1, $target = null, $bot_txt = null, $bot_tit = "Click aqui", $bot_img = "cog",  $bot_tipo = "info", $return = false)
{
	if (($op == 2) && (isset($_SESSION['EHADMIN']))) {
		$bot = "<button type='button' class='btn btn-{$bot_tipo} btn-sm float-end' title='{$bot_tit}' onClick=\"Menu('{$target}')\"><span class='halflings halflings-{$bot_img}'></span>&ensp;{$bot_txt}</button>";
	}
	//	a opção 3 é para simular um CLICK num link HREF para fazer o download de um arquivo ($target)
	//	usado em inc/global/global.inc.php
	else if (($op == 3) && (isset($_SESSION['EHADMIN']))) {
		$bot = "<button type='button' class='btn btn-{$bot_tipo} btn-sm float-end' title='{$bot_tit}' onClick=\"document.getElementById('link').click();\"><span class='halflings halflings-{$bot_img}'></span>&ensp;{$bot_txt}</button><a id='link' href='{$target}' download hidden></a>";
	}
	//	a opção 4 é para executar uma função javascript
	else if (($op == 4) && (isset($_SESSION['EHADMIN']))) {
		$bot = "<button type='button' class='btn btn-{$bot_tipo} btn-sm float-end' title='{$bot_tit}' onClick=\"{$target}\"><span class='halflings halflings-{$bot_img}'></span>&ensp;{$bot_txt}</button>";
	} else
		$bot = null;

	//	saída

	if ($return)
		return "<div class='titulo clearfix'>{$tit}{$bot}</div>";
	else
		echo "<div class='titulo clearfix'>{$tit}{$bot}</div>";
}

/**
 * Calcula a diferença entre duas datas e retorna o resultado em formato completo ou resumido.
 *
 * O sinal de saída indica a relação entre as datas:
 * - (+) se $data2 for posterior a $data1
 * - (-) se $data2 for anterior a $data1
 *
 * Retorna "?" se alguma das datas fornecidas for inválida.
 *
 * @param string $data1 Data inicial no formato 'yyyy-mm-dd hh:mm:ss'
 * @param string|null $data2 Data final no formato 'yyyy-mm-dd hh:mm:ss'. 
 *                           Se não fornecida, será considerada a data e hora atual.
 * @param int $formato Formato de saída: 1 para resposta completa, 2 para resposta resumida
 * @param bool $sinal Se deve mostrar o sinal na saída (padrão é true)
 * @return string A diferença formatada entre as datas, com sinal indicando a relação
 */

function datediff($data1, $data2 = null, $formato = 1, $sinal = true)
{
	// Tenta criar os objetos DateTime
	try {
		$date1 = new \DateTime($data1);
		$date2 = $data2 ? new \DateTime($data2) : new \DateTime();
	} catch (\Exception $e) {
		// Retorna '?' se uma data for inválida
		return "?";
	}

	$dateDiff = $date1->diff($date2);

	$parts = [
		'y' => "{$dateDiff->y}a",
		'm' => "{$dateDiff->m}m",
		'd' => "{$dateDiff->d}d",
		'h' => "{$dateDiff->h}h",
		'i' => "{$dateDiff->i}min",
		's' => "{$dateDiff->s}s"
	];

	$result = '';

	switch ($formato) {
		case 1: // Resposta completa
			foreach ($parts as $key => $value) {
				if ($dateDiff->$key > 0) {
					$result .= $value;
				}
			}
			break;

		case 2: // Resposta resumida
			if ($dateDiff->y > 0) {
				$result = "{$parts['y']}{$parts['m']}{$parts['d']}";
			} elseif ($dateDiff->m > 0) {
				$result = "{$parts['m']}{$parts['d']}";
			} elseif ($dateDiff->d > 1) {
				$result = $parts['d'];
			} elseif ($dateDiff->d === 1) {
				$result = "{$parts['d']}{$parts['h']}";
			} elseif ($dateDiff->h > 0) {
				$result = "{$parts['h']}{$parts['i']}";
			} elseif ($dateDiff->i > 0) {
				$result = "{$parts['i']}{$parts['s']}";
			} else {
				$result = $parts['s'];
			}
			break;
	}

	// Adiciona o sinal se necessário
	if ($sinal) {
		return $dateDiff->invert ? "-{$result}" : "+{$result}";
	}

	return $result;
}

/**
 * Quando um fato ocorreu em relação ao momento atual
 * @param string $data A data a ser considerada (yyyy-mm-dd hh:mm:ss)
 * @return string Quando aconteceu (formatado)
 */
function quando($data)
{
	$dataAtual = new DateTime();
	$dataRecebida = new DateTime($data);

	// Verifica se é o mesmo minuto
	if ($dataAtual->format('Y-m-d H:i') == $dataRecebida->format('Y-m-d H:i')) {
		return "agora";
	}

	// Verifica se é o mesmo dia
	if ($dataAtual->format('Y-m-d') == $dataRecebida->format('Y-m-d')) {
		return $dataRecebida->format('H:i');
	}

	// Verifica se é ontem
	$ontem = clone $dataAtual;
	$ontem->modify('-1 day');
	if ($ontem->format('Y-m-d') == $dataRecebida->format('Y-m-d')) {
		return 'ontem';
	}

	// Verifica se é anteontem
	$anteontem = clone $ontem;
	$anteontem->modify('-1 day');
	if ($anteontem->format('Y-m-d') == $dataRecebida->format('Y-m-d')) {
		return 'anteontem';
	}

	// Verifica se é do mesmo ano
	if ($dataAtual->format('Y') == $dataRecebida->format('Y')) {
		return $dataRecebida->format('d/m');
	}

	// Retorna o dia com ano diferente
	return $dataRecebida->format('d/m/y');
}


/**
 * Validação de DATA
 * 
 * @param string $date_str A string com a data a ser testada
 * @param string $format O formato a ser testado
 * @param string $format_return O formato a ser retornado
 * @return false|string $data A data formatada 
 */

function isValidDate($date_str, $format, $format_return = null)
{
	// Tentar criar o objeto DateTime a partir do formato 'd/m/y'
	$date = DateTime::createFromFormat($format, $date_str);
	
	// Verificar se a data foi criada corretamente e se corresponde ao formato
	if ($date && $date->format($format) === $date_str)	//	data válidada
		if ( $format_return === null)
			return true;
		else
			return $date->format($format_return);
	else
		return false;
}

/**
 * Validação de CPF
 * 
 * @param string $cpf Em qualquer formato
 * @return false|string CPF (apenas os números)
 */

function isCPF($cpf)
{
	// Extrai somente os números
	$cpf = preg_replace('/\D/', '', $cpf);

	// Verifica se foi informado todos os digitos corretamente
	if (strlen($cpf) != 11) 
		return false;

	// Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
	if (preg_match('/(\d)\1{10}/', $cpf)) 
		return false;

	// Faz o calculo para validar o CPF
	for ($t = 9; $t < 11; $t++) {
		for ($d = 0, $c = 0; $c < $t; $c++) {
			$d += $cpf[$c] * (($t + 1) - $c);
		}
		$d = ((10 * $d) % 11) % 10;
		if ($cpf[$c] != $d) {
			return false;
		}
	}
	return $cpf;
}

/**
 * Formatação de CPF e CNPJ
 * 
 * @param string $cad CPF ou CNPJ em qualquer formaato
 * @return string CPF ou CNPJ devidamente formatado
 */

function cpfjFormat($cad)
{
	// Retire tudo que não for número.
	$cadastro = preg_replace("/\D/", "", $cad);

	if (strlen($cadastro) == 11) {
		return substr($cadastro, 0, 3) . "." . substr($cadastro, 3, 3) . "." . substr($cadastro, 6, 3) . "-" . substr($cadastro, -2);
	} elseif (strlen($cadastro) == 14) {
		return substr($cadastro, 0, 2) . "." . substr($cadastro, 2, 3) . "." . substr($cadastro, 5, 3) . "/" . substr($cadastro, 8, 4) . "-" . substr($cad, -2);
	} else
		return $cad;
}

/**
 * Formatação de número telefônico
 * 
 * @param string $tel Número telefônico
 * @return string Número formatado => (XX) XXXXX-XXXX
 */

function telFormat($tel)
{
	## Retirando tudo que não for número.
	$telefone = preg_replace("/\D/", "", $tel);

	if (strlen($telefone) == 9) {
		return substr($telefone, 0, 5) . "-" . substr($telefone, -4);
	} elseif (strlen($telefone) == 11) {
		return "(" . substr($telefone, 0, 2) . ") " . substr($telefone, 2, 5) . "-" . substr($tel, -4);
	} else
		return $tel;
}

function saramFormat($s)
{
	/*
		Formata o número do saram:
		
        XXXXXXX => XXX.XXX-X
    */

	## Retirando tudo que não for número.
	$saram = preg_replace("/\D/", "", $s);

	if (strlen($saram) == 7) {
		return substr($saram, 0, 6) . "-" . substr($saram, -1);
	} else
		return $saram;
}

function idFormat($i)
{
	/*
		Formata o número da identidade:
		
        XXXXXX => XXX.XXX
    */

	## Retirando tudo que não for número.
	$id = preg_replace("/\D/", "", $i);

	if (strlen($id) == 6) {
		return substr($id, 0, 3) . "." . substr($id, -3);
	} else
		return $id;
}

function chPasswdPolicy($password, $min_len = 8, $max_len = 60, $num = true, $letra = true, $minusc = false, $maiusc = false, $simbolo = false)
{
	// Build regex string depending on requirements for the password
	$regex = '/^';

	if ($num == 1) {
		$regex .= '(?=.*\d)';
	}				// ao menos um número
	if ($letra == 1) {
		$regex .= '(?=.*[A-Za-z])';
	}		// ao menos uma letra
	if ($minusc == 1) {
		$regex .= '(?=.*[a-z])';
	}			// ao menos uma letra minúscula
	if ($maiusc == 1) {
		$regex .= '(?=.*[A-Z])';
	}			// ao menos uma letra maiúscula
	if ($simbolo == 1) {
		$regex .= '(?=.*[^a-zA-Z\d])';
	}	// ao meos um caracter diferente dos acima

	$regex .= '.{' . $min_len . ',' . $max_len . '}$/';

	if (preg_match($regex, $password)) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Pega o IP do cliente
 * 
 * @return string Endereço IP do cliente
 */

function getUserIP()
{
	$client		=	@$_SERVER["HTTP_CLIENT_IP"];
	$forward	=	@$_SERVER["HTTP_X_FORWARDED_FOR"];
	$remote		=	$_SERVER["REMOTE_ADDR"];

	if (filter_var($client, FILTER_VALIDATE_IP))
		$IP = $client;

	elseif (filter_var($forward, FILTER_VALIDATE_IP))
		$IP = $forward;

	else
		$IP = $remote;

	if (filter_var($IP, FILTER_VALIDATE_IP))
		return $IP;
	else
		return "IP";
}

/**
 * Tenta autenticar no AD
 * 
 * @param string $usr Usuário (login)
 * @param string $pwd Senha do usuário
 * @param string $dn Domain Name
 * @param array $ips IPs dos servidores de domínio
 * @return int 0: autenticou; 1: não autenticou; 2 - AD fora
 */

function AuthAD($usr, $pwd, $dn = "dacta1.intraer", $ips = null)
{
	function serviceping($host, $port = 389, $timeout = 1)
	{
		$op = @fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$op) return 0; // AD fora
		else {
			fclose($op);   // fecha a conexao aberta
			return 1;    // AD esta no ar
		}
	}

	$usr .= "@" . $dn;

	// ##### Lista estatica de AD's, se nao usa DNS round robin na rede
	// $dclist = array('10.229.8.1', '10.229.8.2', '10.229.8.3');

	// ##### Lista Dinamica de ADs, usa o nome do dominio para selecionar um AD
	$dclist = gethostbynamel($dn);   // retorna false se nao conseguir traduzir o endereco em IPv4 

	if ($dclist === false) $dclist = array('10.229.8.1', '10.229.8.2', '10.229.8.3');

	// Procura um AD disponivel na lista. Se nao achar nenhum, retorna false
	if ($dclist) {
		foreach ($dclist as $k => $dc) if (serviceping($dc) == true) break;
		else $dc = 0;

		if (!$dc) return 2; // AD fora!

		else {
			// aqui eh garantido que o AD esta no ar. Nao ocorrera timeout
			$ldapconn = @ldap_connect($dc);
			if (!($bind = @ldap_bind($ldapconn, $usr, $pwd)))
				return 0;   // nao autenticou!
			else
				return 1;   // autenticou!
		}
	} else return 2; // AD fora!

}

/**
 * Registra um LOG do sistema
 * 
 * Grava a mensagem em arquivo
 * @param string $msg A mensaagem de LOG
 * @param int $tipo Tipo de log (0: Log de atividade; 1: Log de erro; 2: Log de acesso; 3: Log de alerta (segurança))
 */

function _log($msg, $tipo = 0)
{
	/*
		$tipo
				0	=>	Log de atividade
				1	=>	Log de erro
				2	=>	Log de acesso
				3	=>	Log de alerta (segurança)
				
		Arquivos de LOG definidos em 'definicoes.php'
	*/

	switch (true) {
		case preg_match('/1|erro|error/i', $tipo):
			$file = LOG_FILE_ERRO;
			break;

		case preg_match('/2|acesso|access/i', $tipo):
			$file = LOG_FILE_ACESSO;
			break;

		case preg_match('/3|alerta|alert/i', $tipo):
			$file = LOG_FILE_ALERTA;
			break;

		default:
			$file = LOG_FILE_ATIVIDADE;
	}

	$msg = preg_replace('~[[:cntrl:]]~', '', $msg); // remove all control chars (ex: \n \r)

	$datetime = new DateTime("now", new \DateTimeZone("UTC"));	//	ZULU
	$qdo = $datetime->format('Ymd_His.v\z');

	if (isset($_SESSION['LOGADO_NOME']))
		$u = $_SESSION['LOGADO_NOME'];
	else
		$u = "NO_LOGIN";

	$ip = getUserIP();

	$browser = $_SERVER['HTTP_USER_AGENT'];

	$quem = "{" . $u . " :: " . $ip . "}";
	
	//	se for log de acesso, registro o navegador
	if ($file == LOG_FILE_ACESSO)
		$quem = "{" . $u . " :: " . $ip . " :: " . $browser . "}";
	

	$log = sprintf("%s => %s. %s%s", $qdo, $quem, $msg, PHP_EOL);

	//	se o arquivo de LOG n existe, tento criar
	if (is_writable(DIR_LOG) && !file_exists($file)) {
		$arquivo = fopen($file, 'w');
	}

	if (is_writable($file)) {
		file_put_contents($file, $log, FILE_APPEND);	//	nova abordagem 28/05/22
	}
}

/**
 * LOG de tentativa de entrada direta nas páginas PHP
 * 
 * @param string $page A página tentando ser acessada
 */

function _log_ent_inv($page)
{
	_log("Tentativa de acesso direto em [{$page}]", 3);
}

/**
 * LOG de quando o valor de um campo não passa na validação (provável violação no HTML ou JS)
 * 
 * @param string $campo O campo a ser alterado
 * @param string $tab A tabela a qual o campo pertence
 */

function _log_campo_inv($campo, $tab = null)
{
	_log("Tentativa de registro de valor(es) inválido(s) {{$campo}} na tabela [{$tab}]", 3);
}

/**
 * LOG de quando quando há um erro de SQL
 * 
 * @param string $sql O SQL executado
 * @param string $erro O erro gerado pelo MySQL
 * @param string $msg A mensagem gravada no arquivo de LOG
 */

//	
function _log_sql($sql, $erro, $msg = "Erro na execução do SQL.")
{
	_log("{$msg}. SQL: [{$sql}]. Erro: [{$erro}]", 1);
}


/**
 * LOG de resposta a um comando shell
 * 
 * Transforma o Array com a saída da função 'exec()' em linhas de LOG
 * @param array $output_arr A saída do comando no shell
 * @param int $result Código do resultado do comando
 */

function _log_shell($output_arr, $result, $tipo = 0)
{
	_log("    Saída do comando (shell) executado:", $tipo);
	_log("        Código do resultado ($?): {$result}", $tipo);

	if (is_array($output_arr) && !empty($output_arr)) {
		if (count($output_arr) < 10)
			$digitos = 1;
		elseif (count($output_arr) < 100)
			$digitos = 2;
		else
			$digitos = 3;

		$i = 1;
		foreach ($output_arr as $linha) {
			$num = str_pad($i, $digitos, STR_PAD_LEFT);

			_log("        linha_{$num}: {$linha}", $tipo);

			$i++;
		}
	} else {
		_log("        Nada na saída do comando.", $tipo);
	}
}

/**
 * Retorna à página inicial em caso de perda de sessão
 * 
 * @return boolean
 * 
 *   - true: sessão válida (não perdeu)
 *   - false: perdeu a sessão
 */

function chkSession()
{
	if (! isset($_SESSION['LOGADO_NOME']) || ! isset($_SESSION['LOGADO_ID'])) {
		echo "<script>Menu('home');</script>";
		exit;
		return false;
	}

	return true;	
}

/**
 * Mostra uma dica sobre o preenchimento do campo
 * 
 * @param string $txt O texto explicativo
 * @return string O \<span\> estilizado
 */

function dicaCampo($txt)
{
	return "&emsp;<span class='halflings halflings-question-sign dica' data-bs-toggle='tooltip' data-bs-html='true' data-bs-placement='right' title='{$txt}'></span>";
}

/**
 * Ativar a validação de formulários
 * 
 * Função chamada ao final de arquivos AJAX que exibem formulários
 */

function formValidation()
{
	echo "
		<script language='javascript'>
			(function () {
			  'use strict'

			  // Fetch all the forms we want to apply custom Bootstrap validation styles to
			  var forms = document.querySelectorAll('.needs-validation')

			  // Loop over them and prevent submission
			  Array.prototype.slice.call(forms)
				.forEach(function (form) {
				  form.addEventListener('submit', function (event) {
					if (!form.checkValidity()) {
					  event.preventDefault()
					  event.stopPropagation()
					}

					form.classList.add('was-validated')
				  }, false)
				})
			})()
		</script>	
	";
}

/**
 * Exibe uma menssagem temporária de alerta
 * 
 * A mensagem aparece no canto superior direito
 * @param string $titulo O título da mensagem
 * @param string $msg O texto da mensagem
 * @param string $tipo O tipo de mensagem (bootstrab class)
 * @param int $tempo O tempo (em ms) que a mensagem ficará aparecendo
 * @return string A chamada JS (p_notify()) para mostrar a mensagem
 */

function PNotify($titulo, $msg, $tipo = "error", $tempo = 3000)
{
	//	/config/script.js
	echo "<script language='javascript'>p_notify('{$titulo}', '{$msg}', '{$tipo}', {$tempo});</script>";
}

/**
 * Formata um nome próprio deixando as iniciais em maiúsculo.
 * 
 * @param string $nome O nome a ser formatado
 * @return string O nome formatado com as inicias maiúsculas
 */

function formatName($nome)
{
	// Lista de palavras que não devem ser capituladas
	$exclude = ["de", "da", "do", "dos", "das", "e"];

	// Transforme tudo em minúsculo
	$nome = mb_strtolower($nome, 'UTF-8');
	
	// Divide o nome completo em palavras
	$palavras_arr = explode(" ", $nome);

	// Inicializa um array para armazenar as palavras capituladas
    $nomeC = array();

	foreach ($palavras_arr as $palavra) {
		if (in_array($palavra, $exclude)) {
			$nomeC[] = $palavra;
		} else {
			$nomeC[] = mb_convert_case($palavra, MB_CASE_TITLE, 'UTF-8');;
		}
	}

	// Concatena as palavras capituladas de volta em uma string
    $nomeCapitulado = implode(' ', $nomeC);

	return $nomeCapitulado;
}

/**
 * Reescreve o NOME completo destacando (caixa alta) o nome de guerra
 * 
 * Só a primeira ocorrência de cada nome em $ng será destacada
 * @param string $ng O nome de guerra
 * @param string $nome O nome completo
 * @return string O nome formatado
 */

function DestacaNG($ng, $nome)
{
	//	capitulo o nome
	$saida = formatName($nome);

	//	palavras a serem destacadas
	$ng_arr = explode(" ", $ng);

	foreach ($ng_arr as $palavra) {
		$palavra_destacada = mb_strtoupper($palavra);

		$saida = preg_replace("/\b" . "$palavra" . "\b/ui", $palavra_destacada, $saida, 1);
	}

	return $saida;
}

/**
 * Encerra a execução (DIE) de uma página com uma mensagem (pag HTML)
 * 
 * Emojis ref.: https://www.w3schools.com/charsets/ref_emoji.asp
 * @param string $tit Um título
 * @param string $msg A mensagem a ser mostrada
 * @param string $emoji A carinha
 * @param string $tipo Bootstrap Class
 * @return string Página HTML
 */

function die2($tit, $msg, $emoji, $tipo)
{
	$out = "
		<!DOCTYPE html> 
		<html lang='pt-br'
		<head>
			<meta charset='UTF-8'>
			<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
			<title>SIOP</title>
			<style>
				.tit {
					font-style: italic;
					font-family: serif;
					font-weight: bold;
					font-size: xx-large;
				}
				.alert {
					font: 14px verdana;
					border: 1px solid #842029;
					color: #842029;
					background-color: #f8d7da;
					padding: 10px;
					text-align: center;
					width: fit-content;
				}
				.warning {
					font: 14px verdana;
					border: 1px solid chocolate;
					color: chocolate;
					background-color: #fff3cd;
					padding: 10px;
					text-align: center;
					width: fit-content;
				}
			</style>
		</head>
		<body>
			<center>
			<div class='{$tipo}'>
				<p class='tit'>{$tit}</p>
				<p>{$msg}</p>
				<p style='font-size: x-large;'>&#{$emoji}</p>
			</div>
			</center>
		</body></html>
	";

	echo $out;
	die();
}

/**
 * Retorna o valor de uma constante
 * 
 * O nome da constante é passado como parâmetro
 * @return string|null O valor da constante
 */

function getConstantValue($constantName)
{
	if ($constantName == null)
		return null;

	if (defined($constantName)) {
		return constant($constantName);
	
	return null; // A constante não existe
	}
}

/**
 * Retorna uma saudação conforme a hora do dia
 * 
 * Bom dia, boa tarde ou boa noite.
 * @return string Uma saudação
 */

function saudacao()
{
	$hora_atual = date('H');

	if ($hora_atual < 12) 
		return 'Bom dia';
	if ($hora_atual < 18) 
		return 'Boa tarde';
	return 'Boa noite';
}

/**
 * Formata uma string JSON
 * @param string $json_string JSON em uma única linha
 * @return string JSON
 */
function str2json($json_string) 
{
    // Decodifica o JSON para um array associativo
    $array = json_decode($json_string, true);

    if ($array === null) {
        // Se o JSON for inválido, retorna a string original
        return $json_string;
    }

    // Converte o array associativo formatado de volta para JSON com caracteres especiais não codificados
    return json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Converte uma string em um nome de variável formatado
 * 
 * Esta função transforma uma string em um nome adequado para variáveis.
 * Removendo acentuação, convertendo para minúsculas, e substituindo
 * espaços por underscores. Se a string for nula ou vazia, retorna uma string vazia.
 * 
 * Exemplo: "Análise do mapa" => "analise_do_mapa"
 * 
 * @param string|null $str A string a ser convertida
 * @return string A sugestão como nome de variável
 */
function str2var(?string $str): string {
	// Limpar espaços extras e normalizar quebras de linha
	$var = trim2($str);

	if (empty($var))
		return '';
	
	// Retirar caracteres acentuados
	$var = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $var);
	
	// Transformar em minúsculo
	$var = mb_strtolower($var);
	
	// Substituir espaços por '_'
	$var = str_replace(" ", "_", $var);
	
	return $var;
}

/**
 * Converte um tamanho em bytes para forma humana
 * 
 * @param int $bytes Tamanho em bytes
 * @return float Tamanho formatado (B, KB, MB, GB ou TB)
 */

function formatSizeUnits($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;

    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Destaca uma palavra em um texto
 * 
 * @param string $word A palavra pesquisada.
 * @param string $text O texto onde a palavra será pesquisada.
 * @param string $class A classe CSS com a formatação de destaque.
 * @return string O texto com a palavra destacada.
 */

function highlight($word, $text, $class = 'destak') 
{
	// Função para substituir caracteres acentuados
	$substituirAcentos = function($str) {
		$acentos = array('á','à','â','ã','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','ô','õ','ö','ú','ù','û','ü','ç');
		$semAcentos = array('a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c');
		return str_replace($acentos, $semAcentos, $str);
	};

	// Substituir acentuação da palavra e do texto
	$wordSemAcentos = $substituirAcentos(mb_strtolower($word, 'UTF-8'));
	$textSemAcentos = $substituirAcentos(mb_strtolower($text, 'UTF-8'));

	// Encontrar todas as ocorrências da palavra ignorando a capitalização
	$positions = array();
	$pos = mb_stripos($textSemAcentos, $wordSemAcentos, 0, 'UTF-8');
	
	while ($pos !== false) {
		$positions[] = $pos;
		$pos = mb_stripos($textSemAcentos, $wordSemAcentos, $pos + 1, 'UTF-8');
	}

	// Ordenar as posições em ordem decrescente
	rsort($positions);

	// Substituir a palavra no texto pelo HTML desejado
	$wordLength = mb_strlen($word, 'UTF-8');
	$startTag = "<span class='$class'>";
	$endTag = "</span>";

	foreach ($positions as $pos) {
		// Inserir a tag <span class='destak'>
		$text = mb_substr($text, 0, $pos) . $startTag . mb_substr($text, $pos, $wordLength, 'UTF-8') . $endTag . mb_substr($text, $pos + $wordLength, mb_strlen($text, 'UTF-8'), 'UTF-8');
	}
 
	 return $text;
 }


function extractContextAroundTerms($termos_buscados, $texto, $contextLength = 70) {
	// Função para remover acentos e converter para minúsculas
	$normalizarTermo = function($termo) {
		$termo = mb_strtolower($termo, 'UTF-8');
		$termo = str_replace(
			['á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç'],
			['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c'],
			$termo
		);
		return $termo;
	};

	// Normalizar os termos buscados
	$termos_buscados_normalizados = array_map($normalizarTermo, $termos_buscados);

	// Normalizar o texto
	$texto_normalizado = $normalizarTermo($texto);

	// Variável para armazenar o contexto total
	$contexto_total = '';

	// Para cada termo buscado, extrair o contexto ao redor dele
	foreach ($termos_buscados_normalizados as $termo) {

		// Procurar a posição do termo normalizado no texto normalizado
		$pos = mb_stripos($texto_normalizado, $termo, 0, 'UTF-8');

		// Se o termo for encontrado, extrair o contexto ao redor dele
		if ($pos !== false) {
			// Determinar os índices de início e fim do contexto
			$startPos = max(0, $pos - $contextLength);
			$endPos = min(mb_strlen($texto, 'UTF-8'), $pos + mb_strlen($termo, 'UTF-8') + $contextLength);

			// Encontrar o início da palavra mais próxima antes do termo
			while ($startPos > 0 && !preg_match('/\s/u', mb_substr($texto, $startPos - 1, 1, 'UTF-8'))) {
				$startPos--;
			}

			// Encontrar o fim da palavra mais próxima após o termo
			while ($endPos < mb_strlen($texto, 'UTF-8') - 1 && !preg_match('/\s/u', mb_substr($texto, $endPos, 1, 'UTF-8'))) {
				$endPos++;
			}

			// Extrair o contexto ao redor do termo
			$contexto = mb_substr($texto, $startPos, $endPos - $startPos, 'UTF-8');

			// Remover quebras de linha e tags HTML do contexto
			$contexto = strip_tags($contexto);
			$contexto = str_replace(["\r", "\n","<br>", "<br />"], ' ', $contexto);

			// Destaque o termo buscado
			$contexto = highlight($termo, $contexto);

			// Adicionar o contexto ao total
			$contexto_total .= "<p>...{$contexto}...</p>";
		}
	}

	return $contexto_total;
}


/**
 * Exibe um texto 'puramente'. Considera aspas e não interpreta TAG HTML.
 * 
 * @param string $txt O texto a ser considerado.
 * @return string O texto (puro) sem interpretações de HTML e de aspas.
 */

function pureText($txt) {
	if ($txt != "")
		return htmlspecialchars($txt, ENT_QUOTES);
	else
		return null;
}

/**
 * Pega o valor de um array passando possíveis índices
 * @param array Um array
 * @param array Um array com os possíveis valores para o índice buscado
 * @return string|false
 */
function getValueForKey2($array, $possibleKeys) 
{
	if (! is_array($array))
		return null;

	foreach ($possibleKeys as $key) {
		if (array_key_exists($key, $array)) {
			return $array[$key];
		}
	}
	return null; 
}


/**
 * Obtém o formulário do checklist a partir do JSON
 * 
 * @param string $json_text O texto no formato JSON
 * @return array[0] O formulário em HTML
 * @return array[1] a resposta da análise
 */
function buildChecklistForm($json_text)
{
	// Decodifique o JSON em uma estrutura de dados PHP
	$chklist_arr = json_decode($json_text, true);

	//	se o texto JSON do checklist for válido
	if ($chklist_arr != null) {
		
		$i = 1;
		$chklist_form = "<div class='-div_checklist'>";

		$resposta = "<div class='-div_resposta'><h5 class='text-center'><u>Resposta da análise</u></h5>";

		$ok = "<span class='halflings halflings-ok text-success'></span>";
		$nok = "<span class='halflings halflings-remove text-danger'></span>";
		$warn = "<span class='halflings halflings-warning-sign text-warning'></span>";

		foreach($chklist_arr as $gr_key => $grupo) {	//	para cada grupo de itens...
			//	possíveis valores de chaves para o TÍTULO (no JSON)
			$possibleTitleKeys = ['TITLE', 'title', 'TÍTULO', 'título', 'Título', 'TIT', 'tit', 'Tit'];
			
			//	possíveis valores de chaves para o SUBTÍTULO (no JSON)
			$possibleSubtitKeys = ['SUBTITLE', 'subtitle', 'SUBTÍTULO', 'subtítulo', 'Subtítulo', 'SUBTIT', 'subtit', 'Subtit'];

			//	possíveis valores de chaves para os ITENS (no JSON)
			$possibleItemKeys = ['ITEMS', 'ITENS', 'items', 'itens', 'Itens', 'Items', 'Item', 'ITEM', 'item'];

			$tit = getValueForKey2($grupo, $possibleTitleKeys);
			$subtit = getValueForKey2($grupo, $possibleSubtitKeys);
			$itens_arr = getValueForKey2($grupo, $possibleItemKeys);

			$tit = trim2($tit);
			$subtit = trim2($subtit);

			/**
			 * $resposta será o resultado da análise feita com a construção do FORM
			 */
			
			$resposta .= "<span style='font-size: small; margin-top: 15px; display:block;'><b>Grupo {$i}</b></span>( {$tit} )<div class='-resp_grupo text-end'>";
			$resposta .= ($tit == null ? "<p>Sem TÍTULO ou chave inválida!</span> &emsp; {$nok}</p>" : "<span>TÍTULO definido.</span> &emsp; {$ok}</p>");
			$resposta .= ($subtit == null ? "<p><span>Sem SUBTÍTULO ou chave inválida!</span> &emsp; {$warn}</p>" : "<span>SUBTÍTULO definido.</span> &emsp; {$ok}</p>");
			
			($subtit == "" ? $subtit = null : $subtit = "<span class='-grupo_subtit'>{$subtit}</span>");

			if ($itens_arr == null) {
				$resposta .= "<p><span>Sem ITENS ou chave inválida!</span> &emsp; {$nok}</p>";
			}
			else {
				$resposta .= "<span>ITENS definidos.</span> &emsp; {$ok}</p>";

				$chklist_form .= "<div class='-div_checklist_grupo'>";
				$chklist_form .= "<h4 class='-grupo_tit'>{$tit}</h4>{$subtit}";
				$chklist_form .= "<input type='hidden' name='checklist_resp[{$i}][titulo]' value='{$tit}' \>";
				$chklist_form .= "<table class='-tab_itens'>";

				$j = 0;
				foreach($itens_arr as $item) {		//	para cada item deste grupo
					$url = getValueForKey2($item, ["URL", "url", "Url"]);
					
					// Verificar se o item é um array e contém uma URL
					if (is_array($item) && $url != "") {
						$item_id = "gr" . $gr_key . "_" . str2var($item["TEXT"]);

						$text = getValueForKey2($item, ["TEXT", "text", "Text", "TEXTO", "texto", "Texto"]);
						$link = getValueForKey2($item, ['LINK', 'link', 'Link']);

						$text = trim2($text);
						$link = trim2($link);
						$url = trim2($url);

						if ($link != "") {
							if (stripos($text, $link) !== false) {
								// Usamos preg_replace_callback para substituir a palavra de forma case-insensitive
								$item_text = preg_replace_callback(
									"/\b" . preg_quote($link, '/') . "\b/i",
									function($matches) use ($url) {
										// Substitui a palavra encontrada pelo link, mantendo a forma original
										return "<a href='{$url}' target='_blank'>" . $matches[0] . "</a>";
									},
									$text
								);
							} else {
								// Caso a palavra não seja encontrada, cria um link padrão (todo o texto)
								$item_text = "<a href='{$item['URL']}' target='_blank'>{$item['TEXT']}</a>";
							}
						} else {
							// Se não há uma palavra para procurar, cria um link padrão (todo o texto)
							$item_text = "<a href='{$item['URL']}' target='_blank'>{$item['TEXT']}</a>";
						}
					} else {
						$item_id = "gr" . $gr_key . "_" . str2var($item);

						// Se não houver URL, exibir apenas o texto do item
						$item_text = is_array($item) ? $item['TEXT'] : $item;
					}
					
					//	retire as tags HTML (em caso de URL)
					$item_text2 = strip_tags($item_text);

					$chklist_form .=  "<tr>
						<td><input class='form-check-input' type='checkbox' value='' id='{$item_id}' data-pos='gr{$gr_key}_{$j}' required>
							<label class='form-check-label resize_chk' for='{$item_id}'>
								{$item_text}
							</label>
						</td>
						<td style='width: 100%'>
							<input type='text' class='form-control form-control-sm' id='{$item_id}_obs' name='checklist_resp[{$i}][itens][{$item_text2}]' data-pos='gr{$gr_key}_{$j}' placeholder='Alguma observação?' maxlength='250' tabindex='-1' width='100%'>
						</td></tr>
					";

					$j++;
				}
				
				$chklist_form .= "</table></div>";
			}
			$resposta .= "</div>";

			$i++;	//	próximo grupo
		}
		
		$chklist_form.= "</div>";	//	fim do FORM

		$resposta .= "</div>";	//	fim da resposta (análise do JSON)
	}
	else {
		$chklist_form = shAlert("O Checklist não apresenta uma forma JSON válida! Favor verificar o texto.", "danger", false, "return");
		$resposta = "<div>JSON inválido!</div>";
	}

	return [$chklist_form, $resposta];
}