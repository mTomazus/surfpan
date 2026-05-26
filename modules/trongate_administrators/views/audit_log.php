<?php
$action_labels = [
    'impersonate_start'  => 'Impersonate start',
    'impersonate_stop'   => 'Impersonate stop',
    'toggle_org_status'  => 'Toggle org status',
    'grant_free_event'   => 'Grant free event',
    'delete_admin'       => 'Delete admin',
    'create_admin'       => 'Create admin',
    'update_admin'       => 'Update admin',
];
?>
<style>
    .audit-header { margin: 28px 0 20px; }
    .audit-header h1 { margin: 0 0 4px; font-size: 1.8em; }
    .audit-header p { margin: 0; color: #aaa; font-size: 0.95em; }
    .action-pill {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 0.76em;
        font-weight: bold;
        background: rgba(255,255,255,0.08);
        color: #ccc;
    }
    .action-pill.impersonate { background: rgba(255,160,0,0.18); color: #fa0; }
    .action-pill.delete      { background: rgba(255,80,80,0.18);  color: #f55; }
    .action-pill.grant       { background: rgba(0,200,100,0.18);  color: #0c6; }
    .action-pill.toggle      { background: rgba(0,180,255,0.18);  color: #0cf; }
    .mono { font-family: monospace; font-size: 0.88em; }
</style>

<div class="audit-header">
    <h1>Admin Audit Log</h1>
    <p>Last 200 events — most recent first</p>
</div>

<?php if (empty($data['entries'])): ?>
    <p style="color:#aaa;text-align:center;margin-top:3rem">No audit events recorded yet.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Time (UTC)</th>
            <th>Admin</th>
            <th>Action</th>
            <th>Target</th>
            <th>Note</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['entries'] as $e): ?>
        <?php
            $action = $e->action ?? '';
            $label  = $action_labels[$action] ?? $action;
            $pill_class = 'action-pill';
            if (str_contains($action, 'impersonate')) $pill_class .= ' impersonate';
            elseif (str_contains($action, 'delete'))  $pill_class .= ' delete';
            elseif (str_contains($action, 'grant'))   $pill_class .= ' grant';
            elseif (str_contains($action, 'toggle'))  $pill_class .= ' toggle';
        ?>
        <tr>
            <td class="mono" style="white-space:nowrap"><?= date('Y-m-d H:i:s', (int)$e->created_at) ?></td>
            <td><?= out($e->admin_username ?? '—') ?></td>
            <td><span class="<?= $pill_class ?>"><?= out($label) ?></span></td>
            <td class="mono"><?= $e->target_id ? '#' . (int)$e->target_id : '—' ?></td>
            <td style="max-width:260px;word-break:break-word"><?= out($e->note ?? '') ?></td>
            <td class="mono"><?= out($e->ip ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
