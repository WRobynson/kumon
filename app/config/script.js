////////////////////////////////////////////////////////////////////////////////
/* TRIM */
////////////////////////////////////////////////////////////////////////////////
// serve para excluir os espacos em excesso da string

String.prototype.trim = function() {
	var trim1 = this.replace(/^\s+|\s+$/g, "");  // elimina espacos antes e depois
	return trim1.replace(/\s+/g, " "); // elimina espacos no meio
}
////////////////////////////////////////////////////////////////////////////////

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

