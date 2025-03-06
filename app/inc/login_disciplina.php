<?php
	/**
	 * Página do formulário de LOGIN
	 */

	//	OPTIONS disciplina

	//	quais disciplinas o usuário faz?
	
	$result = getSelect("SELECT * FROM `v_usu_vs_disciplina` WHERE `usu_id` = {$logado_id}");

	$op_disciplnas = "<div class='d-block mx-auto disc_op'>";

	foreach ($result as $linha) {
		$disc_id = $linha["disc_id"];
		$disc_n = $linha["disc_n"];
		$disc_a = $linha["disc_a"];

		$op_disciplnas .= "<button class='btn btn-md btn-primary btn-block mx-auto d-block mb-1' name='disciplina' value='{$disc_id}'><i class='fa-solid fa-book'></i>&emsp;{$disc_n}</button>";
	}

	$op_disciplnas .= "<button class='mx-auto d-block mt-2 btn btn-md btn-success btn-block' onClick=\"$('#botClicou').val(''); $('#page').val('add_disciplina')\"><i class='fa-solid fa-plus'></i>&emsp;Nova</button>";

	$op_disciplnas .= "</div>";

	//	mensagem para o usuário
	echo $Msg;
?>

<div id='div_login_ext'>
	<div id='div_login'>
		<form id='f_login' class='form' action='' method='POST' accept-charset='utf-8'  class='form-signin needs-validation' novalidate>
			<img id='logo_login' class='mx-auto d-block' src='logo/logo192.png' class='img-fluid'/>
			<?php echo $op_disciplnas;?>
			
			<input type='hidden' id='botClicou' name='CLICOU' value='DISCIPLINA'>
			<input type='hidden' id='page' name='page' value='home'>
		</form>
	</div>
</div>
