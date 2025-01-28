
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

		$op_disciplnas .= "<button class='btn btn-md btn-primary btn-block' name='disciplina' value='{$disc_id}'><i class='fa-solid fa-book'></i>&emsp;{$disc_n}</button>";
	}

	$op_disciplnas .= "</div>";
?>

<div id='div_login_ext'>
	<div id='div_login'>
		<form id='f_login' class='form' action='' method='POST' accept-charset='utf-8'  class='form-signin needs-validation' novalidate>
			<img id='logo_login' class='mx-auto d-block' src='logo/logo192.png' class='img-fluid'/>
			<?php echo $op_disciplnas;?>
			
			<div class='text-center'>
				<input type='hidden' name='CLICOU' value='DISCIPLINA'>
				<input type='hidden' name='page' value='home'>
			</div>
		</form>
	</div>
</div>
