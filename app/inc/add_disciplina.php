<?php

$csrf_token = bin2hex(random_bytes(32));

//	OPTIONS do campo DISCIPLINA

//  lista de disciplina que o usuário não faz
$sql = "SELECT * FROM `t_disciplinas` WHERE `id` NOT IN (SELECT `disciplina_id` FROM `t_usu_vs_disciplina` WHERE `usuario_id` = {$_SESSION['USU']['ID']})";

$result = getSelect($sql);

$op_disc = "<option value='' disabled selected hidden>Selecione...</option>";

foreach ($result as $linha) {
	$id = $linha["id"];
	$nome = $linha["nome"];

	$op_disc .= "<option value='{$id}'>{$nome}</option>";
}

//	fim do OPTIONS do campo DISCIPLINA


$hoje = date('Y-m-d');
$max = date('Y-m-d', strtotime($hoje . ' +7 day'));

echo "	<div id='ajax'></div>
		<form id='f_disc' class='form' method='POST' action=''>
			<h4 class='text-center'>Adicionar disciplina</h4>
			<input type='hidden' name='csrf_token' value='{$csrf_token}'>
			<div class='form-group mb-2'>
				<label for='disc' class='form-label'>Disciplina</label>
				<select id='disc' name='disc' class='form-select' required>{$op_disc}</select>
				<input type='hidden' id='disc_n' name='disc_n' value=''>
			</div>
			<div class='form-group row mb-4'>
				<div class='form-group col'>
					<label for='dia' class='form-label'>Dia</label>
					<input id='dia' name='dia' type='date' class='form-control' value='{$hoje}' max='{$max}' required>
				</div>
				<div class='form-group col'>
					<label for='est' class='form-label'>Estágio</label>
					<select id='est' name='est' class='form-select' required><option value=''></option></select>
					<input type='hidden' id='est_n' name='est_n' value=''>
				</div>
			</div>
			<div class='text-center'>
				<button id='addDisc' class='btn btn-success' disabled><i class='fa-solid fa-plus'></i> &nbsp;Adicionar</button>
			</div>
			<input type='hidden' name='page' value='home'>
			<input type='hidden' name='CLICOU' value='ADD_DISC'>
		</form>
	";


$bot_home = "<button class='btn btn-primary mt-2'><i class='fa-solid fa-house'></i> &nbsp;Voltar</button>";

echo "<div class='text-center'><form method='POST' action=''>{$bot_home}</form></div>";

?>

<script>
	$(document).ready(function () {
		//	ativa/desativa o submit (se tem ou não alteração nos campos)
		$('#f_disc').each(function(){
				$(this).data('serialized', $(this).serialize());
			}).on('change input', function(){
				$('#addDisc').attr('disabled', $(this).serialize() == $(this).data('serialized'));
		});

		//	ao selecionar uma disciplina
		$("#disc").change(function() {
			//	captura o nome da disciplina selecionada

			var selectedText = $("#disc option:selected").text(); // Obtém o texto do option selecionado
			$("#disc_n").val(selectedText); // Atribui ao input

			//	ajusta os options dos estágios de acordo com a disciplina selecionada
			var disc_id = $(this).val();

			$.ajax({
				url     : "inc/add_discilplina.ajax.php",
				type    : "post",
				data    : {
					disc_id	: disc_id			//	id da disciplina
				},
				success: function(html){ 
					$("#est").html(html);
					
					var selectedText = $("#est option:selected").text(); // Obtém o texto do estágio selecionado
					$("#est_n").val(selectedText); // Atribui ao input
				},
				error: function(html){
					var erro_ajax = "<div class='alert alert-danger alert-dismissible fade show text-center mb-2' role='alert'>		Erro ao carregar os estágios!<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";

					//$("#_debug").text(html);
					$("#ajax").html(erro_ajax);
				}
			});
		});

		//	ao selecionar um estágio
		$("#est").change(function() {
			//	captura o nome do estágio selecionado

			var selectedText = $("#est option:selected").text(); // Obtém o texto do option selecionado
			$("#est_n").val(selectedText); // Atribui ao input
		});
	})
</script>