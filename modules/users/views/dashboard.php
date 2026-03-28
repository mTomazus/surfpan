<?php 
  $user_heats = Modules::run("users/_get_user_heats");
  $user_comps = Modules::run("users/_get_user_comps");
  // id, name, user_id, gender_age, location, year, status, entry_type, 
  // record_id, participation_status, jersey_color, start_time, end_time, round,
  // heat_number, heat_status, timezone

  //json($user_heats);

  // 1) Build unique competitions and grouped divisions.
  $comps = [];               // comp_id => ['id'=>..., 'label'=>...]
  $divisions_by_comp = [];   // comp_id => [ ['id'=>..., 'label'=>..., 'jersey'=>...], ... ]
  $comp_status_map = []; // comp_id => 'open' | 'running' | 'scheduled' | 'finished'

  $timezone = $user_heats[0]['timezone'];

  if ($user_heats[0]['start_time'] != null) {
  
    // Set timezone for start_time formatting
    $dt = new DateTimeImmutable($user_heats[0]['start_time'], new DateTimeZone('UTC'));
    $start_time = $dt->setTimezone(new DateTimeZone($timezone))->format("Y-m-d H:i:s");

    $start = new DateTime($user_heats[0]['start_time']);
    $end   = new DateTime($user_heats[0]['end_time']);
    $int = $start->diff($end);
    $minutes = ($int->days * 1440) + ($int->h * 60) + $int->i;
    if ($int->invert) $minutes *= -1;

  } else {

    //$start_time = date('Y-m-d H:i:s', strtotime('+6 days'));
    $start_time = null;
    $minutes = "N/A";

  }


  switch ($user_heats[0]['jersey_color']):
    case 'white':
      $color = 'black';
      break;
    default:
      $color = 'white';
  endswitch;

  foreach ($user_heats as $row) {

      $comp_id = (int) $row['id'];  // ensure you have id in $user_heats

      // Unique competition label (dedupes even if same name repeats in $user_heats)
      if (!isset($comps[$comp_id])) {
          $label = trim($row['name'].' '.$row['year']);
          $comps[$comp_id] = [
              'id'    => $comp_id,
              'label' => $label
          ];
      }

      if (!isset($comp_status_map[$comp_id])) {
          $comp_status_map[$comp_id] = strtolower(trim($row['status']));
      }

      // Collect divisions for this competition
      $divisions_by_comp[$comp_id][] = [
          'id'     => (int)($row['division_id'] ?? 0),          // ensure you have division_id in $user_heats
          'label'  => $row['division_name'], // ensure you have division_name in $user_heats
          'jersey' => $row['jersey_color'] ?? null,             // optional, if available
      ];
  }

  // Decide which competition is selected by default
  $first_comp_id = array_key_first($comps) ?? null;

