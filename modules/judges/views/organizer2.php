<?php
// ---------- helpers ----------
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function date_short($d){ if(!$d) return ''; $ts=strtotime($d); return $ts?date('M j, Y',$ts):e($d); }
function chip($label,$kind='neutral'){
  $cls = [
    'good'=>'chip chip-good','warn'=>'chip chip-warn','bad'=>'chip chip-bad',
    'info'=>'chip chip-info','neutral'=>'chip'
  ][$kind] ?? 'chip';
  return '<span class="'.$cls.'">'.e($label).'</span>';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Organizer Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
/* ====== Theme ====== */
:root{
  --bg: #0f1222;
  --surface: #171a2e;
  --surface-2: #1e2240;
  --text: #e9eef7;
  --muted: #96a0b5;
  --primary: #67d8ff;
  --primary-ink: #07131a;
  --ok: #4ade80;
  --warn: #fbbf24;
  --bad: #f87171;
  --info: #60a5fa;
  --ring: #7dd3fc;
  --border: #2a2f52;
  --radius: 14px;
  --shadow: 0 10px 24px rgba(0,0,0,.3);
}
*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0; font:14px/1.45 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;
  background: radial-gradient(1200px 600px at 10% -10%, #1a1e3c 0%, #0f1222 55%) no-repeat fixed;
  color: var(--text);
}

/* ====== Layout ====== */
.header{
  position: sticky; top:0; z-index:50; backdrop-filter: blur(8px);
  background: color-mix(in oklab, var(--bg) 80%, transparent);
  border-bottom: 1px solid var(--border);
}
.header-wrap{
  max-width:1200px; margin:auto; padding:12px 18px; display:flex; align-items:center; gap:14px; justify-content:space-between;
}
.brand{display:flex; align-items:center; gap:10px; font-weight:800; letter-spacing:.5px}
.brand-badge{
  width:34px; height:34px; display:grid; place-items:center; border-radius:10px;
  background: linear-gradient(145deg,#2b6cb0,#67d8ff);
  color:#00131a; font-weight:900; box-shadow: var(--shadow);
}
.userline{color:var(--muted); font-size:13px}

.wrap{max-width:1200px; margin:24px auto; padding:0 18px; display:grid; gap:18px}

/* ====== Cards/Sections ====== */
.section{
  background: color-mix(in oklab, var(--surface) 94%, transparent);
  border:1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding:18px;
}
.section h2{margin:0 0 12px; font-size:18px; letter-spacing:.3px}

.grid{display:grid; gap:16px}
.grid-2{grid-template-columns: 380px 1fr}
@media (max-width: 980px){ .grid-2{grid-template-columns: 1fr} }

/* ====== Forms ====== */
form.grid-form{
  display:grid; gap:10px; grid-template-columns: 1fr 2fr;
  align-items:center;
}
form.grid-form .full{grid-column:1 / -1}
label{color:var(--muted); font-weight:600}
input[type="text"], input[type="email"], input[type="date"], select{
  width:100%; padding:10px 12px; color:var(--text);
  background: var(--surface-2); border:1px solid var(--border); border-radius:10px;
  outline: none; transition: border .2s, box-shadow .2s;
}
input:focus, select:focus{ border-color: var(--ring); box-shadow: 0 0 0 4px color-mix(in oklab, var(--ring) 20%, transparent) }
button, .btn{
  display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px;
  background: linear-gradient(180deg, var(--primary) 0%, #48c1ff 100%); color:var(--primary-ink);
  border:1px solid color-mix(in oklab, var(--primary) 35%, black); font-weight:800; cursor:pointer;
  text-decoration:none; box-shadow: 0 6px 16px rgba(103,216,255,.26);
}
.btn:hover{ filter:brightness(1.05) translateZ(0); transform: translateY(-1px); }
.btn-ghost{ background: transparent; color: var(--text); border:1px solid var(--border); box-shadow:none }
.btn-danger{ background: linear-gradient(180deg, #ff8a8a, #f87171); color:#1a0505; border-color:#f87171 }

/* ====== Chips ====== */
.chip{
  display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; border:1px solid var(--border);
  background: var(--surface-2); color:var(--muted); font-size:12px; font-weight:700; letter-spacing:.2px;
}
.chip-good{ background: color-mix(in oklab, var(--ok) 20%, var(--surface-2)); color:#062413; border-color: color-mix(in oklab, var(--ok) 50%, black) }
.chip-warn{ background: color-mix(in oklab, var(--warn) 20%, var(--surface-2)); color:#241a06; border-color: color-mix(in oklab, var(--warn) 50%, black) }
.chip-bad{  background: color-mix(in oklab, var(--bad) 20%, var(--surface-2));  color:#230b0b; border-color: color-mix(in oklab, var(--bad) 50%, black) }
.chip-info{ background: color-mix(in oklab, var(--info) 20%, var(--surface-2)); color:#071826; border-color: color-mix(in oklab, var(--info) 50%, black) }

/* ====== Tables ====== */
.table-wrap{overflow:auto; border:1px solid var(--border); border-radius:12px}
table{width:100%; border-collapse: collapse; min-width: 720px}
th,td{padding:12px 10px; text-align:left}
thead th{
  position:sticky; top:0; background: color-mix(in oklab, var(--surface-2) 94%, transparent);
  color:var(--muted); font-size:12px; letter-spacing:.25px; text-transform: uppercase; border-bottom:1px solid var(--border);
}
tbody tr{ border-bottom:1px solid var(--border) }
tbody tr:hover{ background: color-mix(in oklab, var(--surface) 80%, black 10%) }
td.actions{ text-align:right; white-space:nowrap }

/* ====== Lists ====== */
.list-inline{display:flex; flex-wrap:wrap; gap:8px}
.kv{display:grid; grid-template-columns: 110px 1fr; gap:6px; color:var(--muted)}
hr.sep{border:0; border-top:1px solid var(--border); margin:10px 0}

/* ====== Empty ====== */
.empty{
  padding:18px; text-align:center; color:var(--muted);
  border:1px dashed var(--border); border-radius:12px; background: color-mix(in oklab, var(--surface) 85%, transparent);
}

/* ====== Responsive tweaks ====== */
@media (max-width:680px){
  form.grid-form{ grid-template-columns: 1fr }
  .kv{ grid-template-columns: 1fr }
}
</style>
</head>
<body>

<!-- ===== Header ===== -->
<div class="header">
  <div class="header-wrap">
    <div class="brand">
      <span class="brand-badge">SP</span>
      <span>Organizer Dashboard</span>
    </div>
    <div class="userline">
      <?php if(!empty($user)): ?>
        <?= e($user->name ?? '—') ?> &middot; <?= e($user->email ?? '') ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="wrap">

  <!-- Organization selector -->
  <section class="section">
    <h2>Organization</h2>
    <?php if (empty($orgs)): ?>
      <div class="empty">No organizations linked to your account.</div>
    <?php else: ?>
      <form class="grid-form" onChange="navigateToOrg(this.org_id.value); return false;">
        <label for="org_id">Select organization</label>
        <select id="org_id" name="org_id">
          <?php foreach ($orgs as $o): ?>
            <option value="<?= (int)$o['id'] ?>" <?= ((int)$o['id'] === (int)$org_id ? 'selected' : '') ?>><?= e($o['organization']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="full">
          <small class="kv"><span>Hint</span><span>Switching will reload the dashboard for that organization.</span></small>
        </div>
      </form>
    <?php endif; ?>
  </section>

  <!-- Competitions -->
  <section class="section">
    <h2>Competitions</h2>
    <div class="grid grid-2">

      <!-- Create/Edit -->
      <div>
        <div class="kv" style="margin-bottom:10px">
          <span>Quick status</span>
          <span class="list-inline">
            <?= chip('Scheduled','info') ?><?= chip('Open','good') ?><?= chip('Running','warn') ?><?= chip('Finished','neutral') ?><?= chip('Closed','bad') ?>
          </span>
        </div>

        <form class="grid-form" method="post" action="<?= BASE_URL ?>organizers/competition/save" id="compForm">
          <input type="hidden" name="id" id="comp_id">
          <input type="hidden" name="organization_id" value="<?= (int)($org_id ?? 0) ?>">

          <label>Name</label>
          <input type="text" name="name" id="comp_name" required>

          <label>Year</label>
          <input type="text" name="year" id="comp_year" placeholder="<?= date('Y') ?>">

          <label>Location</label>
          <input type="text" name="location" id="comp_location">

          <label>Start date</label>
          <input type="date" name="start_date" id="comp_start">

          <label>End date</label>
          <input type="date" name="end_date" id="comp_end">

          <label>Status</label>
          <select name="status" id="comp_status">
            <option value="scheduled">Scheduled</option>
            <option value="open">Open</option>
            <option value="running">Running</option>
            <option value="finished">Finished</option>
            <option value="closed">Closed</option>
          </select>

          <div class="full" style="display:flex; gap:10px; margin-top:6px">
            <button type="submit">Save</button>
            <button type="button" class="btn btn-ghost" onclick="resetCompForm()">Clear</button>
          </div>
        </form>
      </div>

      <!-- List -->
      <div>
        <?php if (empty($comps)): ?>
          <div class="empty">No competitions yet.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Competition</th>
                  <th>When &amp; where</th>
                  <th>Status</th>
                  <th style="text-align:right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($comps as $c): ?>
                <tr>
                  <td><strong><?= e($c['name']) ?></strong><?= !empty($c['year']) ? ' · '.e($c['year']) : '' ?></td>
                  <td>
                    <?= date_short($c['start_date']) ?><?= !empty($c['end_date']) ? ' – '.date_short($c['end_date']) : '' ?>
                    <?= !empty($c['location']) ? ' · '.e($c['location']) : '' ?>
                  </td>
                  <td>
                    <?php
                      $st = strtolower($c['status'] ?? 'scheduled');
                      $kind = match($st){
                        'running'=>'warn', 'open'=>'good', 'finished'=>'neutral', 'closed'=>'bad', default=>'info'
                      };
                      echo chip(ucfirst($st), $kind);
                    ?>
                  </td>
                  <td class="actions">
                    <button class="btn" onclick='prefillComp(<?= json_encode($c, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form style="display:inline" method="post" action="<?= BASE_URL ?>organizers/competition/delete/<?= (int)$c['id'] ?>" onsubmit="return confirm('Delete this competition and related data?');">
                      <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </section>

  <!-- Judges -->
  <section class="section">
    <h2>Judges</h2>
    <div class="grid grid-2">

      <!-- Create/link judge -->
      <div>
        <form class="grid-form" method="post" action="<?= BASE_URL ?>organizers/judge_create">
          <input type="hidden" name="organization_id" value="<?= (int)($org_id ?? 0) ?>">
          <label>Name</label>
          <input type="text" name="name" required>
          <label>Email</label>
          <input type="email" name="email" required>
          <label>Role</label>
          <select name="role">
            <option value="judge">Judge</option>
            <option value="head_judge">Head Judge</option>
            <option value="video_judge">Video Judge</option>
          </select>
          <div class="full" style="margin-top:6px"><button type="submit">Save</button></div>
        </form>

        <hr class="sep">

        <!-- Assign judge to competition -->
        <form class="grid-form" method="post" action="<?= BASE_URL ?>organizers/judge_assign">
          <input type="hidden" name="organization_id" value="<?= (int)($org_id ?? 0) ?>">
          <label>Competition</label>
          <select name="competition_id" required>
            <option value="">Select…</option>
            <?php foreach ($comps as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?><?= !empty($c['year']) ? ' · '.e($c['year']) : '' ?></option>
            <?php endforeach; ?>
          </select>

          <label>Judge</label>
          <select name="user_id" required>
            <option value="">Select…</option>
            <?php foreach ($org_judges as $j): ?>
              <option value="<?= (int)$j['user_id'] ?>"><?= e($j['name']) ?> (<?= e($j['email']) ?>)</option>
            <?php endforeach; ?>
          </select>

          <label>Role</label>
          <select name="role">
            <option value="judge">Judge</option>
            <option value="head_judge">Head Judge</option>
            <option value="video_judge">Video Judge</option>
          </select>

          <div class="full" style="margin-top:6px"><button type="submit">Assign</button></div>
        </form>
      </div>

      <!-- Assignments per competition -->
      <div>
        <?php if (empty($comps)): ?>
          <div class="empty">No competitions to show assignments.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Competition</th>
                  <th>Assigned judges</th>
                  <th style="text-align:right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($comps as $c): $list = $assigned_by_comp[$c['id']] ?? []; ?>
                  <tr>
                    <td><strong><?= e($c['name']) ?></strong><?= !empty($c['year']) ? ' · '.e($c['year']) : '' ?></td>
                    <td>
                      <?php if (empty($list)): ?>
                        <span class="muted">None</span>
                      <?php else: ?>
                        <div class="list-inline">
                          <?php foreach ($list as $a): ?>
                            <?= chip($a['name'].' · '.ucwords(str_replace('_',' ',$a['role'])).' ('.($a['status'] ?? 'active').')','info') ?>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="actions">
                      <?php foreach ($list as $a): ?>
                        <form style="display:inline" method="post" action="<?= BASE_URL ?>organizers/judge_remove/<?= (int)$a['id'] ?>" onsubmit="return confirm('Remove this judge from the competition?');">
                          <button class="btn btn-danger" type="submit">Remove</button>
                        </form>
                      <?php endforeach; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </section>

  <!-- Participants -->
  <section class="section">
    <h2>Participants</h2>
    <?php if (empty($participants_by_comp)): ?>
      <div class="empty">No participants yet.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Competition</th>
              <th>Participant</th>
              <th>Status</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($comps as $c): $rows = $participants_by_comp[$c['id']] ?? []; if (empty($rows)) continue; ?>
              <?php foreach ($rows as $idx => $p): 
                $nm = $p['user_name'] ?: trim(($p['first_name'] ?? '').' '.($p['last_name'] ?? ''));
                $status = strtolower($p['status'] ?? 'pending');
                $kind = $status==='confirmed' ? 'good' : ($status==='pending' ? 'info' : 'warn');
              ?>
              <tr>
                <?php if ($idx===0): ?>
                  <td rowspan="<?= count($rows) ?>"><strong><?= e($c['name']) ?></strong><?= !empty($c['year'])?' · '.e($c['year']):'' ?></td>
                <?php endif; ?>
                <td><?= e($nm ?: '—') ?><?= !empty($p['email']) ? ' · <small>'.e($p['email']).'</small>' : '' ?></td>
                <td><?= chip(ucfirst($status), $kind) ?></td>
                <td class="actions">
                  <?php if ($status !== 'confirmed'): ?>
                    <form style="display:inline" method="post" action="<?= BASE_URL ?>organizers/participant_confirm/<?= (int)$p['id'] ?>">
                      <button class="btn" type="submit">Confirm</button>
                    </form>
                  <?php endif; ?>
                  <form style="display:inline" method="post" action="<?= BASE_URL ?>organizers/participant_delete/<?= (int)$p['id'] ?>" onsubmit="return confirm('Delete this participant?');">
                    <button class="btn btn-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

</div>

<script>
function navigateToOrg(id){
  if(!id) return;
  location.href = "<?= BASE_URL ?>organizers/dashboard/" + id;
}
function prefillComp(c){
  document.getElementById('comp_id').value = c.id || '';
  document.getElementById('comp_name').value = c.name || '';
  document.getElementById('comp_year').value = c.year || '';
  document.getElementById('comp_location').value = c.location || '';
  document.getElementById('comp_start').value = c.start_date || '';
  document.getElementById('comp_end').value = c.end_date || '';
  document.getElementById('comp_status').value = c.status || 'scheduled';
  document.getElementById('comp_name').focus({preventScroll:false});
}
function resetCompForm(){
  document.getElementById('compForm').reset();
  document.getElementById('comp_id').value = '';
}
</script>

</body>
</html>
