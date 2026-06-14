<?php
  $user_heats  = Modules::run("users/_get_user_heats");
  $user_comps  = Modules::run("users/_get_user_comps");
  $user_scores = Modules::run("users/_get_user_scores");

  $has_heats = !empty($user_heats);
  // $user_comps rows with no name come from the LEFT JOIN when there are no registrations.
  $has_comps = !empty(array_filter($user_comps, fn($c) => !empty($c['name'])));

  // --- Build competition/division maps from ALL registrations (not just drawn heats) ---
  // This ensures open/scheduled comps appear in the dropdown, not only running ones.
  $comps           = [];  // comp_id => ['id'=>..., 'label'=>...]
  $divisions_by_comp = []; // comp_id => [ ['id'=>..., 'label'=>..., 'jersey'=>null], ... ]
  $comp_status_map = [];  // comp_id => 'open'|'running'|'scheduled'|'finished'

  if ($has_comps) {
    foreach ($user_comps as $row) {
      if (empty($row['name'])) continue;
      $comp_id = (int) $row['id'];
      if (!isset($comps[$comp_id])) {
        $comps[$comp_id] = ['id' => $comp_id, 'label' => trim($row['name'].' '.$row['year'])];
      }
      if (!isset($comp_status_map[$comp_id])) {
        $comp_status_map[$comp_id] = strtolower(trim($row['status']));
      }
      // Avoid duplicate division entries per comp (user may have multiple rows if multi-heat).
      $div_id = (int)($row['division_id'] ?? 0);
      $already = false;
      foreach ($divisions_by_comp[$comp_id] ?? [] as $d) {
        if ($d['id'] === $div_id) { $already = true; break; }
      }
      if (!$already) {
        $divisions_by_comp[$comp_id][] = [
          'id'     => $div_id,
          'label'  => $row['division_name'],
          'jersey' => null,  // overlaid from heat data below
        ];
      }
    }

    // Overlay jersey colour from heat assignments where available.
    foreach ($user_heats as $row) {
      $comp_id = (int) $row['id'];
      $div_id  = (int)($row['division_id'] ?? 0);
      if (!isset($divisions_by_comp[$comp_id])) continue;
      foreach ($divisions_by_comp[$comp_id] as &$div) {
        if ($div['id'] === $div_id && !empty($row['jersey_color'])) {
          $div['jersey'] = $row['jersey_color'];
          break;
        }
      }
      unset($div);
    }
  }

  // --- Heat-specific data for Next Heat card (only when heats are drawn) ---
  if ($has_heats) {
    $timezone = $user_heats[0]['timezone'];

    if ($user_heats[0]['start_time'] != null) {
      $dt = new DateTimeImmutable($user_heats[0]['start_time'], new DateTimeZone('UTC'));
      $start_time = $dt->setTimezone(new DateTimeZone($timezone))->format("Y-m-d H:i:s");
      $start = new DateTime($user_heats[0]['start_time']);
      $end   = new DateTime($user_heats[0]['end_time']);
      $int = $start->diff($end);
      $minutes = ($int->days * 1440) + ($int->h * 60) + $int->i;
      if ($int->invert) $minutes *= -1;
    } else {
      $start_time = null;
      $minutes    = "N/A";
    }

    // Build schedule rows for the Upcoming Heats table and ICS export.
    $schedule_heats = [];
    foreach ($user_heats as $sh) {
      if ($sh['start_time'] === null) continue;
      $sh_tz = $sh['timezone'] ?? 'UTC';
      $sh_start_utc = new DateTimeImmutable($sh['start_time'], new DateTimeZone('UTC'));
      $sh_start_local = $sh_start_utc->setTimezone(new DateTimeZone($sh_tz));
      $sh_end_utc = $sh['end_time']
        ? (new DateTimeImmutable($sh['end_time'], new DateTimeZone('UTC')))
        : $sh_start_utc->modify('+25 minutes');
      $schedule_heats[] = [
        'heat_number'  => (int) $sh['heat_number'],
        'division'     => $sh['division_name'],
        'round'        => $sh['round'],
        'jersey'       => $sh['jersey_color'],
        'location'     => $sh['location'] ?? '',
        'comp_name'    => $sh['name'],
        'display_time' => $sh_start_local->format('H:i'),
        'iso_start'    => $sh_start_utc->format('Y-m-d\TH:i:s\Z'),
        'iso_end'      => $sh_end_utc->format('Y-m-d\TH:i:s\Z'),
        'title'        => trim($sh['name'] . ' — ' . $sh['division_name'] . ' Heat #' . $sh['heat_number']),
      ];
    }
  }

  $first_comp_id = array_key_first($comps) ?? null;
