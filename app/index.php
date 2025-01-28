<?php
	include("session.php");						//	dados da sessão
	include("header.php");						//	definiçÕes de cabeçalho PHP
	include("definicoes.php");					//	variáveis de ambiente e informações sobre o servidor
	
	/**
	 * Funcões específicas para o sistema
	 */
	include("config/functions.php");

	/**
	 * Conecta no banco usando PD e geranco a instância '$DB' e
	 * carrega funções específicas para o tratamento com a Base de Dados
	 */
	include("config/db_functions.php");

	//print_r2($_SESSION);
	//print_r2($_POST);

	// MENSAGEM PARA O USUARIO
	$Msg = null;

	/**
	 * Processamento
	 */
	
	if (isset($_POST["CLICOU"])) {
		$csrf_token = filter_input(INPUT_POST, "csrf_token");
		$CLICOU = filter_input(INPUT_POST, "CLICOU");

		switch($CLICOU) {
			case "ENTRAR" :	//	login
				$u = filter_input(INPUT_POST, 'usuario');
				$p = filter_input(INPUT_POST, 'senha');	
				
				$debug = null;
				$LogFail = null;
				$FailAuth = true;

				$segueLogin = true;

				//	v_usuarios descrita em inc/usu.inc.php
				//	vejo se usuário existe na base
				$sql = "SELECT `id`, `login`, `nome`, `email`, `senha`, `ativo` FROM `v_usuarios` WHERE `login`=:u LIMIT 1";
				
				$stmt = $DB->prepare($sql);
				$stmt->bindParam(':u', $u, PDO::PARAM_STR);
				$stmt->execute();
				$reg = $stmt->fetchAll(PDO::FETCH_ASSOC);

				//	usuário existe na base local
				if (! empty($reg) && $p != "") {
					$reg = $reg[0];

					//	se for autenticação local
					if (MD5($p) == $reg["senha"]) {
						$FailAuth = false;
						/* AUTENTICOU LOCALMENTE!!! */
					}
					else {
						$Msg = shAlert("Senha incorreta!", "danger", false, "return", "mb-3");
						$FailAuth = true;
						/* NÃO AUTENTICOU, SENHA INVÁLIDA */
					}
				}
				else {
					//	usuário NÃO existe na base local
					$Msg = shAlert("Usuário não cadastrado!", "danger", false, "return", "mb-3");
				}

				// se autenticou - registra USUARIO e ID
				if (! $FailAuth) {
					//	
					$_SESSION["LOGADO"] = true;

					// para controle de formulário (evitar duplicação com F5)
					$_SESSION["csrf_token"] = null;

					$_SESSION["USU"]["NOME"] = $reg['nome'];
					$_SESSION["USU"]["ID"] = $reg['id'];
					$_SESSION["USU"]["EMAIL"] = $reg['email'];

					//	registro o acesso
					_log("Autenticado com sucesso! Login: [{$u}]. ID: [{$id}]. Base: [local]", 2);

					$_SESSION["LOGIN_LAST_ATTEMPT"] = null;
					$_SESSION["LOGIN_ATTEMPT"] = 0;
					$_SESSION["LOGIN_ESPERA"] = 0;

					//	quais disciplinas este usuário faz?
					$disc_arr = getSelect("SELECT * FROM `v_usu_vs_disciplina` WHERE `usu_id`={$_SESSION["USU"]["ID"]}");

					//	se o usuário faz só uma disciplina
					if (count($disc_arr) == 1) {
						$_SESSION["DISCIPLINA"]["ID"] = $disc_arr[0]["disc_id"];;
						$_SESSION["DISCIPLINA"]["N"] = $disc_arr[0]["disc_n"];
						$_SESSION["DISCIPLINA"]["L"] = $disc_arr[0]["disc_l"];
						$_SESSION["DISCIPLINA"]["A"] = $disc_arr[0]["disc_a"];
						$_SESSION["DISCIPLINA"]["D"] = $disc_arr[0]["disc_d"];
					}
					//	vai para a página inicial após a atutenticação
					$page = $_POST["page"];
				}

			break;
			
			//	faz mais de uma disciplina e escolheu uma
			case "DISCIPLINA" :	
				//	Disciplina escolhida (ID)
				$disc = filter_input(INPUT_POST, 'disciplina');	

				$disc_arr = getSelect("SELECT * FROM `t_disciplinas` WHERE `id` = {$disc}");

				$_SESSION["DISCIPLINA"]["ID"] = $disc;
				$_SESSION["DISCIPLINA"]["N"] = $disc_arr[0]["nome"];
				$_SESSION["DISCIPLINA"]["L"] = $disc_arr[0]["legenda"];
				$_SESSION["DISCIPLINA"]["A"] = $disc_arr[0]["abrev"];
				$_SESSION["DISCIPLINA"]["D"] = $disc_arr[0]["descricao"];
				
				//	registro a escolha
				_log("Usuário [{$u}]. ID: [{$id}] escolheu disciplina id {$disc}.", 2);

				$page = $_POST["page"];
			break;
			
			case "NOVO_LANC" :
				$confirmado = filter_input(INPUT_POST, "confirmado");

				if ($confirmado == "YES") {
					//	obtenha o ID de quem está logado
					$logado_id = $_SESSION["USU"]["ID"];

					//	qual a disciplina?
					$disc_id = $_SESSION["DISCIPLINA"]["ID"];

					$dia = filter_input(INPUT_POST, "dia");
					$qtde = filter_input(INPUT_POST, "qtde");

					//	obter o estágio e a última folha lançados
					$result = getSelect("SELECT `folha`, `estagio` FROM `t_desempenho` WHERE `usuario_id`={$logado_id} ORDER BY `dia` DESC LIMIT 1");

					$ult_est = $result[0]["estagio"];
					$ult_folha = $result[0]["folha"];

					$estagio = $ult_est;
					$folha = $ult_folha + $qtde;

					if ($folha > 200) {
						$estagio++;
						$folha = $folha - 200;
					}

					$dados_arr = ["t_desempenho", 
									["usuario_id" => $logado_id, "disciplina_id" => $disc_id, "dia" => $dia, "qtde" => $qtde, "folha" => $folha, "estagio" => $estagio]
								];

					$resp = sqlInsert($dados_arr, $csrf_token);

					if ($resp != "F5") {
						if (is_numeric($resp)) {
							
							if ($qtde < 0) {
								$img = "<img height='25' src='img/kumon-triste.png'>";
								$txt = "Que pena você não ter feito nada neste dia...";
								$cor = "warning";
							}
							elseif ($qtde < 5) {
								$img = "<img height='25' src='img/kumon-indiferente.png'>";
								$txt = "Você concluiu apenas {$qtde} novas folhas... Não desanime!";
								$cor = "warning";
							}
							elseif ($qtde < 9) {
								$img = "<img height='25' src='img/kumon-feliz.png'>";
								$txt = "<b>Parabéns!</b> Você concluiu {$qtde} novas folhas. Continue assim!";
								$cor = "success";
							}
							else {
								$img = "<img height='25' src='img/kumon-muito-feliz.png'>";
								$txt = "<b>Uau!</b> {$qtde} novas folhas concluídas. Você é demais!";
								$cor = "success";
							}
							
							$Msg = "<div class='alert alert-{$cor} alert-dismissible show text-center mb-3' role='alert'>
									{$img}&emsp;{$txt}
									<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>
									</div>";

							//$Msg = shAlert($txt, $cor, true, "return", "mb-3");
							_log("Adicionou novas folhas ({#$resp} em `t_desempenho`) [Dia = {$dia}; Folhas = {$qtde}]");
						}
						else {
							$Msg = shAlert("<b>ERRO</b>. Não foi possível adicionar novas folhas.", "danger", false, "return", "mb-3");
							_log_sql($resp[0], $resp[1], "Erro na tentativa de adicionar folhas concluídas.");
						}
					}
				}

			break;
		
			case "SALVAR_META":
				$meta_dia = filter_input(INPUT_POST, "meta_dia");
				$estagio = filter_input(INPUT_POST, "estagio");
				$folha = filter_input(INPUT_POST, "folha");

				//	obtenha o ID de quem está logado
				$logado_id = $_SESSION["USU"]["ID"];

				//	qual a disciplina?
				$disc_id = $_SESSION["DISCIPLINA"]["ID"];
				
				$valor = $estagio * 200 + $folha;

				/**
				 * Obtenção do registro em t_meta
				 */

				//echo "logado_id = " . $logado_id;
				$id_reg = sqlResult("SELECT `id` FROM `t_meta` WHERE `usuario_id`={$logado_id} AND `disciplina_id`={$disc_id}", "id");

				$dados_arr = ["t_meta", $id_reg, ["dia" => $meta_dia, "estagio" => $estagio, "folha" => $folha]];

				$resp = sqlUpdate($dados_arr, $csrf_token);

				if ($resp != "F5") {
					if (is_numeric($resp)) {
						$Msg = shAlert("<b>Sucesso!</b> Sua meta foi alterada!.", "success", true, "return", "mb-3");
						_log("atualizou a meta: [Dia = {$meta_dia}; Estágio: {$estagio}; Folhas = {$folha}]");
					}
					else {
						$Msg = shAlert("<b>ERRO</b>. Não foi possível atualizar a meta.", "danger", false, "return", "mb-3");
						_log_sql($resp[0], $resp[1], "Erro na tentativa de atualizar a meta.");
						echo $resp[0] . $resp[1];
					}
				}
			break;
		}
	}

	$estagio_arr = [
		null, "A1", "A2", "B1", "B2", "C1", "C2", "D1", "D2", "E1", "E2", 
		"F1", "F2", "G1", "G2", "H1", "H2", "I1", "I2", "J1", "J2", "K1", 
		"K2", "L1", "L2", "M1", "M2", "N1", "N2", "O1", "O2", "P1", "P2", 
		"Q1", "Q2", "R1", "R2", "S1", "S2", "T1", "T2", "U1", "U2", "V1", 
		"V2", "W1", "W2", "X1", "X2"
	];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="blue">
	<meta name="apple-touch-icon" content="logo/logo192.png">
	
	<title>Desempenho no Kumon</title>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js"></script>
	<script src="https://unpkg.com/@popperjs/core@2"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootbox@6.0.0/dist/bootbox.min.js"></script>
	<script src="https://code.highcharts.com/10/highcharts.js"></script>
	<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
	<script src="https://kit.fontawesome.com/e64da6c24c.js" crossorigin="anonymous"></script>
	
	<script src="config/script.js"></script>

	<link rel="manifest" href="/manifest.json">
	<link rel="stylesheet" type="text/css" href="config/reset.css">
	<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.14.1/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
	
	<link rel="stylesheet" type="text/css" href="config/estilos.css">

	<link rel="shortcut icon" type="image/x-icon" href="./logo/favicon.ico" />
