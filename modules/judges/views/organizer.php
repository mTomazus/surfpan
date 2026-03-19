<?php
function fmt_date($d){ if(!$d) return ''; $ts=strtotime($d); return $ts?date('M j, Y',$ts):htmlspecialchars($d); }
function chip($txt,$kind='neutral'){
  $map=[
    'running'=>'background:var(--chip-running);color:#000',
    'open'=>'background:var(--chip-open);color:#000',
    'scheduled'=>'background:var(--chip-scheduled);color:#000',
    'finished'=>'background:var(--chip-finished);color:#000',
    'closed'=>'background:var(--chip-closed);color:#000',
    'ok'=>'background:var(--ok);color:#000',
    'warn'=>'background:var(--warn);color:#000',
    'info'=>'background:var(--info);color:#000',
    'neutral'=>'background:transparent;color:var(--white);border:1px solid var(--white)',
  ];
  $style=$map[$kind]??$map['neutral'];
  return '<span class="chip" style="'.$style.'">'.htmlspecialchars($txt).'</span>';
}
?>

<div class="container grid-2 gap-1">
    <div class="grid-1 gap-1">

        <!-- ORG PICKER -->
        <section class="card">
            <div class="row" style="display: flex; justify-content:space-between; margin-bottom:10px">
                <h2 style="margin:0; font-size:18px">Organization</h2  style="margin:0; font-size:18px">
            <div>
            <?php if (empty($orgs)): ?>
            <div class="card" style="border:1px solid var(--white); padding:1rem;">
                <p>No organizations linked to your account.</p>
            </div>
            <?php else: ?>
            <form method="get" onChange="location='<?= BASE_URL ?>organizers/dashboard/'+this.org_id.value" style="grid-template-columns:1fr 2fr;max-width:620px;margin:auto">
                <label for="org_id"><strong>Select Organization</strong></label>
                <select id="org_id" name="org_id">
                <?php foreach ($orgs as $o): ?>
                    <option value="<?= (int)$o['id'] ?>" <?= ((int)$o['id']===(int)$org_id?'selected':'') ?>>
                    <?= htmlspecialchars($o['organization']) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>
        </section>

        <!-- COMPETITIONS -->
        <section class=" card">
            <h2 style="margin:0; font-size:18px">Competitions</h2  style="margin:0; font-size:18px">

            <div class="wrapper" style="align-items:start">
            <!-- Create / Edit form -->
            <div class="card card-judge" style="text-align:left;">
                <h4 style="margin-bottom:.5rem;">Create / Edit Competition</h4>
                <form method="post" action="<?= BASE_URL ?>organizers/competition/save">
                <input type="hidden" name="id" id="comp_id">
                <input type="hidden" name="organization_id" value="<?= (int)($org_id ?? 0) ?>">

                <label>Name</label>
                <input type="text" name="name" id="comp_name" required>

                <label>Year</label>
                <input type="text" name="year" id="comp_year" placeholder="<?= date('Y') ?>">

                <label>Location</label>
                <input type="text" name="location" id="comp_location">

                <label>Start</label>
                <input type="date" name="start_date" id="comp_start">

                <label>End</label>
                <input type="date" name="end_date" id="comp_end">

                <label>Status</label>
                <select name="status" id="comp_status">
                    <option value="scheduled">Scheduled</option>
                    <option value="open">Open</option>
                    <option value="running">Running</option>
                    <option value="finished">Finished</option>
                    <option value="closed">Closed</option>
                </select>

                <button class="btn-primary" type="submit" style="grid-column:1/-1;margin-top:.5rem;">Save</button>
                </form>
            </div>

            <!-- List -->
            <div class="edit-scores-table" style="width:100%;">
                <?php if (empty($comps)): ?>
                <div class="card" style="border:1px solid var(--white); padding:1rem;">
                    <p>No competitions yet.</p>
                </div>
                <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Competition</th>
                        <th>When &amp; Where</th>
                        <th>Status</th>
                        <th class="d-sm-none"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($comps as $c): ?>
                    <tr>
                        <td>
                        <strong><?= htmlspecialchars($c['name']) ?></strong>
                        <?= !empty($c['year']) ? ' · '.htmlspecialchars($c['year']) : '' ?>
                        </td>
                        <td>
                        <?= fmt_date($c['start_date']) ?>
                        <?php if (!empty($c['end_date'])): ?> – <?= fmt_date($c['end_date']) ?><?php endif; ?>
                        <?php if (!empty($c['location'])): ?> · <?= htmlspecialchars($c['location']) ?><?php endif; ?>
                        </td>
                        <td>
                        <?php $st=strtolower($c['status'] ?? 'scheduled'); echo chip(ucfirst($st), $st==='running'?'running':($st==='open'?'open':($st==='finished'?'finished':($st==='closed'?'closed':'scheduled')))); ?>
                        </td>
                        <td>
                        <div class="d-flex justify-content-evenly">
                            <button class="btn-primary" onclick='prefillComp(<?= json_encode($c, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>Edit</button>
                            <form method="post" action="<?= BASE_URL ?>organizers/competition/delete/<?= (int)$c['id'] ?>" onsubmit="return confirm('Delete competition & related data?');">
                            <button class="btn-primary modal-delete" type="submit">Delete</button>
                            </form>
                        </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            </div>
        </section>
    </div>
    <div class="grid-1 gap-1">
        <!-- JUDGES -->
        <section class=" card">
            <h2 style="margin:0; font-size:18px">Judges</h2  style="margin:0; font-size:18px">
            <div class="wrapper" style="align-items:start">
            <!-- Create judge -->
            <div class="card card-judge" style="text-align:left;">
                <h4>Add / Link Judge to Org</h4>
                <form method="post" action="<?= BASE_URL ?>organizers/judge_create">
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
                <button class="btn-primary" type="submit" style="grid-column:1/-1;margin-top:.5rem;">Save</button>
                </form>
            </div>

            <!-- Assign judge to competition -->
            <div class="card card-judge" style="text-align:left;">
                <h4>Assign Judge to Competition</h4>
                <form method="post" action="<?= BASE_URL ?>organizers/judge_assign">
                <input type="hidden" name="organization_id" value="<?= (int)($org_id ?? 0) ?>">
                <label>Competition</label>
                <select name="competition_id" required>
                    <option value="">Select…</option>
                    <?php foreach ($comps as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?><?= !empty($c['year'])?' · '.htmlspecialchars($c['year']):'' ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Judge</label>
                <select name="user_id" required>
                    <option value="">Select…</option>
                    <?php foreach ($org_judges as $j): ?>
                    <option value="<?= (int)$j['user_id'] ?>"><?= htmlspecialchars($j['name']) ?> (<?= htmlspecialchars($j['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>

                <label>Role</label>
                <select name="role">
                    <option value="judge">Judge</option>
                    <option value="head_judge">Head Judge</option>
                    <option value="video_judge">Video Judge</option>
                </select>

                <button class="btn-primary" type="submit" style="grid-column:1/-1;margin-top:.5rem;">Assign</button>
                </form>
            </div>

            <!-- Judges list & assignments per comp -->
            <div class="edit-scores-table" style="width:100%;">
                <table>
                <thead>
                    <tr>
                    <th>Competition</th>
                    <th>Assigned Judges</th>
                    <th class="d-sm-none"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($comps)): ?>
                    <tr><td colspan="3">No competitions.</td></tr>
                <?php else: foreach ($comps as $c): 
                    $list = $assigned_by_comp[$c['id']] ?? []; ?>
                    <tr>
                    <td><strong><?= htmlspecialchars($c['name']) ?></strong><?= !empty($c['year'])?' · '.htmlspecialchars($c['year']):'' ?></td>
                    <td>
                        <?php if (empty($list)): ?>
                        <?= chip('None','neutral') ?>
                        <?php else: ?>
                        <ul class="legend" style="border:none;box-shadow:none;">
                            <?php foreach ($list as $a): ?>
                            <li>
                                <h4 style="margin:0">
                                <?= htmlspecialchars($a['name']) ?> · <?= ucwords(str_replace('_',' ',$a['role'])) ?>
                                <small>(<?= htmlspecialchars($a['status']) ?>)</small>
                                </h4>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php foreach ($list as $a): ?>
                        <form method="post" action="<?= BASE_URL ?>organizers/judge_remove/<?= (int)$a['id'] ?>" onsubmit="return confirm('Remove judge from this competition?');">
                            <button class="btn-primary modal-delete" type="submit">Remove</button>
                        </form>
                        <?php endforeach; ?>
                    </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
                </table>
            </div>
            </div>
        </section>

        <!-- PARTICIPANTS -->
        <section class=" card">
            <h2 style="margin:0; font-size:18px">Participants</h2  style="margin:0; font-size:18px">
            <div class="edit-scores-table" style="width:100%;">
            <table>
                <thead>
                <tr>
                    <th>Competition</th>
                    <th>Participant</th>
                    <th>Status</th>
                    <th class="d-sm-none"></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($participants_by_comp)): ?>
                <tr><td colspan="4">No participants yet.</td></tr>
                <?php else: foreach ($comps as $c): 
                $rows = $participants_by_comp[$c['id']] ?? []; ?>
                <?php if (empty($rows)) continue; ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['name']) ?></strong><?= !empty($c['year']) ? ' · '.htmlspecialchars($c['year']) : '' ?></td>
                    <td style="text-align:left;">
                    <?php foreach ($rows as $p): 
                        $nm = $p['user_name'] ?: trim(($p['first_name'] ?? '').' '.($p['last_name'] ?? '')); ?>
                        <div style="padding:.25rem 0; border-bottom:1px solid rgba(255,255,255,.1)">
                        <?= htmlspecialchars($nm ?: '—') ?>
                        <?php if(!empty($p['email'])): ?> · <small><?= htmlspecialchars($p['email']) ?></small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </td>
                    <td>
                    <?php foreach ($rows as $p): 
                        $st = strtolower($p['status'] ?? 'pending');
                        echo '<div>'.chip(ucfirst($st), $st==='confirmed'?'ok':($st==='pending'?'info':'warn')).'</div>';
                    endforeach; ?>
                    </td>
                    <td>
                    <?php foreach ($rows as $p): ?>
                        <div class="d-flex" style="gap:.5rem;justify-content:center;margin:.25rem 0;">
                        <?php if (($p['status'] ?? 'pending') !== 'confirmed'): ?>
                            <form method="post" action="<?= BASE_URL ?>organizers/participant_confirm/<?= (int)$p['id'] ?>">
                            <button class="btn-primary" type="submit">Confirm</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= BASE_URL ?>organizers/participant_delete/<?= (int)$p['id'] ?>" onsubmit="return confirm('Delete this participant?');">
                            <button class="btn-primary modal-delete" type="submit">Delete</button>
                        </form>
                        </div>
                    <?php endforeach; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </section>
    </div>
</div>

<script>
  // Prefill competition form for editing
  function prefillComp(c){
    document.getElementById('comp_id').value = c.id || '';
    document.getElementById('comp_name').value = c.name || '';
    document.getElementById('comp_year').value = c.year || '';
    document.getElementById('comp_location').value = c.location || '';
    document.getElementById('comp_start').value = c.start_date || '';
    document.getElementById('comp_end').value = c.end_date || '';
    document.getElementById('comp_status').value = (c.status || 'scheduled');
    window.scrollTo({top: 0, behavior: 'smooth'});
  }
</script>

</body>
</html>