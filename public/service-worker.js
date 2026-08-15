// Adhook Employee Portal — Web Push service worker.
// Kept intentionally minimal: display the pushed notification and route a
// click to the relevant page. No caching/offline logic is implemented.

self.addEventListener('push', function (event) {
  let data = { title: 'Adhook Employee Portal', body: '', url: '/dashboard' };
  try {
    if (event.data) {
      data = Object.assign(data, event.data.json());
    }
  } catch (e) {
    data.body = event.data ? event.data.text() : '';
  }

  const options = {
    body: data.body,
    icon: data.icon || '/assets/img/icon-192.png',
    badge: '/assets/img/icon-192.png',
    data: { url: data.url || '/dashboard' },
  };

  event.waitUntil(self.registration.showNotification(data.title, options));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.url) || '/dashboard';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (const client of clientList) {
        if (client.url.includes(url) && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});
