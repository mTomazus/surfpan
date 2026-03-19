<div id="results">
    <h2 class="text-center pad" style="margin-top:100px">COMPETITIONS</h2>
    <div class="justify-center container">

        <section class="card pad span-12" aria-labelledby="search-title">
            <div class="section-head">
                <h3 id="search-title">Search Competitions or Organisers</h3>
            </div>
            <div class="controls">
                <label class="field">
                <input type="search" id="searchCompetition" placeholder="Type competition or organiser name…" oninput="searchCompetitions()" />
                </label>
                <button class="btn" onclick="searchCompetitions()">Search</button>
            </div>
            <div id="searchResults" class="list" style="display:none"></div>
        </section>

    </div>
</div>

<script>
    // Quick selector
    const $ = sel => document.querySelector(sel);

    let __t;
    function searchCompetitions() {
      clearTimeout(__t);
      __t = setTimeout(async () => {
        const q = $('#searchCompetition').value.trim();
        const box = document.getElementById('searchResults');

        if (!q || q.length < 2) { box.style.display = 'none'; box.innerHTML = ''; return; }

        try {
          const res = await fetch(`/surfpan/results/search?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json' }
          });

          const text = await res.text();
          if (!res.ok) { console.error('Search failed', res.status, text); throw new Error('Network error'); }

          let data;
          try { data = JSON.parse(text); } catch(e){ console.error('Bad JSON', text); throw e; }

          if (!Array.isArray(data) || data.length === 0) {
            box.style.display = 'block';
            box.innerHTML = '<div class="subtle">No results.</div>';
            return;
          }

          box.style.display = 'grid';
          box.innerHTML = data.map(x => `
            <div class="card pad" style="padding:12px">
              <div class="list-item" style="align-items: center; justify-content: space-between;">
                <span class="badge chip status-${escapeHtml(String(x.status || ''))}">${escapeHtml(String(x.status || '').toUpperCase())}</span>
                <div>
                  <h3>${escapeHtml(String((x.title).toUpperCase()))} ${escapeHtml(x.year || '')}</h3>
                  <h4 class="subtle">• ${x.type === 'competition' ? 'Competition' : 'Organiser'} •</h4>
                  <h4 class="subtle">${escapeHtml(x.organiser || x.country)} </h4>
                </div>
                <div class="controls">
                  ${buttonsFor(x)}
                </div>
              </div>
            </div>
          `).join('');

        } catch (e) {
          box.style.display = 'block';
          box.innerHTML = `<div class="subtle">Search failed. Please try again.</div>`;
          console.error(e);
        }
      }, 250);
    }

    function escapeHtml(s='') {
      return String(s).replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[m]));
    }

    function buttonsFor(x) {
      const id = encodeURIComponent(String(x.id || ''));
      const slug = encodeURIComponent(String(x.slug || ''));
      if (x.type !== 'competition') {
        return `<a class="btn view" href="organizations/show/${slug}">View</a>`;
      }

      const status = String(x.status || '').toLowerCase(); // normalize
      switch (status) {
        case 'running':
          return `
            <button class="btn running" style="margin-left:8px" onclick="goLive('${id}')">Live</button>
          `;
        case 'finished':
          return `
            <button class="btn finished" style="margin-left:8px" onclick="viewResults('${id}')">Results</button>
            <button class="btn finished" style="margin-left:8px" onclick="openCompetition('${id}')">Draw</button>
          `;
        default: // open/scheduled/other
          return `<button class="btn" style="margin-left:8px" mx-get="users/competition/${id}" mx-build-modal="competitionModal">Action</button>`;
      }
    }

    // optional: endpoints these buttons go to
    function goLive(id)       { if (!id) return; location.href = `heats/show_heats_draw/${id}`; }
    function viewResults(id)  { if (!id) return; location.href = `results/show/${id}`; }
    function openCompetition(id){ if (!id) return; location.href = `heats/show_heats_draw/${id}`; }
    function openOrganiser(id){ if (!id) return; location.href = `organisers/view/${id}`; }
</script>
<style>
  .controls {
    display: flex;
    gap: 8px;
  }
  .list-item {
    display: grid;
    grid-template-columns: auto 1fr auto;
  }
  .chip {
    margin-inline:  1rem;
    display:grid;
    place-items: center;
    width: 150px;
  }
  .list {
    margin-top:2rem;
  }
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