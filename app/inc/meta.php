<?php

/**
 * Para impedir duplicação com F5
 * $csrf_token é um valor aleatório que é enviado via POST nos formulários.
 * Quando os dados são gravados, este valor é atribuído a $_SESSION['csrf_token']
 * Quando $_SESSION['csrf_token'] == $POST['csrf_token'], é pq a página foi recaregada (F5)
 */
$csrf_token = bin2hex(random_bytes(32));

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
}
else
	$meta_dia = $meta_ts = $meta_est =  $meta_folha =  $meta_valor = null;

//	pegue o estágio atual
$estagio_atual = sqlResult("SELECT MAX(`estagio`) `est` FROM `t_desempenho` WHERE {$this_user_t}", "est");

//	OPTIONS dos estágios a serem alcançados

for ($i = $estagio_atual + 1; $i <= count($estagio_arr) - 1; $i++) {
	$op_est = null;

	$sel = ($meta_est == $i ? "selected" : null);

	$op_estagio .= "<option value='$i' {$sel}>{$estagio_arr[$i]}</option>";
}

$op_folha = null;

for ($i = 10; $i <= 200; $i+=10) {
	$sel_folha = null;

	$sel = ($meta_folha == $i ? "selected" : null);

	$op_folha .= "<option value='$i' {$sel}>{$i}</option>";
}

echo "
	<div id='metaForm'>
	<form id='form_meta' class='form' method='POST' action='index.php'>
		<div class='form-group'>
			<h4 class='text-center'>Definir Meta</h4>
			<input type='hidden' name='csrf_token' value='{$csrf_token}'>
			<div class='form-group row mb-2'>
				<div class='form-group'>
					<label for='meta_dia' class='form-label'>Data</label>
					<input type='date' id='meta_dia' name='meta_dia' class='form-control' value='{$meta_dia}'>
				</div>
			</div>
			<div class='form-group row mb-2'>
				<div class='form-group'>
					<label for='estagio' class='form-label'>Estágio</label>
					<select id='estagio' name='estagio' class='form-select'>{$op_estagio}</select>
				</div>
			</div>
			<div class='form-group row mb-2'>
				<div class='form-group'>
					<label for='folha' class='form-label'>Folha</label>
					<select id='folha' name='folha' class='form-select'>{$op_folha}</select>
				</div>
			</div>
			<div class='form-group'>
				<button type='submit' id='botSubmit' class='btn btn-success' name='CLICOU' value='SALVAR_META' disabled><i class='fa-solid fa-floppy-disk'></i> &nbsp;Salvar</button>
				<button type='button' class='btn btn-primary float-end' onclick=\"window.location.href='/'\"><i class='fa-solid fa-house'></i> &nbsp;Voltar</button>
			</div>
		</div>
	</form>
	</div>
";

?>

<script>
	//	ativa/desativa o submit (se tem ou não alteração nos campos)
	$('#form_meta').each(function(){
		$(this).data('serialized', $(this).serialize());
	}).on('change input', function(){
		$('#botSubmit').attr('disabled', $(this).serialize() == $(this).data('serialized'));
	});	
</script>