?>

  <div class="grid cards">

    <!-- ===== Search Competitions / Organisers ===== -->
    <section class="card pad span-12" aria-labelledby="search-title">
      <div class="section-head">
        <h2 id="search-title"><?= t('dash_search_comps') ?></h2>
      </div>
      <div class="controls">
        <label class="field">
          <input type="search" id="searchCompetition" placeholder="<?= t('dash_search_placeholder') ?>" oninput="searchCompetitions()" />
        </label>
        <button class="btn" onclick="searchCompetitions()"><?= t('dash_search_btn') ?></button>
      </div>
      <div id="searchResults" class="list" style="margin-top:10px; display:none"></div>
    </section>

    <!-- ===== Context / Controls ===== -->
    <section class="card pad span-12" aria-labelledby="ctx-title">
      <div class="section-head">
      <h2 id="ctx-title"><?= t('dash_my_comps') ?></h2>
        <div id="statusChips" class="chips">
          <span class="chip status-open"><span class="dot" style="background:var(--chip-open)"></span><?= t('dash_status_open') ?></span>
          <span class="chip status-running"><span class="dot" style="background:var(--chip-running)"></span><?= t('dash_status_running') ?></span>
          <span class="chip status-scheduled"><span class="dot" style="background:var(--chip-scheduled)"></span><?= t('dash_status_scheduled') ?></span>
          <span class="chip status-finished"><span class="dot" style="background:var(--chip-finished)"></span><?= t('dash_status_finished') ?></span>
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

        <span class="chip" id="jerseyChip" title="Assigned jersey" style="background: var(--jersey-blue);font-weight: 900;color: <?= $color ?>">
          Jersey: BLUE
        </span>
      </div>
    </section>

    <!-- ===== Next Heat ===== -->
    <section class="card pad span-7" aria-labelledby="next-heat-title">
      <div class="section-head">
        <h2 id="next-heat-title"><?= t('dash_next_heat') ?></h2>
        <span class="chip status-<?= out($user_heats[0]['heat_status']) ?>"><span class="dot" style="background:var(--chip-<?= out($user_heats[0]['heat_status']) ?>)"></span><span id="heatStatus"><?= out($user_heats[0]['heat_status']) ?></span></span>
      </div>
      <div class="next-heat">
        <div class="countdown justify-around">
          <span class="pill" id="countdownPill"><?= t('dash_starts_in') ?></span>
          <span id="countdown">—:—:—</span>
        </div>
        <div class="meta" style="margin:1rem auto 0; text-align:center; padding-bottom:1rem;justify-content:center;">
          <span><?= out($user_heats[0]['division_name']) ?></span>
          <span>· <?= out($user_heats[0]['round']) ?> ·</span>
          <span>Heat #<?= out($user_heats[0]['heat_number']) ?></span>
          <h3 class="chip" style="color: <?= $color ?>; border: 1px solid;background: var(--jersey-<?= out($user_heats[0]['jersey_color']) ?>);
          font-weight: 900;font-size:1.2rem;text-align: center;margin: auto;display: grid;width: 100%;
          grid-template-columns: auto auto;justify-content: center;">Jersey: <span style="text-transform:uppercase"><?= out($user_heats[0]['jersey_color']) ?></span></h3>
        </div>
        <div class="meta justify-around">
          <h4><i class="fa fa-ticket" aria-hidden="true"></i> <strong id="callTime"><?= out($call_time) ?></strong></h4>
          <h4><i class="fa fa-clock-o" aria-hidden="true"></i> <strong id="startTimeLabel"><?= out($start_time) ?></strong></h4>
          <h4 class="text-center"><i class="fa fa-hourglass-start" aria-hidden="true"></i> <strong id="durationLabel"><?= out($minutes) ?> <?= t('dash_mins') ?></strong></h4>
        </div>
        <div class="controls mt-2" style="margin:auto; text-align:center; gap:1rem;display: grid;grid-template-columns: 1fr 1fr 1fr;">
          <button class="btn" onclick="checkIn()"><?= t('dash_check_in') ?></button>
          <button class="btn" onclick="alert('Rules opened (wire to /docs/rulebook.pdf)')"><?= t('dash_rules') ?></button>
          <button class="btn" onclick="goLive(<?= out($user_heats[0]['id']) ?>)"><?= t('dash_heats') ?></button>
        </div>
      </div>
    </section>

    <!-- ===== Scores ===== -->
    <section class="card pad span-5 disabled d-none" aria-labelledby="scores-title">
      <div class="section-head">
        <h3 id="scores-title"><?= t('dash_my_scores') ?></h3>
        <span class="subtle"><?= t('dash_best_two_waves') ?></span>
      </div>
      <div class="table-wrap">
        <table class="table" aria-describedby="scores-title">
          <thead>
            <tr>
              <th>Heat</th>
              <th>Wave</th>
              <th>J1</th>
              <th>J2</th>
              <th>J3</th>
              <th>J4</th>
              <th>J5</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#5</td>
              <td>1</td>
              <td>4.5</td><td>4.7</td><td>4.8</td><td>4.6</td><td>4.9</td>
              <td>23.5</td>
            </tr>
            <tr>
              <td>#5</td>
              <td>2</td>
              <td>6.2</td><td>6.0</td><td>6.1</td><td>6.3</td><td>6.2</td>
              <td>30.8</td>
            </tr>
            <tr>
              <td>#5</td>
              <td>3</td>
              <td>1.5</td><td>1.8</td><td>1.7</td><td>1.9</td><td>1.6</td>
              <td>8.5</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="7" class="right">Best 2 waves</th>
              <th>54.3</th>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="controls" style="margin-top:10px">
        <button class="btn" onclick="alert('Open detailed heat results…')">View heat details</button>
        <button class="btn" onclick="alert('Open rankings…')">Event rankings</button>
      </div>
    </section>

    <!-- ===== Schedule ===== -->
    <section class="card pad span-7 disabled d-none" aria-labelledby="schedule-title">
      <div class="section-head">
        <h3 id="schedule-title"><?= t('dash_upcoming_heats') ?></h3>
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
              <th>Status</th>
              <th class="right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>11:30</td>
              <td>#12</td>
              <td>Men Longboard</td>
              <td><span class="chip jersey" style="background:var(--jersey-blue);">BLUE</span></td>
              <td><span class="badge">Scheduled</span></td>
              <td class="right"><button class="btn" onclick="addCalendar('Heat 12', '2025-08-30T08:30:00Z')">Add to calendar</button></td>
            </tr>
            <tr>
              <td>14:10</td>
              <td>#28</td>
              <td>Men Longboard — R2</td>
              <td><span class="chip jersey" style="background:var(--jersey-blue);">BLUE</span></td>
              <td><span class="badge">TBC</span></td>
              <td class="right"><button class="btn" disabled>Pending</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===== Documents ===== -->
    <section class="card pad span-5" aria-labelledby="docs-title">
      <div class="section-head">
        <h2 id="docs-title"><?= t('dash_documents') ?></h2>
        <span class="subtle">Rulebook, waivers & heat draw</span>
      </div>
      <div class="doc-grid">
        <article class="doc">
          <div>
            <strong>ISA Rulebook (PDF)</strong>
            <div class="subtle">Updated Apr 2025</div>
          </div>
          <div class="controls">
            <a href="https://isasurf.org/downloads/ISA_RULEBOOK_April-2025.pdf" download="rulebook.pdf" style="text-decoration: none;" class="btn">Download</a>
          </div>
        </article>
        <article class="doc">
          <div>
            <strong>Heat Draw (HTML)</strong>
            <div class="subtle">Men Longboard · Rev 2</div>
          </div>
          <div class="controls">
            <button class="btn" onclick="alert('Open heat draw…')">Open</button>
          </div>
        </article>
        <article class="doc">
          <div>
            <strong>Liability Waiver</strong>
            <div class="subtle">Signed · 2025‑08‑25</div>
          </div>
          <div class="controls">
            <button class="btn" onclick="alert('View waiver…')">View</button>
          </div>
        </article>
      </div>
    </section>

    <!-- ===== Registrations ===== -->
    <section class="card pad span-12" aria-labelledby="regs-title">
      <div class="section-head">
        <h2 id="regs-title"><?= t('dash_my_registrations') ?></h2>
        <span class="subtle"><?= t('dash_manage_entries') ?></span>
      </div>
      <div  id="registrations" mx-get="users" mx-select="#registrations" mx-target="#registrations" mx-trigger="activate" class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th><?= t('dash_competition') ?></th>
              <th><?= t('dash_division') ?></th>
              <th><?= t('dash_status') ?></th>
              <th><?= t('dash_payment') ?></th>
              <th class="right"><?= t('dash_actions') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($user_comps as $comp) { 
              if (!empty($comp['name'])) { ?>
              <tr>
                <td><?= out($comp['name']) ?> <?= out($comp['year']) ?></td>
                <td><?= out($comp['division_name']) ?></td>
                <td><span class="badge <?= out($comp['participation_status']) ?>"><?= out($comp['participation_status']) ?></span></td>
                <?php if ($comp['entry_type'] === 'free entry') { ?>
                  <td><span class="badge" style="background: color-mix(in oklab, var(--warning), transparent 80%); border-color: color-mix(in oklab, var(--warning), transparent 50%);"><?= out(strtoupper($comp['entry_type'])) ?></span></td>
                <?php } else { ?>
                  <td><span class="badge" style="background: indianred; border-color: rgb(125, 0, 0); color: whitesmoke;"><?= out(strtoupper($comp['entry_type'])) ?></span></td>
                <?php } ?>
                <td class="right" style="gap: 1rem;display: flex;justify-content: end;">
                  <?php
                    if ($comp['participation_status'] === 'pending') {
                      if ($comp['entry_type'] === 'free entry') {
                        // free entry, pending approval
                        echo '<button class="btn" onclick="alert(\'Entry pending approval…\')" >Pending</button>';
                      } else {
                        // paid entry, pending payment
                        echo '<button class="btn" mx-get="billings/entry_pay_modal/' . out($comp['id']) . '" mx-build-modal="modalEntryPay">Pay now</button>';
                      }
                    } else if ($comp['participation_status'] === 'paid') {
                        // paid entry, not confirmed
                        echo '<button class="btn" onclick="alert(\'Open receipt…\')">Receipt</button>';
                    } else if ($comp['entry_type'] === 'entry fee') {
                      // other status (e.g. withdrawn)
                      echo '<button class="btn" onclick="alert(\'Open receipt…\')">Receipt</button>';
                    }
                    If ($comp['status'] === 'open') {
                  ?>
                    <button class="btn danger" mx-get="users/withdraw/<?= out($comp['record_id']) ?>" mx-build-modal="modalWithdraw"><?= t('dash_withdraw') ?></button>
                  <?php } else { ?>
                    <button class="btn disabled" disabled>Withdraw</button>
                  <?php } ?>
                </td>
              </tr>
            <?php } else { ?>
              <tr>
                <td colspan="5"><em class="subtle"><?= t('dash_no_registrations') ?></em></td>
              </tr>
            <?php }
          } ?>
          </tbody>
        </table>
      </div>
    </section>

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
          const res = await fetch(`/surfpan/users/search?q=${encodeURIComponent(q)}`, {
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
            <div class="card pad" style="padding:12px">
              <div class="list-item" style="align-items: center; justify-content: space-between;">
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

    // ===== Countdown to next heat =====
    //const startAt = new Date(Date.now() + 60 * 60 * 1000); // +1h

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
      
    
    // ===== Actions =====
    function checkIn() {
      checkedIn = true;
      alert('Checked-in! (POST /heats/checkin)');
      $('#countdownPill').textContent = 'Checked-in';
    }

    function copyJoinCode(code) {
      navigator.clipboard.writeText(code).then(() => {
        alert('Your join code copied: ' + code);
      });
    }

    function joinCompetition(ev) {
      ev.preventDefault();
      const code = $('#joinCode').value.trim();
      if (!code) return alert('Enter a code');
      alert('Joining with code: ' + code + ' (POST /competitions/join)');
    }

    function addCalendar(title, iso) {
      const dtStart = new Date(iso);
      const dtEnd = new Date(dtStart.getTime() + 20*60*1000);
      const fmt = d => d.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
      const ics = [
        'BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Surf Club LT//Participant//EN','BEGIN:VEVENT',
        'UID:'+crypto.randomUUID(),
        'DTSTAMP:'+fmt(new Date()),
        'DTSTART:'+fmt(dtStart),
        'DTEND:'+fmt(dtEnd),
        'SUMMARY:'+title,
        'LOCATION:2nd Jetty, Melnrage',
        'END:VEVENT','END:VCALENDAR' 
      ].join('\r\n');
      const blob = new Blob([ics], {type:'text/calendar'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = `${title.replace(/\s+/g,'_')}.ics`; a.click();
      URL.revokeObjectURL(url);
    }

    function exportSchedule() { addCalendar('Heat 12', new Date(Date.now()+3600*1000).toISOString()); }

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
      const divisionsByComp   = <?= json_encode($divisions_by_comp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const compStatusMap     = <?= json_encode($comp_status_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

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

      function onCompetitionChange(compId) {
        populateDivisions(compId);
        highlightStatus(compStatusMap[compId]);
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
