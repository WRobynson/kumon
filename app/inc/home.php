<?php
/**
 * Antes de carregar a HOME PAGE
 * verifique se há DISCIPLINA escolhida
 */

if (!isset($_SESSION["DISCIPLINA"]) || (isset($_POST["chDisc"]) && $_POST["chDisc"] == "YES")) {
	include("inc/login_disciplina.php");
	exit;
}

/**
 * Página para registro do desempenho diário
 */

//	pegue o último registro
$result = getSelect("SELECT * FROM `t_desempenho` WHERE {$this_user_t} ORDER BY `dia` DESC LIMIT 1");

if (count($result) == 0) {
	$pos_atual_msg = "Aluno não iniciado nesta disciplina.";
}
else {
	$ult_dia = $result[0]["dia"];
	$folha_atual = $result[0]["folha"];
	$estagio_atual = $result[0]["estagio"];

	$pos_atual_msg = "Hoje estou na folha <b>{$folha_atual}</b> do estágio <b>{$estagio_arr[$estagio_atual]}</b>.";
}

//$ult_dia = sqlResult("SELECT MAX(`dia`) `dia` FROM `t_desempenho`", "dia");
//echo $ult_dia;

//	FORM da posição atual

echo "
	<form class='form'>
	<h4 class='text-center'>Posição atual</h4>
		<p class='text-center'>{$pos_atual_msg}</p>
	</form>
";

//	FIM do FORM da posição atual

//	FORM do registro diário

$dow_arr = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"];

$hoje = date('Y-m-d');	//	hoje

if (! isValidDate($ult_dia, 'Y-m-d'))
	$px_dia = $hoje;	//	hoje
else
	$px_dia = date('Y-m-d', strtotime($ult_dia . ' +1 day'));

//	este lançamento é de quantos dias atrás?
$dias = haQuantosDias($px_dia);

$dow_n = date('w', strtotime($px_dia));

//	Hoje, ontem ou anteontem...

if ($px_dia == $hoje)
	$qdo = " (hoje)";
elseif ($px_dia == date('Y-m-d', strtotime($hoje . ' -1 day')))
	$qdo = " (ontem)";
elseif ($px_dia == date('Y-m-d', strtotime($hoje . ' -2 day')))
	$qdo = " (anteontem)";
else
	$qdo = null;

$px_dia_sh = $dow_arr[$dow_n] . date(', d/m/Y', strtotime($px_dia)) . $qdo;
$px_dia_sh2 = $dow_arr[$dow_n] . date(', d/m/Y', strtotime($px_dia));		//	para o JS

$bgc = ($px_dia < $hoje ? "bg-warning" : "bg-success text-white");

/**
 * Para impedir duplicação com F5
 * $csrf_token é um valor aleatório que é enviado via POST nos formulários.
 * Quando os dados são gravados, este valor é atribuído a $_SESSION['csrf_token']
 * Quando $_SESSION['csrf_token'] == $POST['csrf_token'], é pq a página foi recaregada (F5)
 */
$csrf_token = bin2hex(random_bytes(32));

//	OPTIONS da quantidade de folhas feitas

//	opções de 1 a 10

// $op_folhas = null;
// for ($i = 0; $i <= 10; $i++) {
// 	$sel = ($i == 5 ? "selected" : null);
// 	$op_folhas .= "<option value='$i' {$sel}>{$i}</option>";
// }

//	opções de 3, 4, 5 e 10
$op_folhas = "
	<option value='' disabled selected hidden>Selecione...</option>
	<option value='0'>0</option>
	<option value='3'>3</option>
	<option value='4'>4</option>
	<option value='5'>5</option>
	<option value='10'>10</option>
	<option value='15'>15</option>
	<option value='20'>20</option>";

if ($px_dia > $hoje) {
	$msg = shAlert("Você está em dia com o Kumon.", "primary", false, true, "mt-2 mb-1");

	echo "
		<form id='form_lanc' class='form'>
			<div class='form-group'>
				<h4 class='text-center'>Registro diário</h4>
				<div class='text-center'>
					<img height='90' src='img/kumon-muito-feliz.png'>
				</div>
				{$msg}
			</div>
		</form>
	";
}
else {
	echo "
		<form id='form_lanc' class='form' method='POST' action=''>
			<div class='form-group'>
				<h4 class='text-center'>Registro diário</h4>
				<input type='hidden' name='csrf_token' value='{$csrf_token}'>
				<div class='form-group'>
					<label for='day' class='form-label'>Data</label>
					<input type='text' id='day' class='form-control {$bgc}' value='{$px_dia_sh}' readonly>
					<input type='hidden' id='day2' value='{$px_dia_sh2}'>
					<input type='hidden' name='dia' value='{$px_dia}'>
					<input type='hidden' id='dias' value='{$dias}'>
					<input type='hidden' name='CLICOU' value='NOVO_LANC'>
					<input type='hidden' id='confirmado' name='confirmado' value='NO'>
				</div>
				<div class='form-group mb-2'>
					<label for='qtde' class='form-label'>Quantidade de Folhas</label>
					<select id='qtde' name='qtde' class='form-select' required>{$op_folhas}</select>
				</div>
				<div class='text-center'>
					<button type='button' id='addFolhas' class='btn btn-success' disabled><i class='fa-solid fa-plus'></i> &nbsp;Adicionar</button>
				</div>
			</div>
		</form>
	";
}

