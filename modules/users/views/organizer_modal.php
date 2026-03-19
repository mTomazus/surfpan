<style>
  /* Header */
  .head{display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-bottom:1px solid color-mix(in oklab, var(--text), transparent 90%);}
  .title{display:flex; align-items:center; gap:12px;}
  .avatar{width:56px; height:56px; border-radius:14px; background:linear-gradient(135deg,#5cc8ff,#a58cff); display:grid; place-items:center; color:white; font-weight:800; font-size:20px; overflow:hidden}
  .avatar img{width:100%; height:100%; object-fit:cover; display:block}
  .name{font-weight:800; font-size:1.1rem}
  .subtle{font-size:.9rem}

  .xbtn{all:unset; cursor:pointer; width:40px; height:40px; display:grid; place-items:center; border-radius:12px;}
  .xbtn:hover{background:color-mix(in oklab, var(--text), transparent 94%)}

  /* Body layout */
  .body{padding-top:16px;}
  @media (max-width: 920px){ .body{grid-template-columns: 1fr;} }

  .card{background:color-mix(in oklab, var(--card), white 2%); border:1px solid color-mix(in oklab, var(--text), transparent 90%); border-radius:16px; padding:14px;}
  .section-title{margin:0 0 8px; font-size:1rem}

  .kv{display:grid; gap:8px}
  .row{display:flex; align-items:center; gap:8px}
  .row span{min-width:110px; font-size:.9rem}

  .btn{display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:999px; border:1px solid color-mix(in oklab, var(--text), transparent 85%); background:transparent; color:var(--text); cursor:pointer}
  .btn:hover{border-color:color-mix(in oklab, var(--text), transparent 70%)}
  .btn.primary{background:var(--primary); color:#062033; border-color:transparent; box-shadow:0 10px 24px color-mix(in oklab, var(--primary), transparent 70%)}
  .btn.live{background:var(--live); color:#261603; border-color:transparent}
  .btn.view{color:#cbd5e1; border-color:#475569} /* neutral view button */

  .chips{display:flex; flex-wrap:wrap; gap:8px}
  .chip{display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border:1px solid color-mix(in oklab, var(--text), transparent 85%); border-radius:999px; font-size:.85rem; cursor:pointer}
  .chip[aria-pressed="true"]{background:color-mix(in oklab, var(--primary), transparent 85%); border-color:color-mix(in oklab, var(--primary), transparent 65%)}

  table{width:100%; border-collapse:collapse}
  th,td{padding:10px 12px; border-bottom:1px solid color-mix(in oklab, var(--text), transparent 90%); text-align:left}
  th{color:var(--muted); font-size:.86rem}
  tbody tr:last-child td{border-bottom:none}

  .actions{display:flex; gap:8px; flex-wrap:wrap}
</style>

<section class="card pad span-12" aria-labelledby="organizer-title">
  <div class="head">
    <div class="title">
      <div class="avatar" id="orgAvatar" data-name="<?= out($organizer->organization ?? $organizer->name ?? ''); ?>">
        <?php if (!empty($organizer->logo)): ?>
        <img src="<?= out($organizer->logo); ?>" alt="Logo" />
        <?php else: ?>
        <span id="orgInitials"></span>
        <?php endif; ?>
      </div>
      <div>
          <div class="name" id="org-title"><?= out($organizer->organization ?? $organizer->name ?? 'Organizer'); ?></div>
          <div class="subtle">
            <?= out($organizer->location ?? ''); ?><?= (!empty($organizer->city) && !empty($organizer->country)) ? ' · ' : '' ?><?= out($organizer->country ?? ''); ?>
          </div>
      </div>
    </div>
    <button class="xbtn" onclick="closeModal()" aria-label="Close">✕</button>
  </div>

  <div class="body">
    <!-- LEFT: About organizer -->
    <div class="card">
      <h3 class="section-title">About</h3>
      <div class="kv">
        <?php if (!empty($organizer->email)): ?>
          <div class="row">
            <span>Email</span>
            <a href="mailto:<?= out($organizer->email); ?>"><?= out($organizer->email); ?></a>
          </div>
        <?php endif; ?>
        <?php if (!empty($organizer->phone)): ?>
          <div class="row">
            <span>Phone</span>
            <a href="tel:<?= out($organizer->phone); ?>"><?= out($organizer->phone); ?></a>
          </div>
        <?php endif; ?>
        <?php if (!empty($organizer->website)): ?>
          <div class="row">
            <span>Website</span>
            <a href="<?= out($organizer->website); ?>" target="_blank" rel="noopener">Visit</a>
          </div>
        <?php endif; ?>
      </div>
      <div class="actions" style="margin-top:12px">
        <button class="btn" onclick="window.location.href='<?= BASE_URL ?>organizers/view/<?= (int)($organizer->id ?? 0) ?>'">Full profile</button>
        <button class="btn" onclick="window.location.href='<?= BASE_URL ?>competitions?organizer_id=<?= (int)($organizer->id ?? 0) ?>'">All competitions</button>
        <?php if (!empty($organizer->email)): ?>
          <button class="btn" onclick="window.location.href='mailto:<?= out($organizer->email); ?>'">Contact</button>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIGHT: Competitions with filter chips -->
    <div class="card">

      <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:8px;">
        <h3 class="section-title" style="margin:0">Competitions</h3>
        <div class="chips" role="tablist" aria-label="Filter competitions">
          <button class="chip" role="tab" aria-selected="true" aria-pressed="true" data-tab="running" onclick="showTab('running')">Running</button>
          <button class="chip" role="tab" aria-selected="false" aria-pressed="false" data-tab="upcoming" onclick="showTab('upcoming')">Upcoming</button>
          <button class="chip" role="tab" aria-selected="false" aria-pressed="false" data-tab="past" onclick="showTab('past')">Past</button>
        </div>
      </div>

      <!-- Running -->
      <div class="tab" id="tab-running">
        <?php if (!empty($competitions_running)): ?>
        <table>
          <thead><tr><th>Name</th><th>When</th><th>Location</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($competitions_running as $c): ?>
            <tr>
              <td><?= out($c['name']); ?><?= !empty($c['year']) ? ' · '.out($c['year']) : '' ?></td>
              <td><?= out($c['start_date'] ?? '') ?><?= !empty($c['end_date']) ? ' – '.out($c['end_date']) : '' ?></td>
              <td><?= out($c['location'] ?? '') ?></td>
              <td><span class="btn live" style="pointer-events:none">LIVE</span></td>
              <td style="text-align:right">
                <button class="btn live" onclick="window.location.href='<?= BASE_URL ?>competitions_heats/show_heats_draw/<?= (int)$c['id'] ?>'">Watch</button>
                <button class="btn view" onclick="window.location.href='<?= BASE_URL ?>competitions/view/<?= (int)$c['id'] ?>'">View</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <div class="subtle">No running competitions.</div>
        <?php endif; ?>
      </div>

      <!-- Upcoming -->
      <div class="tab" id="tab-upcoming" hidden>
        <?php if (!empty($grouped_comps['open'])): ?>
        <table>
          <thead><tr><th>Name</th><th>When</th><th>Location</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($grouped_comps['open'] as $c): ?>
            <tr>
              <td><?= out($c->name); ?><?= !empty($c->year) ? ' ' . out($c->year) : '' ?></td>
              <td><?= out($c->start_date ?? '') ?><?= !empty($c->end_date) ? ' – '.out($c->end_date) : '' ?></td>
              <td><?= out($c->location ?? '') ?></td>
              <td><span class="subtle"><?= out($c->status ?? '') ?></span></td>
              <td style="text-align:right">
                <button class="btn primary" onclick="window.location.href='<?= BASE_URL ?>competitions/view/<?= (int)$c['id'] ?>'">Details</button>
                <button class="btn" onclick="window.location.href='<?= BASE_URL ?>competitions/register/<?= (int)$c['id'] ?>'">Register</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <div class="subtle">No upcoming competitions.</div>
        <?php endif; ?>
      </div>

      <!-- Past -->
      <div class="tab" id="tab-past" hidden>
        <?php if (!empty($competitions_past)): ?>
        <table>
          <thead><tr><th>Name</th><th>When</th><th>Location</th><th>Result</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($competitions_past as $c): ?>
            <tr>
              <td><?= out($c['name']); ?><?= !empty($c['year']) ? ' · '.out($c['year']) : '' ?></td>
              <td><?= out($c['start_date'] ?? '') ?><?= !empty($c['end_date']) ? ' – '.out($c['end_date']) : '' ?></td>
              <td><?= out($c['location'] ?? '') ?></td>
              <td><span class="subtle">Finished</span></td>
              <td style="text-align:right">
                <button class="btn" onclick="window.location.href='<?= BASE_URL ?>competitions/results/<?= (int)$c['id'] ?>'">Results</button>
                <button class="btn" onclick="window.location.href='<?= BASE_URL ?>competitions/view/<?= (int)$c['id'] ?>'">Summary</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <div class="subtle">No past competitions.</div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<script>
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeOrganizerModal(); });
  document.getElementById('organizerModal').addEventListener('click', (e)=>{
    if(e.target.id==='organizerModal') closeModal();
  });

  // Avatar initials fallback
  (function(){
    const wrap = document.getElementById('orgAvatar');
    if(!wrap) return;
    const hasImg = wrap.querySelector('img');
    if(hasImg) return;
    const target = document.getElementById('orgInitials');
    const name = (wrap.getAttribute('data-name')||'').trim();
    const initials = name.split(/\s+/).slice(0,2).map(w=>w[0]||'').join('').toUpperCase();
    target.textContent = initials || 'OR';
  })();
</script>