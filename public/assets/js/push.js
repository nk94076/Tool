(function () {
  'use strict';

  if (!('serviceWorker' in navigator)) {
    return; // Unsupported browser — app continues to work normally without push.
  }

  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
  }

  navigator.serviceWorker.register('/service-worker.js').catch(() => {
    // Registration failure must never break the rest of the app.
  });

  window.enablePushNotifications = async function () {
    if (!('PushManager' in window)) {
      alert('Push notifications are not supported in this browser.');
      return false;
    }
    try {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
        return false; // App continues to work normally without push.
      }

      const reg = await navigator.serviceWorker.ready;
      const keyRes = await adhookFetch('/api/push/vapid-key');
      const { public_key } = await keyRes.json();
      if (!public_key) {
        console.warn('VAPID public key not configured.');
        return false;
      }

      const subscription = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(public_key),
      });

      await adhookFetch('/api/push/subscribe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(subscription.toJSON()),
      });

      localStorage.setItem('adhook_push_enabled', '1');
      return true;
    } catch (e) {
      console.warn('Push subscription failed:', e);
      return false;
    }
  };

  document.querySelectorAll('[data-enable-push]').forEach(btn => {
    btn.addEventListener('click', async function () {
      const ok = await window.enablePushNotifications();
      btn.textContent = ok ? 'Notifications enabled' : 'Enable failed — try again';
      if (ok) btn.setAttribute('disabled', 'disabled');
    });
  });
})();
