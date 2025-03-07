<?php
	include("session.php");						//	dados da sessão
	include("header.php");						//	definiçÕes de cabeçalho PHP
	include("constantes.php");					//	variáveis de ambiente e informações sobre o servidor
	
	/**
	 * Funcões específicas para o sistema
	 */
	include("config/functions.php");

	/**
	 * Conecta no banco usando PD e geranco a instância '$DB' e
	 * carrega funções específicas para o tratamento com a Base de Dados
	 */
	include("config/db_functions.php");

	/**
	 * Verifica a existência das chaves VAPID (para o notificações)
	 */
	
	if (! isset($_ENV['VAPID_PUBLIC_KEY']) || ! isset($_ENV['VAPID_PRIVATE_KEY'])) {
		_log("Erro: Chaves VAPID não carregadas!", 1);
	}
	
	//print_r2($_SESSION);
	//print_r2($_POST);
	//print_r2($_ENV);

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
						$Msg = shAlert("Senha incorreta!", "danger", false, true, "mb-3");
						$FailAuth = true;
						/* NÃO AUTENTICOU, SENHA INVÁLIDA */
					}
				}
				else {
					//	usuário NÃO existe na base local
					$Msg = shAlert("Usuário não cadastrado!", "danger", false, true, "mb-3");
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
					_log("Autenticado com sucesso! Login: [{$u}]. ID: [{$reg['id']}]. Base: [local]", 2);

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
				$id = $_SESSION["USU"]["ID"];
				$u = $_SESSION["USU"]["NOME"];
				
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
					$result = getSelect("SELECT `folha`, `estagio` FROM `t_desempenho` WHERE `usuario_id`={$logado_id} AND `disciplina_id` = {$disc_id} ORDER BY `dia` DESC LIMIT 1");

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

							//$Msg = shAlert($txt, $cor, true, true, "mb-3");
							_log("Adicionou novas folhas ({#$resp} em `t_desempenho`) [Dia = {$dia}; Folhas = {$qtde}]");
						}
						else {
							$Msg = shAlert("<b>ERRO</b>. Não foi possível adicionar novas folhas.", "danger", false, true, "mb-3");
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
				$usu_id = $_SESSION["USU"]["ID"];

				//	qual a disciplina?
				$disc_id = $_SESSION["DISCIPLINA"]["ID"];
				$disc_n = $_SESSION["DISCIPLINA"]["NOME"];
				
				$valor = $estagio * 200 + $folha;

				/**
				 * Obtenção do registro em t_meta
				 */

				//echo "usu_id = " . $usu_id;
				
				//	obtenha o ID do registro que define a meta em `t_meta`
				$id_reg = sqlResult("SELECT `id` FROM `t_meta` WHERE `usuario_id`={$usu_id} AND `disciplina_id`={$disc_id}", "id");

				//	se não há meta definida... criamos
				if ($id_reg == null) {
					$dados_arr = ["t_meta", ["usuario_id" => $usu_id, "disciplina_id" => $disc_id, "dia" => $meta_dia, "estagio" => $estagio, "folha" => $folha]];

					$resp = sqlInsert($dados_arr, $csrf_token);

					if ($resp != "F5") {
						if (is_numeric($resp)) {
							$Msg = shAlert("<b>Sucesso!</b> Sua meta foi definida!.", "success", true, true, "mb-3");
							_log("definiu a meta para [{$disc_n}]: [Dia = {$meta_dia}; Estágio: {$estagio}; Folhas = {$folha}]");
						}
						else {
							$Msg = shAlert("<b>ERRO</b>. Não foi possível definir a meta.", "danger", false, true, "mb-3");
							_log_sql($resp[0], $resp[1], "Erro na tentativa de definir a meta.");
						}
					}

				}
				else {	//	alteramos
					$dados_arr = ["t_meta", $id_reg, ["dia" => $meta_dia, "estagio" => $estagio, "folha" => $folha]];

					$resp = sqlUpdate($dados_arr, $csrf_token);

					if ($resp != "F5") {
						if (is_numeric($resp)) {
							$Msg = shAlert("<b>Sucesso!</b> Sua meta foi alterada!.", "success", true, true, "mb-3");
							_log("atualizou a meta: [Dia = {$meta_dia}; Estágio: {$estagio}; Folhas = {$folha}]");
						}
						else {
							$Msg = shAlert("<b>ERRO</b>. Não foi possível atualizar a meta.", "danger", false, true, "mb-3");
							_log_sql($resp[0], $resp[1], "Erro na tentativa de atualizar a meta.");
							echo $resp[0] . $resp[1];
						}
					}
				}
			break;

			//	adicionar nova disciplina
			case "ADD_DISC" :
				$disc_id = filter_input(INPUT_POST, "disc");
				$disc_n = filter_input(INPUT_POST, "disc_n");
				$dia = filter_input(INPUT_POST, "dia");
				$estagio_nr = filter_input(INPUT_POST, "est");			//	número do estágio (posição)
				$estagio_n = filter_input(INPUT_POST, "est_n");			//	o nome (A, B...)

				$usu_id = $_SESSION["USU"]["ID"];

				//	a disciplina tem o estágio selecionado?
				$sql = "SELECT `pos` FROM `t_estagios` WHERE `disciplina_id` =  {$disc_id} AND `cod` = '{$estagio_n}'";
				//echo $sql;
				$estagio = sqlResult($sql, "pos");

				if (empty($estagio) || $estagio = "SQL_ERROR") {
					$Msg = shAlert("Estágio inválido [<b>{$estagio_n}</b>] para a disciplina [<b>{$disc_n}</b>] escolhida!", "danger", true, true, "mb-2");
					$page = "login_disciplina";
				}
				else {
					//	confirma se o aluno já não faz esta disciplina
					$sql = "SELECT `id` FROM `t_usu_vs_disciplina` WHERE `usuario_id` = {$usu_id}";
					$result = sqlResult($sql, "id");

					if (! empty($result)) {
						$Msg = shAlert("Você já está cadastrado na disciplina [<b>{$disc_n}</b>] escolhida!", "warning", true, true, "mb-2");
						$page = "login_disciplina";
					}
					else {
						$csrf_token = filter_input(INPUT_POST, "csrf_token");

						//	insere o aluno na disciplina
						$dados_arr = ["t_usu_vs_disciplina",
										 ["usuario_id" => $usu_id, "disciplina_id" => $disc_id]
									];

						$resp = sqlInsert($dados_arr, $csrf_token);

						if ($resp != "F5") {
							if (is_numeric($resp)) {
								_log("Adicionou a disciplina [$disc_n].");
								$deu_certo = true;

								//	adiciona o registro inicial na tabela de desempenho

								/**
								 * Obter o estágio inicial
								 * Em t_desempenho o estágio é um número que corresponde à posição do estágio (label) na disciplina
								 * Ou seja, a sequência dos estágios muda com a disciplina
								 */

								$sql = "SELECT `pos` FROM `t_estagios` WHERE `disciplina_id` = {$disc_id} AND `cod` = '{$estagio_n}'";
								$est_n = sqlResult($sql, "pos");
 
								if (! is_int($est_n)) {		//	algo deu errado
									_log("Erro na obtenção do código do estágio. [{$sql}]", 1);
									$Msg = shAlert("Não foi possível adcionar a disciplina.", "danger", true, true, "mb-2");

									$deu_certo = false; 
									$page = "login_disciplina";
								}
								else {
									//	$est_n é o estágio inicial

									//	dia anterior
									$dia_anterior = date('Y-m-d', strtotime($dia . ' -1 day'));
 
									$dados_arr = ["t_desempenho",
											 ["usuario_id" => $usu_id, "disciplina_id" => $disc_id, "dia" => $dia_anterior, "qtde" => 0, "folha" => 0, "estagio" => $est_n]
										 ];
									
									$resp2 = sqlInsert($dados_arr);

									if (is_numeric($resp2)) {
										_log("Registro inicial adicionado em `t_desempenho` para disciplina [{$disc_n}].");

										//	aponta para a nova disciplina
										$disc_arr = getSelect("SELECT * FROM `v_usu_vs_disciplina` WHERE `usu_id`={$usu_id} AND `disc_id` = {$disc_id} LIMIT 1");

										$_SESSION["DISCIPLINA"]["ID"] = $disc_arr[0]["disc_id"];;
										$_SESSION["DISCIPLINA"]["N"] = $disc_arr[0]["disc_n"];
										$_SESSION["DISCIPLINA"]["L"] = $disc_arr[0]["disc_l"];
										$_SESSION["DISCIPLINA"]["A"] = $disc_arr[0]["disc_a"];
										$_SESSION["DISCIPLINA"]["D"] = $disc_arr[0]["disc_d"];
									}
									else {
										_log("Erro ao adicionar registro inicial em `t_desempenho` para disciplina [{$disc_n}].", 1);
										$deu_certo = false;

										$Msg = shAlert("Não foi possível adcionar a disciplina.", "danger", true, true, "mb-2");
										$page = "login_disciplina";
									}

								}

								if ($deu_certo === false) {
									/**
									 * Se algo deu errado ao tentar adicionar o registro inicial em `t_desempenho`,
									 * é preciso apagar o registro de add da disciplina
									 */
									$sql = "DELETE FROM `t_usu_vs_disciplina` WHERE `id` = {$resp}";
									$DB->exec($sql);
								}
 							}
							else {
								$Msg = shAlert("<b>ERRO</b>. Não foi possível adicionar a disciplina.", "danger", false, true, "mb-1");
								_log_sql($resp[0], $resp[1], "Erro na tentativa de adicionar disciplina.");
								$deu_certo = false;
							}
						}
					}
				}

			break;
		}
	}

	//	Definição dos estágios

	if (isset($_SESSION["DISCIPLINA"])) {
		
		$sql = "SELECT `cod` FROM `t_estagios` WHERE `disciplina_id` = {$_SESSION["DISCIPLINA"]["ID"]} ORDER BY `pos`";
		$estagios = getSelect($sql);

		$i = 1;
		foreach ($estagios as $estagio) {
			$disciplina_id = $estagio['disciplina_id']; // Obter o ID da disciplina
			$cod = $estagio['cod']; // Obter o código
		
			$estagio_arr[$i] = $cod;

			$i++;
		}

		//print_r2($estagio_arr);

	}
	else {
		$estagio_arr = null;
	}
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
		//	Solicitação de Notificação PUSH
		echo "<script src='push/notif-push.js'></script>";

		$logado_id = $_SESSION["USU"]["ID"];
		$logado_n = $_SESSION["USU"]["NOME"];
		$disc_id = $_SESSION["DISCIPLINA"]["ID"];
		$disc_n = $_SESSION["DISCIPLINA"]["N"];		//	nome da disciplina
		$disc_l = $_SESSION["DISCIPLINA"]["L"];		//	legenda da disciplina

		$onClick = "onClick=\"$('#chDisc').val('YES'); $('#clicou').val(''); $('#f_identificacao').submit();\"";
		$letra = "<span class='drop-cap' data-bs-toggle='tooltip' title='{$disc_n}' {$onClick}>{$disc_l}</span>";

		//	usado nas cláusulas WHERE SQL
		$this_user_t = "`usuario_id`={$logado_id} AND `disciplina_id`={$disc_id}";		// para tabelas
		$this_user_v = "`usu_id`={$logado_id} AND `disc_id`={$disc_id}";			//	para views

		$div_logado = "<div id='identificacao'>
						<form id='f_identificacao' class='form' style='background: bisque' method='POST' action=''>
							<h6 class='mb-0' style='display: inline-block;'>{$logado_n} {$letra}</h6>
							<input type='hidden' id='clicou' name='CLICOU' value='SAIR'>
							<button class='btn btn-sm btn-success float-end' style='margin-top: -3px;'><i class='fa-solid fa-right-from-bracket'></i></button>
							<input type='hidden' id='chDisc' name='chDisc' value=''></form>
						</div></form>";
		
		//	página a ser carregada
		if (empty($page)) {
			if (! isset($_POST["page"])) {
				$page = "home";
			}
			else {
				$page = $_POST["page"];
			}
		}
	}

	echo $div_logado;
	//echo "page = [{$page}]<br>";

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