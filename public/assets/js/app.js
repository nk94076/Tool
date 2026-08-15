(function () {
  'use strict';

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  window.adhookFetch = function (url, options = {}) {
    options.headers = Object.assign({ 'X-CSRF-Token': csrfToken }, options.headers || {});
    return fetch(url, options);
  };

  function timeAgo(iso) {
    const diff = (Date.now() - new Date(iso.replace(' ', 'T'))) / 1000;
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
  }

  function renderNotifications(data) {
    const list = document.getElementById('notifList');
    const badge = document.getElementById('notifBadge');
    if (!list) return;

    if (data.unread_count > 0) {
      badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
      badge.classList.remove('d-none');
    } else {
      badge.classList.add('d-none');
    }

    if (!data.notifications.length) {
      list.innerHTML = '<div class="empty-state py-4"><i class="bi bi-bell-slash"></i><p class="small mb-0 mt-2">No notifications yet</p></div>';
      return;
    }

    list.innerHTML = data.notifications.map(n => `
      <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
        <div class="fw-semibold">${escapeHtml(n.title)}</div>
        ${n.body ? `<div class="text-muted">${escapeHtml(n.body)}</div>` : ''}
        <div class="text-muted" style="font-size:.72rem">${timeAgo(n.created_at)}</div>
      </div>
    `).join('');
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function loadNotifications() {
    if (!document.getElementById('notifList')) return;
    adhookFetch('/api/notifications').then(r => r.json()).then(renderNotifications).catch(() => {});
  }

  document.getElementById('markAllReadBtn')?.addEventListener('click', function () {
    adhookFetch('/api/notifications/read-all', { method: 'POST' }).then(loadNotifications);
  });

  document.getElementById('notifBell')?.addEventListener('shown.bs.dropdown', loadNotifications);

  loadNotifications();
  setInterval(loadNotifications, 60000);
})();