//	FIM do FORM do registro diário

// Mensagem para o usuário
echo $Msg;

//	Formulário da META

/**
 * Obtenção dos dados da META
 */

$meta = getSelect("SELECT * FROM `v_meta` WHERE {$this_user_v}");

if (! empty($meta)) {
	$meta_dia = $meta[0]["dia"];
	$meta_ts = $meta[0]["ts"];
	$meta_est = $meta[0]["estagio"];
	$meta_folha = $meta[0]["folha"];
	$meta_valor = $meta[0]["valor"];

	$meta_dia2 = date('d/m/Y', strtotime($meta_dia));

	$meta_msg = "No dia [<b>{$meta_dia2}</b>], eu quero <br>concluir a folha [<b>{$meta_folha}</b>] do estágio [<b>{$estagio_arr[$meta_est]}</b>].";
}
else {
	$meta_dia = $meta_ts = $meta_est =  $meta_folha =  $meta_valor = $serie_meta = null;
	$meta_msg = "Ainda não há meta definida.";
}

$meta_dia = date('d M. Y',strtotime($meta_dia));

echo "
	<form class='form' method='POST' action=''>
	<h4 class='text-center'>Meta</h4>
		<p class='text-center'>{$meta_msg}</p>
		<div class='text-center'>
			<button class='btn btn-success' name='page' value='meta'><i class='fa-solid fa-pen-to-square'></i> &nbsp;Alterar</button>
		</div>
	</form>

";

//	FIM do formulário da META

//	para passar ao JS (popup de confirmação do lançamento)
$estagio_feito = ($folha_atual != 200 ? $estagio_arr[$estagio_atual] : $estagio_arr[$estagio_atual + 1]);

echo "<form method='POST' action=''>
			<button class='btn btn-primary mx-auto d-block' name='page' value='grafico'><i class='fa-solid fa-chart-line'></i> &nbsp;Gráficos</button>
		</form>";

?>

<script>
		$(document).ready(function () {
			//	ativa/desativa o submit (se tem ou não alteração nos campos)
			$('#form_lanc').each(function(){
				$(this).data('serialized', $(this).serialize());
			}).on('change input', function(){
				$('#addFolhas').attr('disabled', $(this).serialize() == $(this).data('serialized'));
		});	

		$('#addFolhas').on('click', function () {
			let folhas = $("#qtde").val();
			let dias = $("#dias").val();		//	lançamento de tos dias atrás?

			if (dias == 0)
				dia = $("#day2").val() + " (<b>hoje</b>)";
			else if (dias == 1)
				dia = $("#day2").val() + " (<b>ontem</b>)";
			else if (dias == 2)
				dia = $("#day2").val() + " (<b>anteontem</b>)";
			else
				dia = $("#day2").val() + " (<b>há "+dias+" dias</b>)";


			let qtde = parseInt(folhas, 10);

			if (qtde < 3) {
				emoji = "<div class='emoji'><img height='70' src='img/kumon-triste.png'></div>";
			}
			else if (qtde < 5) {
				emoji = "<div class='emoji'><img height='70' src='img/kumon-indiferente.png'></div>";
			}
			else  if (qtde < 9) {
				emoji = "<div class='emoji'><img height='70' src='img/kumon-feliz.png'></div>";
			}
			else {
				emoji = "<div class='emoji'><img height='70' src='img/kumon-muito-feliz.png'></div>";
			}

			let folha_atual = parseInt("<?php echo $folha_atual;?>", 10) + 1;
			if (folha_atual > 200)
				folha_atual = folha_atual - 200;
			let folha_final = folha_atual + qtde -1;
			let estagio_feito = "<?php echo $estagio_feito;?>";

			if (folhas > 0)
				folhas_txt = folhas +" <b>["+estagio_feito+" "+folha_atual+" - "+folha_final+"]</b>";
			else
				folhas_txt = "<b>[Nenhuma]</b>";

			let msg = "<table><tr><td>Dia:</td><td>"+dia+"</td></tr><tr><td>Folhas: &ensp;</td><td>"+folhas_txt+"</td></tr></table>"+emoji;

			bootbox.confirm({
				title: "É isto mesmo?", 
				message: msg,
				buttons: {
					confirm: {
						label: 'Sim',
						className: 'btn-success'
					},
					cancel: {
						label: 'Não',
						className: 'btn-danger'
					}
				},
				callback: function (result) {
					if (result) {
						$("#confirmado").val("YES");
						$("#form_lanc").submit();
					}
				}
			});
		});
	});
</script>