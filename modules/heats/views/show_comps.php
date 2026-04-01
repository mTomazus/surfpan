<style>
  .comps-wrap { max-width: 1000px; margin: 0 auto; padding: 0 1rem 4rem; }

  .comps-search {
    max-width: 420px;
    margin: 1.5rem auto;
    position: relative;
  }
  .comps-search input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    background: var(--bg);
    color: var(--text);
    font-size: 0.95rem;
    box-sizing: border-box;
    text-align: center;
  }
  .comps-search .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.45;
    pointer-events: none;
  }

  .comps-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; backdrop-filter: blur(10px);}
  .comps-table th {
    text-align: left;
    padding: 0.5rem 0.75rem;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-light);
    border-bottom: 1px solid var(--border);
    background: var(--primary-20);
    white-space: nowrap;
  }
  .comps-table td {
    padding: 0.65rem 0.75rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }
  .comps-table tr:last-child td { border-bottom: none; }
  .comps-table tr:hover td { background: var(--bg-hover, rgba(255,255,255,.04)); }
  .comps-table tr td:nth-child(3) { color: var(--ok); }

  .comp-title { font-family: 'Lato', sans-serif; font-weight: 600; font-size: 0.9rem; }
  .comp-sub   { font-family: 'Lato', sans-serif; font-size: 0.75rem; color: var(--text-light); margin-top: 1px; }

  .status-pill {
    display: inline-block; font-size: 0.7rem; padding: 2px 10px;
    border-radius: 20px; font-weight: 600; text-transform: uppercase; white-space: nowrap;
  }
  .status-finished  { background: #1a5c2a; color: #6effa0; }
  .status-running   { background: #5c3a00; color: #ffb84d; }
  .status-open      { background: #0d2a5c; color: #7fc3ff; }
  .status-created, .status-closed, .status-generated { background: var(--border); color: var(--text-light); }

  .btn-action {
    display: inline-block;
    padding: 0.3rem 0.85rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    font-size: 0.78rem;
    text-decoration: none;
    color: inherit;
    white-space: nowrap;
    cursor: pointer;
    background: transparent;
    transition: background .15s, border-color .15s;
  }
  .btn-action:hover { background: var(--primary-20); border-color: var(--primary-60); }
  .btn-action.live  { border-color: #ffb84d; color: #ffb84d; }
  .btn-action.live:hover { background: rgba(255,184,77,.1); }

  .comps-empty { text-align: center; padding: 3rem; color: var(--text-light); display: none; }

  main:before {
    content: "";
    position: absolute;
    inset: 0;
    background-image: url("images/abstract.svg");
    background-size: cover;
    background-position: center;
    opacity: 0.85;
    z-index: -1;
  }
</style>

<h2>Find <span>Event</span></h2>
<p>Browse all competitions and organisations on Surf Panel</p>

<div class="comps-wrap">
  <div class="comps-search field" style="background: var(--bg);">
    <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
    <input type="search" id="searchCompetition" placeholder="Search by competition or organiser…" oninput="searchCompetitions()">
  </div>

  <div style="overflow-x:auto">
    <table class="comps-table" id="comps-table" style="display:none">
      <thead>
        <tr>
          <th>Status</th>
          <th>Name</th>
          <th>Organiser / Country</th>
          <th style="text-align:center">Type</th>
          <th style="text-align:center">Action</th>
        </tr>
      </thead>
      <tbody id="comps-tbody"></tbody>
    </table>
  </div>

  <p class="comps-empty" id="comps-empty">No results match your search.</p>
</div>

<script>
  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  function buttonsFor(x) {
    const id     = encodeURIComponent(String(x.id || ''));
    const slug   = encodeURIComponent(String(x.slug || ''));
    const status = String(x.status || '').toLowerCase();

    if (x.type !== 'competition') {
      return `<a class="btn-action" href="organizations/show/${slug}">View</a>`;
    }
    switch (status) {
      case 'running':
        return `<button class="btn-action live" onclick="goLive('${id}')">Live</button>`;
      case 'finished':
        return `
          <button class="btn-action" onclick="viewResults('${id}')">Results</button>
          <button class="btn-action" style="margin-left:4px" onclick="openCompetition('${id}')">Draw</button>`;
      default:
        return `<button class="btn-action" mx-get="users/competition/${id}" mx-build-modal="competitionModal">Action</button>`;
    }
  }

  let __t;
  function searchCompetitions() {
    clearTimeout(__t);
    __t = setTimeout(async () => {
      const q     = document.getElementById('searchCompetition').value.trim();
      const tbody = document.getElementById('comps-tbody');
      const table = document.getElementById('comps-table');
      const empty = document.getElementById('comps-empty');

      if (!q || q.length < 2) {
        table.style.display = 'none';
        empty.style.display = 'none';
        tbody.innerHTML = '';
        return;
      }

      try {
        const res  = await fetch(`/surfpan/results/search?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
        const text = await res.text();
        if (!res.ok) throw new Error('Network error');
        const data = JSON.parse(text);

        if (!Array.isArray(data) || data.length === 0) {
          table.style.display = 'none';
          empty.style.display = 'block';
          tbody.innerHTML = '';
          return;
        }

        tbody.innerHTML = data.map(x => {
          const status = String(x.status || '').toLowerCase();
          const typeLabel = x.type === 'competition' ? 'Competition' : 'Organiser';
          return `
            <tr>
              <td><span class="status-pill status-${escapeHtml(status)}">${escapeHtml(status)}</span></td>
              <td>
                <div class="comp-title">${escapeHtml(String((x.title || '')).toUpperCase())} ${escapeHtml(x.year || '')}</div>
              </td>
              <td>${escapeHtml(x.organiser || x.country || '—')}</td>
              <td style="text-align:center"><span class="comp-sub">${escapeHtml(typeLabel)}</span></td>
              <td style="text-align:center">${buttonsFor(x)}</td>
            </tr>`;
        }).join('');

        table.style.display = '';
        empty.style.display = 'none';

      } catch (e) {
        table.style.display = 'none';
        empty.style.display = 'block';
        empty.textContent   = 'Search failed. Please try again.';
        console.error(e);
      }
    }, 250);
  }

  function goLive(id)          { if (id) location.href = `heats/show_heats_draw/${id}`; }
  function viewResults(id)     { if (id) location.href = `results/show/${id}`; }
  function openCompetition(id) { if (id) location.href = `heats/show_heats_draw/${id}`; }
</script>
