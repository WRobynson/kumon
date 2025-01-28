
<?php
	/**
	 * Página do formulário de LOGIN
	 */

	//	OPTIONS do SELECT disciplina

	$result = getSelect("SELECT * FROM `t_disciplinas`");

	$op_disciplnas = null;

	foreach ($result as $linha) {
		$disc_id = $linha["id"];
		$disc_n = $linha["nome"];

		$op_disciplnas .= "<option value='{$disc_id}'>{$disc_n}</option>";
	}
?>

<div id='div_login_ext'>
	<div id='div_login'>
		<form id='f_login' class='form' action='' method='POST' accept-charset='utf-8'  class='form-signin needs-validation' novalidate>
			<img id='logo_login' class='mx-auto d-block' src='logo/logo192.png' class='img-fluid'/>
			<?php echo $Msg; ?>
			<div class='input-group mb-3'>
				<span class='input-group-text'><i class='fa-solid fa-user'></i></span>
				<input type='text' id='usuario' name='usuario' class='form-control' placeholder='Usuário' value='' required autofocus>
			</div>
			
			<div class='input-group mb-3'>
				<span class='input-group-text'><i class='fa-solid fa-lock'></i></span>
				<input type='password' id='senha' name='senha' class='form-control' placeholder='Senha' value='' required>
			</div>
			
			<div class='text-center'>
				<button id='btnLogin' class='btn btn-md btn-primary btn-block'><i class='fa-solid fa-right-to-bracket'></i>&emsp;Entrar</button>
				<input type='hidden' name='CLICOU' value='ENTRAR'>
				<input type='hidden' name='page' value='home'>
			</div>
		</form>
	</div>
</div>
