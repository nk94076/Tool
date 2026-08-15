<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 fw-bold mb-0" id="calTitle"></h2>
  <div class="btn-group">
    <button class="btn btn-outline-secondary btn-sm" id="calPrev"><i class="bi bi-chevron-left"></i></button>
    <button class="btn btn-outline-secondary btn-sm" id="calToday">Today</button>
    <button class="btn btn-outline-secondary btn-sm" id="calNext"><i class="bi bi-chevron-right"></i></button>
  </div>
</div>

<div class="d-flex gap-3 mb-3 flex-wrap small">
  <span><span class="badge rounded-circle p-1" style="background:#5b3df6">&nbsp;</span> Birthday</span>
  <span><span class="badge rounded-circle p-1" style="background:#16a34a">&nbsp;</span> Anniversary</span>
  <span><span class="badge rounded-circle p-1" style="background:#d97706">&nbsp;</span> Event</span>
  <span><span class="badge rounded-circle p-1" style="background:#dc2626">&nbsp;</span> Secret Santa</span>
</div>

<div class="card">
  <div class="card-body p-2 p-md-3">
    <div id="calGrid" class="calendar-grid"></div>
  </div>
</div>

<style>
  .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
  .cal-cell { min-height: 70px; border: 1px solid var(--border); border-radius: 8px; padding: 4px; font-size: .72rem; }
  .cal-cell .day-num { font-weight: 600; font-size: .75rem; }
  .cal-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 2px; }
  .cal-head { text-align:center; font-weight:600; font-size:.72rem; color:#7b8194; padding:4px 0; }
  @media (max-width: 575.98px) { .cal-cell { min-height: 52px; font-size: .62rem; } }
</style>

<script>
(function () {
  const colors = { birthday: '#5b3df6', anniversary: '#16a34a', event: '#d97706', secret_santa: '#dc2626' };
  let current = new Date();
  let events = [];

  function load() {
    adhookFetch('/calendar/feed').then(r => r.json()).then(data => {
      events = data.events || [];
      render();
    });
  }

  function render() {
    const grid = document.getElementById('calGrid');
    const title = document.getElementById('calTitle');
    const year = current.getFullYear(), month = current.getMonth();
    title.textContent = current.toLocaleString('default', { month: 'long', year: 'numeric' });

    const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    let html = days.map(d => `<div class="cal-head">${d}</div>`).join('');

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    for (let i = 0; i < firstDay; i++) html += '<div></div>';

    for (let d = 1; d <= daysInMonth; d++) {
      const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const dayEvents = events.filter(e => e.date === dateStr);
      const dots = dayEvents.slice(0, 4).map(e => `<span class="cal-dot" style="background:${colors[e.type] || '#999'}" title="${e.title.replace(/"/g,'')}"></span>`).join('');
      html += `<div class="cal-cell"><div class="day-num">${d}</div><div>${dots}</div></div>`;
    }

    grid.innerHTML = html;
  }

  document.getElementById('calPrev').addEventListener('click', () => { current.setMonth(current.getMonth() - 1); render(); });
  document.getElementById('calNext').addEventListener('click', () => { current.setMonth(current.getMonth() + 1); render(); });
  document.getElementById('calToday').addEventListener('click', () => { current = new Date(); render(); });

  load();
})();
</script>
