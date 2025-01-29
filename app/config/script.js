var DEBUG = false;
// DEBUG = true;

// Verifica se o navegador suporta Service Workers
if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register('/service-worker.js')
		.then(function(registration) {
			console.log('Service Worker registrado com sucesso.');
		})
		.catch(function(error) {
			console.log('Erro ao registrar o Service Worker:', error);
		});
}

// Converte a chave pública VAPID de Base64 para Uint8Array
function urlBase64ToUint8Array(base64String) {
	const padding = '='.repeat((4 - base64String.length % 4) % 4);
	const base64 = (base64String + padding)
		.replace(/\-/g, '+')
		.replace(/\_/g, '/');
	const rawData = atob(base64);
	const outputArray = new Uint8Array(rawData.length);

	for (let i = 0; i < rawData.length; i++) {
		outputArray[i] = rawData.charCodeAt(i);
	}
	return outputArray;
}



// Solicita permissão para notificações
if ('Notification' in window && 'serviceWorker' in navigator) {
	Notification.requestPermission().then(function(permission) {
		if (permission === 'granted') {
			console.log('Permissão concedida para notificações');
			
			// Inscrição no PushManager para receber notificações
			navigator.serviceWorker.ready.then(function(registration) {
				registration.pushManager.subscribe({
					userVisibleOnly: true, // A notificação será visível ao usuário
					applicationServerKey: urlBase64ToUint8Array('SUA_CHAVE_PUBLICA_VAPID')
				}).then(function(subscription) {
					console.log('Push Subscription:', subscription);
					
					// Aqui você pode enviar a inscrição ao servidor
					fetch('/save-push-subscription', {
						method: 'POST',
						body: JSON.stringify(subscription),
						headers: {
							'Content-Type': 'application/json'
						}
					}).then(response => {
						if (!response.ok) {
							throw new Error('Falha ao salvar a inscrição');
						}
						console.log('Assinatura salva no servidor!');
					}).catch(err => {
						console.log('Erro ao salvar assinatura no servidor:', err);
					});
				}).catch(function(error) {
					console.log('Erro ao inscrever no Push:', error);
				});
			});
		} else {
			console.log('Permissão negada para notificações');
		}
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

