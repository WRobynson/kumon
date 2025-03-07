/**
 * 	Chave para notificação PUSH
 * 	veja README
 */

const VAPID_PUBLIC_KEY="BKRCcoLiM8m603rSzRBk7X__30seOnMRUxhYSBSJHQV-vF5wsn004M14UF_dv2au73l2EeyxW1RkmyAQL1ISCYA";

// Verifica se o navegador suporta Service Workers
if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register('/push/notif-sw.js')
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

let isSubscribed = false; // variável para controlar o status da inscrição

$(document).ready(function() {
	$(document).click(function() {
		if ('Notification' in window && 'serviceWorker' in navigator && !isSubscribed) { // Verifica se a inscrição ainda não foi feita
			Notification.requestPermission().then(function(permission) {
				if (permission === 'granted') {
					console.log('Permissão concedida para notificações');

					// Inscreve-se para as notificações push
					navigator.serviceWorker.ready.then(function(registration) {
						registration.pushManager.subscribe({
							userVisibleOnly: true, // A notificação será visível ao usuário
							applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
						}).then(function(subscription) {
							console.log('Push Subscription:', subscription);

							// Marcar como inscrito para evitar novas inscrições
							isSubscribed = true;
							
							// Enviar a inscrição ao servidor
							fetch('/push/subscription.php', {
								method: 'POST',
								body: JSON.stringify(subscription),
								headers: {
									'Content-Type': 'application/json'
								}
							}).then(response => {
								if (!response.ok) {
									throw new Error('Falha ao salvar a inscrição');
								}
								console.log('Assinatura enviada ao servidor!');
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
			}).catch(function(error) {
				console.log('Erro ao solicitar permissão:', error);
			});
		}
	});
});
