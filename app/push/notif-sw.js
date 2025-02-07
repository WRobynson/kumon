self.addEventListener("push", (event) => {
	const notification = event.data.json();
	// {"title":"Hi" , "body":"something amazing!" , "url":"./?message=123"}
	event.waitUntil(self.registration.showNotification(notification.title, {
		body: notification.body,
		icon: notification.icon,
		data: {
			notifURL: notification.url
		}
	}));
});
