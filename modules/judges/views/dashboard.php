<?php
// ========== tiny helpers ==========
function fmt_date($d){
  if(!$d) return '';
  $ts = strtotime($d);
  return $ts ? date('M j, Y', $ts) : out($d);
}
function chip_status($label, $status){ // running/open/scheduled/past/etc.
  $map = [
    'running'   => 'background:var(--chip-running);color:#000',
    'open'      => 'background:var(--chip-open);color:#000',
    'scheduled' => 'background:var(--chip-scheduled);color:#000',
    'finished'  => 'background:var(--chip-finished);color:#000',
    'closed'    => 'background:var(--chip-closed);color:#000',
    'upcoming'  => 'background:var(--chip-scheduled);color:#000',
    'past'      => 'background:var(--chip-closed);color:#000',
    'warn'      => 'background:var(--warn);color:#000',
    'ok'        => 'background:var(--ok);color:#000',
    'info'      => 'background:var(--info);color:#000',
    'neutral'   => 'background:var(--neutral);color:var(--bg-elev);border:0'
  ];
  $style = $map[strtolower($status)] ?? $map['neutral'];
  return '<span class="chip" style="'.$style.'">'.out($label).'</span>';
}
?>

<div id="form-container" mx-get="judges" mx-trigger="activate" mx-select="#form-container">
  <div id="response"></div>
  <!-- ===== Your Competitions ===== -->
  <h2 class="mb-0 mt-0"><span>Your</span> Assignments</h2>
  <?php if (empty($active_assigned)): ?>
    <?= '<p class="mb-2">You have no active assignments and ' . (count($pending_invites ?? [])) . ' pending invitations.</p>' ?>
  <?php else: ?>
    <?= '<p class="mb-2"> You have currently ' . count($active_assigned) . ' assignment<br>and ' . (count($pending_invites ?? [])) . ' pending invitations.</p>' ?>
  <?php endif; ?>
      
  <section>
      <table id="assignments-table">
        <thead>
          <tr class="d-sm-none">
            <th>Competition</th>
            <th>Status</th>
            <th>Role</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="4" class="section-header" style="color: var(--chip-running);">Your active assignments</td>
          </tr>
          <?php if (!empty($active_assigned)): ?>
            <?php foreach ($active_assigned as $c): ?>
              <tr>
                <td>
                  <strong style="font-size: 0.75rem;color: var(--primary-60);"><?= out($c['name']) ?>
                  <?= !empty($c['year']) ? ' '.out($c['year']) : '' ?></strong><br>
                  <?= fmt_date($c['start_date']) ?>
                  <?php if (!empty($c['end_date'])): ?> – <?= fmt_date($c['end_date']) ?><?php endif; ?>
                  <?php if (!empty($c['location'])): ?> <br> <?= out($c['location']) ?><?php endif; ?>
                </td>
                <td>
                  <?php
                    $status = strtolower($c['comp_status'] ?? 'neutral');
                    echo chip_status(ucfirst($status), $status);
                  ?>
                </td>
                <td><?= out(ucwords(str_replace('_',' ', $c['judge_role'] ?? 'judge'))) ?></td>
                <td>
                  <a class="btn" href="<?= BASE_URL ?>judges/enter/<?= (int)$c['comp_id'] ?>">Enter</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr class="empty no-assigned"><td colspan="4">You are not currently assigned to any competition.</td></tr>
          <?php endif; ?>

          <tr>
            <td colspan="4" class="section-header" style="color: var(--chip-closed);">Your pending invitations</td>
          </tr>
          <?php if (empty($pending_invites)): ?>
            <tr class="empty no-invited"><td colspan="4">You have no pending invitations.</td></tr>
          <?php else: ?>
            <?php foreach ($pending_invites as $c): ?>
              <tr>
                <td>
                  <strong style="font-size: 0.75rem;color: var(--primary-60);text-transform: uppercase;"><?= out($c['name']) ?>
                  <?= !empty($c['year']) ? ' '.out($c['year']) : '' ?></strong><br>
                  <?= fmt_date($c['start_date']) ?>
                  <?php if (!empty($c['end_date'])): ?> – <?= fmt_date($c['end_date']) ?><?php endif; ?>
                  <?php if (!empty($c['location'])): ?> <br> <?= out($c['location']) ?><?php endif; ?>
                </td>
                <td><?= chip_status('Pending Invite','warn') ?></td>
                <td><?= out(ucwords(str_replace('_',' ', $c['judge_role'] ?? 'judge'))) ?></td>
                <td class="actions" style="max-width: -webkit-fit-content;margin: auto;">
                  <form mx-post="<?= BASE_URL ?>judges/accept_invite/<?= (int)$c['link_id'] ?>" mx-on-success="#form-container" mx-select="#form-container" mx-target="#response" style="display:inline">
                    <button class="btn accept" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Accept</button>
                  </form>
                  <form mx-post="<?= BASE_URL ?>judges/decline_invite/<?= (int)$c['link_id'] ?>" mx-on-success="#form-container" mx-select="#form-container" mx-target="#response" style="display:inline">
                    <button class="btn danger modal-delete" type="submit"><i class="fa fa-times" aria-hidden="true"></i> Decline</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <tr>
            <td colspan="4" class="section-header" style="color: var(--chip-info);">Linked organizations</td>
          </tr>
          <?php if (empty($orgs)): ?>
            <tr class="empty no-linked"><td colspan="4">You are not linked to any organizations.</td></tr>
          <?php else: ?>
            <?php foreach ($orgs as $o): ?>
              <tr>
                <td>
                  <strong style="font-size: 0.75rem; color: var(--primary-60); text-transform: uppercase;"><?= out($o['organization']) ?></strong><br>
                  organization
                </td>
                <td>
                  <?php
                    $st = strtolower($o['org_status'] ?? 'neutral');
                    echo chip_status(ucfirst($st), $st === 'pending invite' ? 'warn' : ($st === 'active' ? 'ok' : ($st === 'suspended' ? 'finished' : 'neutral')));
                  ?>
                </td>
                <td><?= out(ucwords(str_replace('_',' ', $o['org_role'] ?? 'judge'))) ?></td>
                <td>
                  <a href="" class="btn danger" mx-post="judges/leave_organization/<?= (int)$o['org_id'] ?>" mx-on-success="#form-container" mx-target="#response" mx-select="#form-container"><i class="fa fa-chain-broken" aria-hidden="true"> </i>Unlink</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="4">
              <div style="display:flex;justify-content:space-between;align-items:center">
                <?= '<span>Active Assignments: <strong style="font-weight:bold;color:var(--text);">' . count($active_assigned) . '</strong></span><span>Pending Invitations: <strong style="font-weight:bold;color:var(--text);">' . (count($pending_invites ?? [])) . '</strong></span><span>Linked Organizations: <strong style="font-weight:bold;color:var(--text);">' . count($orgs ?? []) . '</strong></span>' ?>
              </div>
            </td></tr>
          </tfoot>
        </table>


  </section>

  <!-- ===== Profile & Organizations ===== -->
  <section>

      <h2 style="margin:0"><span>Your</span> Statistics</h2>
      <?php if (!empty($judge)): ?>
        <?= '<p>Signed in as: '.($judge->name ?? 'Unknown').'</p>' ?>
      <?php endif; ?>

  </section>

</div>