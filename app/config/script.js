var DEBUG = false;
// DEBUG = true;

if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register('/service-worker.js').then(() => {
	//console.log('Service Worker registrado com sucesso.');
	}).catch((error) => {
	console.log('Erro ao registrar o Service Worker:', error);
	});
}

////////////////////////////////////////////////////////////////////////////////
/* TRIM */
////////////////////////////////////////////////////////////////////////////////
// serve para excluir os espacos em excesso da string

String.prototype.trim = function() {
	var trim1 = this.replace(/^\s+|\s+$/g, "");  // elimina espacos antes e depois
	return trim1.replace(/\s+/g, " "); // elimina espacos no meio
}
////////////////////////////////////////////////////////////////////////////////

function Eh_par(numero){
   	var resto = numero % 2
   	if (resto == 0)
      	 return true
   	else
      	 return false
}

/*
	Para ativar os ToolTips
	Não funciona para elementos oriundos de AJAX. Para estes caso, deve ser carregado no JS que chama o AJAX.
*/
$(function () {
	$('[data-bs-toggle="tooltip"]').tooltip()
})

/*
	desativa o SUBMIT com o pressionar de ENTER
*/
$('form input:not([type="submit"])').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        return false;
    }
});