</head>
<body>
<div class='geral'>
<h2 id='titulo' class='text-center mb-2'>Desempenho KUMON</h2>

<?php
	/**
	 * Verifique se há alguém logado
	 */

	 if (empty($_SESSION["LOGADO"])) {
		//	ninguém logado
		$logado_id = $logado_n = null;
		$page = "login";
		$div_logado = null;
	}
	else {
		$logado_id = $_SESSION["USU"]["ID"];
		$logado_n = $_SESSION["USU"]["NOME"];
		$disc_id = $_SESSION["DISCIPLINA"]["ID"];
		$disc_n = $_SESSION["DISCIPLINA"]["N"];		//	nome da disciplina
		$disc_l = $_SESSION["DISCIPLINA"]["L"];		//	legenda da disciplina

		$letra = "<span class='drop-cap' data-bs-toggle='tooltip' title='{$disc_n}'>{$disc_l}</span>";

		//	usado nas cláusulas WHERE SQL
		$this_user_t = "`usuario_id`={$logado_id} AND `disciplina_id`={$disc_id}";		// para tabelas
		$this_user_v = "`usu_id`={$logado_id} AND `disc_id`={$disc_id}";			//	para views

		$div_logado = "<div id='identificacao'>
						<form class='form' style='background: bisque' method='POST' action=''>
							<h6 class='mb-0' style='display: inline-block;'>{$logado_n} {$letra}</h6>
							<input type='hidden' name='CLICOU' value='SAIR'>
							<button class='btn btn-sm btn-success float-end' style='margin-top: -3px;'><i class='fa-solid fa-right-from-bracket'></i></button>
						</div></form>";
		
		//	página a ser carregada
		if (! isset($_POST["page"])) {
			$page = "home";
		}
		else {
			$page = $_POST["page"];
		}
	}

	echo $div_logado;

	//$page = "home";
	include("inc/{$page}.php");

?>
</div><!-- .geral -->
</body>
</html>

<script>
	//	a ideia é anular a função 'voltar'
	window.onload = function () {
		history.replaceState(null, "", window.location.href); // Substitui o histórico inicial
		window.onpopstate = function () {
			history.pushState(null, "", window.location.href); // Reinsere o estado atual
		};
	};

	//	para carregar os tooltips
	$('[data-bs-toggle="tooltip"]').tooltip();
</script>