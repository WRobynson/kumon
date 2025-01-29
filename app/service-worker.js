self.addEventListener('install', (event) => {
	console.log('Service Worker: Instalado.');
	event.waitUntil(
		caches.open('meu-app-cache').then((cache) => {
			console.log('Service Worker: Cache criado.');
			return cache.addAll([
				'/',
				'/config/script.js',
				'/config/reset.css',
				'/config/estilos.css',
				'/logo/logo192.png',
				'/logo/logo512.png',
				'/logo/favicon.ico',
			]).catch((error) => {
				console.error('Erro ao adicionar ao cache:', error);
			});
		})
	);
});

// Evento de Push
self.addEventListener('push', function(event) {
	const notificationData = event.data.json();  // Use .json() se a notificação for enviada como JSON
	const title = notificationData.title || 'Nova Notificação'; // Título da notificação
	const options = {
		body: notificationData.body || event.data.text(), // Corpo da notificação
		icon: notificationData.icon || '/logo/logo192.png',  // Ícone da notificação
		badge: notificationData.badge || '/logo/logo192.png', // Badge da notificação
	};

	event.waitUntil(
		self.registration.showNotification(title, options).catch((error) => {
			console.error('Erro ao exibir notificação:', error);
		})
	);
});

// Evento de clique na notificação
self.addEventListener('notificationclick', function(event) {
	event.notification.close();
	// Ao clicar, você pode redirecionar para uma página específica
	event.waitUntil(
		clients.openWindow('/dashboard') // Altere conforme necessário
	);
});