?>

  <div class="grid cards dash<?= $has_comps ? ' has-comps' : '' ?>">

    <!-- ===== Search Competitions / Organisers ===== -->
    <section class="card pad span-12 search-card" aria-labelledby="search-title">
      <div class="section-head">
        <h2 id="search-title">Find Competitions</h2>
        <span class="subtle kbd-hint">Press <kbd>/</kbd> to search</span>
      </div>
      <label class="field search-field">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" id="searchCompetition" placeholder="Type competition or organiser name…" oninput="searchCompetitions()" autocomplete="off" />
      </label>
      <div id="searchResults" class="list" style="margin-top:10px; display:none"></div>
    </section>

    <?php if (!$has_comps): ?>

    <!-- ===== Empty / onboarding state ===== -->
    <section class="card pad span-12 empty-state">
      <div class="empty-state__inner">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--subtle)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        <h2>No registrations yet</h2>
        <p class="subtle">You haven't joined any competitions. Search for an open competition above and register to get started.</p>
        <button class="btn" onclick="document.getElementById('searchCompetition').focus()">Find a competition</button>
      </div>
    </section>

    <?php else: ?>

    <!-- ===== Context / Controls ===== -->
    <section class="card pad span-12" aria-labelledby="ctx-title">
      <div class="section-head">
      <h2 id="ctx-title">My Competitions</h2>
        <div id="statusChips" class="chips">
          <span class="chip status-open"><span class="dot" style="background:var(--chip-open)"></span>Open</span>
          <span class="chip status-running"><span class="dot" style="background:var(--chip-running)"></span>Running</span>
          <span class="chip status-scheduled"><span class="dot" style="background:var(--chip-scheduled)"></span>Scheduled</span>
          <span class="chip status-finished"><span class="dot" style="background:var(--chip-finished)"></span>Finished</span>
        </div>
      </div>
      <div class="controls">
        <label class="field" title="Select competition">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 18a3 3 0 01-3 3H6a3 3 0 01-3-3"/></svg>
          <select id="competitionSelect">
            <?php foreach ($comps as $c): ?>
              <option value="<?= $c['id'] ?>"><?= out($c['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field" title="Your division">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 007.4 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 003 15.4a1.65 1.65 0 00-1.51-1H1a2 2 0 110-4h.09A1.65 1.65 0 003 7.4a1.65 1.65 0 00-.33-1.82l-.06-.06A2 2 0 115.44 2.7l.06.06A1.65 1.65 0 007.32 3a1.65 1.65 0 001-1.51V1a2 2 0 114 0v.09A1.65 1.65 0 0013 3.6a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09A1.65 1.65 0 0019.4 15z"/></svg>
          <select id="divisionSelect">
          </select>
        </label>

        <span class="chip" id="jerseyChip" title="Assigned jersey" style="font-weight:900;">
          Jersey: —
        </span>
      </div>
    </section>

    <!-- ===== Next Heat ===== -->
    <section class="card pad span-7" aria-labelledby="next-heat-title">
      <div class="section-head">
        <h2 id="next-heat-title">Your Next Heat</h2>
        <?php if ($has_heats): ?>
        <span class="chip status-<?= out($user_heats[0]['heat_status']) ?>"><span class="dot" style="background:var(--chip-<?= out($user_heats[0]['heat_status']) ?>)"></span><span id="heatStatus"><?= out($user_heats[0]['heat_status']) ?></span></span>
        <?php endif; ?>
      </div>
      <?php if ($has_heats): ?>
      <div class="next-heat">
        <div class="countdown-hero">
          <span class="pill" id="countdownPill">Starts in</span>
          <span class="count" id="countdown" role="timer">—:—:—</span>
          <div class="heat-meta">
            <span><?= out($user_heats[0]['division_name']) ?></span>
            <span class="heat-meta__sep" aria-hidden="true"></span>
            <span><?= out($user_heats[0]['round']) ?></span>
            <span class="heat-meta__sep" aria-hidden="true"></span>
            <span>Heat #<?= out($user_heats[0]['heat_number']) ?></span>
            <span class="jersey-tag" style="--jersey: var(--jersey-<?= out($user_heats[0]['jersey_color']) ?>)">
              <i class="swatch" aria-hidden="true"></i><?= out($user_heats[0]['jersey_color']) ?> jersey
            </span>
          </div>
        </div>
        <dl class="stat-row">
          <div class="stat"><dt>Call time</dt><dd id="callTime">—</dd></div>
          <div class="stat"><dt>Start</dt><dd id="startTimeLabel"><?= out($start_time) ?></dd></div>
          <div class="stat"><dt>Duration</dt><dd id="durationLabel"><?= out($minutes) ?> min</dd></div>
        </dl>
        <div class="heat-actions">
          <button class="btn" onclick="checkIn()">Check in</button>
          <button class="btn" onclick="goLive(<?= out($user_heats[0]['id']) ?>)">Heat draw</button>
          <a class="btn" href="https://isasurf.org/downloads/ISA_RULEBOOK_April-2025.pdf" target="_blank" rel="noopener">Rulebook</a>
        </div>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--subtle)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <p class="subtle">Heat draw not published yet.<br>Check back once the competition goes live.</p>
      </div>
      <?php endif; ?>
    </section>

    <!-- ===== Scores ===== -->
    <section class="card pad span-5" aria-labelledby="scores-title">
      <div class="section-head">
        <h2 id="scores-title">My Scores</h2>
        <span class="subtle">Best 2 waves count</span>
      </div>

      <?php if (empty($user_scores)): ?>
        <div class="empty-state">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--subtle)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          <p class="subtle">No scores recorded yet.<br>Scores appear once judges submit wave results.</p>
        </div>
      <?php else: ?>
        <?php foreach ($user_scores as $comp_id => $comp_scores): ?>
        <div class="scores-block" data-score-comp="<?= (int)$comp_id ?>">
          <?php foreach ($comp_scores['heats'] as $heat): ?>
          <div class="scores-heat">
            <div class="scores-heat__label">
              <?= out($heat['division_name']) ?> · <?= out($heat['round'] ?: 'Heat') ?> #<?= out($heat['heat_number']) ?>
            </div>
            <div class="table-wrap">
              <table class="table scores-table">
                <thead>
                  <tr>
                    <th>Wave</th>
                    <th class="right">Avg Score</th>
                    <th class="right"><span class="sr-only">Counts toward total</span></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($heat['waves'] as $w): ?>
                  <tr<?= $w['best'] ? ' class="is-best"' : '' ?>>
                    <td>#<?= out($w['wave_number']) ?></td>
                    <td class="right"><?= number_format($w['avg_score'], 2) ?></td>
                    <td class="right best-mark"><?php if ($w['best']): ?><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-label="counted"><path d="M12 2l2.9 6.26L21.5 9.3l-4.75 4.4 1.15 6.55L12 17.1l-5.9 3.15 1.15-6.55L2.5 9.3l6.6-1.04z"/></svg><?php endif; ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Best 2 total</th>
                    <th class="right"><?= number_format($heat['best2_total'], 2) ?></th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <!-- ===== Schedule ===== -->
    <?php if ($has_heats && !empty($schedule_heats)): ?>
    <section class="card pad span-7" aria-labelledby="schedule-title">
      <div class="section-head">
        <h2 id="schedule-title">Upcoming Heats</h2>
        <div class="controls">
          <label class="field"><input type="search" id="scheduleSearch" placeholder="Filter by heat/division…" oninput="filterSchedule()" /></label>
          <button class="btn" onclick="exportSchedule()">Export .ICS</button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="table" aria-describedby="schedule-title" id="scheduleTable">
          <thead>
            <tr>
              <th>Time</th>
              <th>Heat</th>
              <th>Division</th>
              <th>Jersey</th>
              <th class="right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($schedule_heats as $sh): ?>
            <tr>
              <td><?= out($sh['display_time']) ?></td>
              <td>#<?= out($sh['heat_number']) ?></td>
              <td><?= out($sh['division']) ?><?= $sh['round'] ? ' — ' . out($sh['round']) : '' ?></td>
              <td>
                <?php if ($sh['jersey']): ?>
                  <span class="chip jersey" style="background:var(--jersey-<?= out($sh['jersey']) ?>);"><?= strtoupper(out($sh['jersey'])) ?></span>
                <?php else: ?>
                  <span class="subtle">—</span>
                <?php endif; ?>
              </td>
              <td class="right">
                <button class="btn" onclick="addCalendar(<?= json_encode($sh['title']) ?>, <?= json_encode($sh['iso_start']) ?>, <?= json_encode($sh['iso_end']) ?>, <?= json_encode($sh['location']) ?>)">Add to calendar</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

    <!-- ===== Registrations ===== -->
    <section class="card pad span-12" aria-labelledby="regs-title">
      <div class="section-head">
        <h2 id="regs-title">My Registrations</h2>
        <span class="subtle">Manage entries & payments</span>
      </div>
      <div  id="registrations" mx-get="users" mx-select="#registrations" mx-target="#registrations" mx-trigger="activate" class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Competition</th>
              <th>Division</th>
              <th>Status</th>
              <th>Payment</th>
              <th class="right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($user_comps as $comp) { 
              if (!empty($comp['name'])) { ?>
              <tr>
                <td><?= out($comp['name']) ?> <?= out($comp['year']) ?></td>
                <td><?= out($comp['division_name']) ?></td>
                <td><span class="badge <?= out($comp['participation_status']) ?>"><?= out($comp['participation_status']) ?></span></td>
                <td><span class="badge <?= $comp['entry_type'] === 'free entry' ? 'entry-free' : 'entry-paid' ?>"><?= out(strtoupper($comp['entry_type'])) ?></span></td>
                <td class="right actions-cell">
                  <?php
                    $status  = $comp['participation_status'];
                    $is_paid = in_array($status, ['paid', 'confirmed']);

                    // Payment / receipt
                    if ($status === 'pending' && $comp['entry_type'] !== 'free entry') {
                        echo '<button class="btn" mx-get="billings/entry_pay_modal/' . out($comp['id']) . '" mx-build-modal="modalEntryPay">Pay now</button>';
                    } elseif ($is_paid && !empty($comp['billing_charge_id'])) {
                        echo '<a href="' . BASE_URL . 'billings/receipt/' . out($comp['billing_charge_id']) . '" class="btn" style="text-decoration:none">Receipt</a>';
                    }

                    // Waiver — chip (status) when done, secondary btn when needed
                    if ($status !== 'withdrawn') {
                        if (!empty($comp['waiver_accepted_at'])) {
                            echo '<span class="chip waiver-ok"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>Waiver signed</span>';
                        } else {
                            echo '<button class="btn btn-sm" mx-get="users/waiver_modal/' . out($comp['record_id']) . '" mx-build-modal="modalWaiver">Sign waiver</button>';
                        }
                    }

                    // Withdraw
                    if ($status === 'withdrawn') {
                        echo '<span class="chip chip-muted">Withdrawn</span>';
                    } elseif ($comp['status'] === 'open') {
                        echo '<button class="btn danger" mx-get="users/withdraw/' . out($comp['record_id']) . '" mx-build-modal="modalWithdraw">Withdraw</button>';
                    }
                  ?>
                </td>
              </tr>
            <?php } else { ?>
              <tr>
                <td colspan="5"><em class="subtle">No registrations found. Try searching at top section.</em></td>
              </tr>
            <?php }
          } ?>
          </tbody>
        </table>
      </div>
    </section>

    <?php endif; // $has_comps ?>

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

        // Skeleton rows while fetching — same shape as result cards.
        box.style.display = 'grid';
        box.style.gap = '8px';
        box.innerHTML = '<div class="skeleton skeleton-row"></div><div class="skeleton skeleton-row"></div>';

        try {
          const res = await fetch(`<?= BASE_URL ?>users/search?q=${encodeURIComponent(q)}`, {
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
          box.style.gap = '8px';
          box.innerHTML = data.map(x => `
              ${x.type !== 'competition' ? `<div class="card pad" style="padding:12px; display:flex; align-items:center; gap:12px">
              <div class="avatar-org">
                ${x.logo
                  ? `<img src="organizations_module/images/org_avatars/${escapeHtml(x.logo)}" alt="Logo" onerror="this.onerror=null;this.style.display='none';this.parentNode.innerHTML='<span class=\\'avatar-letter\\'>' + escapeHtml((x.title||'?')[0].toUpperCase()) + '</span>';" />`
                  : `<span class="avatar-letter">${escapeHtml((x.title||'?')[0].toUpperCase())}</span>`}
              </div>` : '<div class="card pad" style="padding:12px">'}
              <div class="list-item" style="align-items: center; justify-content: space-between; flex:1;">
                <div>
                  <strong>${escapeHtml(String((x.title).toUpperCase()))} ${escapeHtml(x.year || '')}</strong>
                  <h4 class="text-center subtle">• ${x.type === 'competition' ? 'Competition' : 'Organiser'} •</h4>
                  <h4 class="text-center subtle">${escapeHtml(x.organiser || x.country)} </h4>
                </div>
                <div>
                  ${x.entry_type ? `<span class="badge chip status-${escapeHtml(String(x.entry_type))}">${escapeHtml(String(x.entry_type).toUpperCase())}</span>` : ''}
                  <span class="badge chip status-${escapeHtml(String(x.status || ''))}">${escapeHtml(String(x.status || '').toUpperCase())}</span>
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

    // "/" focuses the competition search from anywhere on the page
    document.addEventListener('keydown', e => {
      if (e.key === '/' && !/INPUT|SELECT|TEXTAREA/.test(document.activeElement.tagName)) {
        e.preventDefault();
        const inp = document.getElementById('searchCompetition');
        inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
        inp.focus({ preventScroll: true });
      }
    });

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
            <button class="btn finished" style="margin-left:8px" onclick="openCompetition('${id}')">Results</button>
          `;
        default: // open/scheduled/other
          return `<button class="btn" style="margin-left:8px" mx-get="users/competition/${id}" mx-build-modal="competitionModal">Action</button>`;
      }
    }

    // optional: endpoints these buttons go to
    function goLive(id)       { if (!id) return; location.href = `heats/show_heats_draw/${id}`; }
    function viewResults(id)  { if (!id) return; location.href = `heats/show_heats_draw/${id}`; }
    function openCompetition(id){ if (!id) return; location.href = `heats/show_heats_draw/${id}`; }
    function openOrganiser(id){ if (!id) return; location.href = `organisers/view/${id}`; }

    <?php if ($has_heats): ?>
    // ===== Countdown to next heat =====
      const startAt = new Date('<?= out($start_time) ?>');
      const callAt  = new Date(startAt.getTime() - 15 * 60 * 1000); // -5 min

      function isValid(d){ return d instanceof Date && !isNaN(d.getTime()); }

      if (!isValid(startAt) || !isValid(callAt)) {

        document.getElementById('startTimeLabel').textContent = 'TBA';
        document.getElementById('callTime').textContent = 'TBA';
        document.getElementById('durationLabel').textContent = 'TBA';

      } else {
        
        document.getElementById('startTimeLabel').textContent = startAt.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        //document.getElementById('startTimeLabel').textContent = startAt;
        document.getElementById('callTime').textContent = callAt.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
      }

      let checkedIn = false;

      function tickCountdown() {
        const now = new Date();
        const ms = startAt.getTime() - now.getTime();
        const cd = $('#countdown');
        const pill = $('#countdownPill');
        const status = $('#heatStatus');

        if (!isValid(startAt) || !isValid(callAt)) {

          cd.textContent = '-:-';
          pill.textContent = 'upcoming';

        } else {

          if (ms <= 0) {
            cd.textContent = 'LIVE NOW';
            pill.textContent = checkedIn ? 'Checked-in' : 'Started';
            status.textContent = 'Running';
            status.parentElement.className = 'chip status-running';
            return; // stop updating after start
          }
          const s = Math.floor(ms / 1000);
          const h = String(Math.floor(s / 3600)).padStart(2, '0');
          const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
          const sec = String(s % 60).padStart(2, '0');
          cd.textContent = `${h}:${m}:${sec}`;
          pill.textContent = now >= callAt ? 'Check-in open' : 'Starts in';
          if (now >= callAt) {
            status.textContent = 'Check-in';
            status.parentElement.className = 'chip status-open';
          }
        }
      }
        const timer = setInterval(tickCountdown, 1000);
        tickCountdown();
    <?php endif; // $has_heats — countdown block ?>

    // ===== Actions =====
    function checkIn() {
      checkedIn = true;
      $('#countdownPill').textContent = 'Checked-in';
    }

    function copyJoinCode(btn, code) {
      navigator.clipboard.writeText(code).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = orig, 1500);
      });
    }

    function joinCompetition(ev) {
      ev.preventDefault();
    }

    function icsDateTime(iso) {
      return iso.replace(/[-:]/g, '').replace('.000Z', 'Z');
    }

    function buildVEvent(title, isoStart, isoEnd, location) {
      return [
        'BEGIN:VEVENT',
        'UID:' + crypto.randomUUID(),
        'DTSTAMP:' + icsDateTime(new Date().toISOString()),
        'DTSTART:' + icsDateTime(isoStart),
        'DTEND:' + icsDateTime(isoEnd),
        'SUMMARY:' + title,
        'LOCATION:' + (location || ''),
        'END:VEVENT',
      ].join('\r\n');
    }

    function downloadIcs(filename, vevents) {
      const ics = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//SurfPan//Participant//EN', ...vevents, 'END:VCALENDAR'].join('\r\n');
      const blob = new Blob([ics], {type: 'text/calendar'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = filename; a.click();
      URL.revokeObjectURL(url);
    }

    function addCalendar(title, isoStart, isoEnd, location) {
      downloadIcs(title.replace(/\s+/g, '_') + '.ics', [buildVEvent(title, isoStart, isoEnd, location)]);
    }

    function exportSchedule() {
      const scheduleData = <?= isset($schedule_heats) ? json_encode($schedule_heats, JSON_HEX_TAG | JSON_HEX_AMP) : '[]' ?>;
      if (!scheduleData.length) return;
      const vevents = scheduleData.map(h => buildVEvent(h.title, h.iso_start, h.iso_end, h.location));
      downloadIcs('my_heats.ics', vevents);
    }

    // ===== Schedule filter =====
    function filterSchedule() {
      const q = $('#scheduleSearch').value.toLowerCase();
      const rows = document.querySelectorAll('#scheduleTable tbody tr');
      rows.forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    }

    // ===== Division selector =====
    (function () {
      const divisionsByComp   = <?= json_encode($divisions_by_comp ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const compStatusMap     = <?= json_encode($comp_status_map ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

      const competitionSelect = document.getElementById('competitionSelect');
      const divisionSelect    = document.getElementById('divisionSelect');
      const jerseyChip        = document.getElementById('jerseyChip');
      const statusChipsContainer = document.getElementById('statusChips');

      const sanitizeVarName = (s) => String(s || '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

      function populateDivisions(compId) {
        const list = divisionsByComp[compId] || [];
        divisionSelect.innerHTML = '';

        const frag = document.createDocumentFragment();
        list.forEach(div => {
          const opt = document.createElement('option');
          opt.value = div.id;              // division_id
          opt.textContent = div.label;     // gender_age
          if (div.jersey) opt.dataset.jersey = div.jersey;
          frag.appendChild(opt);
        });
        divisionSelect.appendChild(frag);

        if (divisionSelect.options.length) {
          divisionSelect.disabled = false;
          divisionSelect.selectedIndex = 0;
          updateJerseyChip();
        } else {
          divisionSelect.disabled = true;
          clearJerseyChip();
        }
      }

      function clearJerseyChip() {
        if (!jerseyChip) return;
        jerseyChip.textContent = 'Jersey not assigned';
        jerseyChip.style.background = '';
      }

      function updateJerseyChip() {
        if (!jerseyChip) return;
        const sel = divisionSelect.options[divisionSelect.selectedIndex];
        if (!sel) {
          clearJerseyChip();
          return;
        }
        const jersey = sel.dataset.jersey;
        if (jersey) {
          jerseyChip.textContent = 'Jersey: ' + jersey.toUpperCase();
          jerseyChip.style.background = 'var(--jersey-' + sanitizeVarName(jersey) + ')';
        } else {
          clearJerseyChip();
        }
      }

      function highlightStatus(status) {
        if (!statusChipsContainer) return;
        statusChipsContainer.querySelectorAll('.chip').forEach(chip => {
          chip.classList.remove('is-active');
        });

        const normalized = sanitizeVarName(status);
        if (!normalized) return;

        const target = statusChipsContainer.querySelector('.chip.status-' + normalized);
        if (target) target.classList.add('is-active');
      }

      // Organizer Modal Tabs
      window.showTab = function(which) {
        const tabs = ["running", "upcoming", "past"];
        for (const t of tabs) {
          const el = document.getElementById('tab-' + t);
          const chip = document.querySelector(`.chip[data-tab="${t}"]`);
          if (!el || !chip) continue;
          const active = (t === which);
          el.hidden = !active;
          chip.setAttribute('aria-pressed', active);
          chip.setAttribute('aria-selected', active);
        }
      }

      document.addEventListener('DOMContentLoaded', () => {
        showTab('running');
      });

      function updateScoresBlock(compId) {
        document.querySelectorAll('.scores-block').forEach(el => {
          el.style.display = el.dataset.scoreComp === String(compId) ? '' : 'none';
        });
      }

      function onCompetitionChange(compId) {
        populateDivisions(compId);
        highlightStatus(compStatusMap[compId]);
        updateScoresBlock(compId);
      }

      // Single set of listeners
      competitionSelect.addEventListener('change', function () {
        onCompetitionChange(this.value);
      });
      divisionSelect.addEventListener('change', updateJerseyChip);

      // Single init
      onCompetitionChange(competitionSelect.value);
    })();
  </script>
