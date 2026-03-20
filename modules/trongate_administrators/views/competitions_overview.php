<?php
/**
 * Admin: Competition overview — all competitions across all organizations.
 */
?>
<style>
    .page-header { margin: 28px 0 20px; }
    .page-header h1 { margin: 0 0 4px; font-size: 1.8em; }
    .page-header p  { margin: 0; color: #aaa; font-size: 0.9em; }

    .filter-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        align-items: center;
    }
    .filter-bar input {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 6px;
        color: #eee;
        padding: 7px 12px;
        font-size: 0.9em;
        width: 240px;
        outline: none;
    }
    .filter-bar input::placeholder { color: #777; }
    .filter-bar input:focus { border-color: rgba(0,200,255,0.5); }
    .filter-bar select {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 6px;
        color: #eee;
        padding: 7px 12px;
        font-size: 0.9em;
        outline: none;
        cursor: pointer;
    }
    .filter-bar select option { background: #222; color: #eee; }

    .count-badge {
        display: inline-block;
        background: rgba(255,255,255,0.08);
        color: #999;
        border-radius: 20px;
        padding: 2px 9px;
        font-size: 0.78em;
        margin-left: 8px;
        vertical-align: middle;
    }

    .org-link { color: #0cf; text-decoration: none; }
    .org-link:hover { text-decoration: underline; }

    .empty-state {
        text-align: center;
        padding: 48px;
        color: #666;
        font-style: italic;
    }

    @media (max-width: 700px) {
        .comp-table th:nth-child(4),
        .comp-table td:nth-child(4),
        .comp-table th:nth-child(6),
        .comp-table td:nth-child(6) { display: none; }
        .stat-row { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<?php
function comp_pill(string $status): string {
    $cls = match($status) {
        'running'   => 'status-running',
        'finished'  => 'status-finished',
        'scheduled' => 'status-scheduled',
        'generated' => 'status-generated',
        'created'   => 'status-created',
        default     => 'status-default',
    };
    return '<span class="status-pill '.$cls.'">'.out($status).'</span>';
}
$s = $data['stats'];
?>

<div class="page-header">
    <h1>Competitions Overview</h1>
    <p>All active competitions across all organizations</p>
</div>

<!-- Summary stats -->
<div class="stat-row">
    <div class="stat-card">
        <div class="val"><?= (int)$s->total ?></div>
        <div class="lbl">Total</div>
    </div>
    <div class="stat-card live">
        <div class="val"><?= (int)$s->running ?></div>
        <div class="lbl">Running</div>
    </div>
    <div class="stat-card next">
        <div class="val"><?= (int)$s->upcoming ?></div>
        <div class="lbl">Upcoming</div>
    </div>
    <div class="stat-card done">
        <div class="val"><?= (int)$s->finished ?></div>
        <div class="lbl">Finished</div>
    </div>
    <div class="stat-card draft">
        <div class="val"><?= (int)$s->draft ?></div>
        <div class="lbl">Draft</div>
    </div>
    <div class="stat-card draft">
        <div class="val"><?= (int)$s->archived ?></div>
        <div class="lbl">Archived</div>
    </div>
</div>

<!-- Filters -->
<div class="filter-bar">
    <input type="text" id="comp-search" placeholder="Filter by name, org or location…" oninput="filterComps()">
    <select id="status-filter" onchange="filterComps()">
        <option value="">All statuses</option>
        <option value="running">Running</option>
        <option value="scheduled">Scheduled</option>
        <option value="generated">Generated</option>
        <option value="finished">Finished</option>
        <option value="created">Draft</option>
    </select>
</div>

<?php if (!empty($data['competitions'])): ?>
<p style="color:#666;font-size:0.83em;margin:0 0 8px;">
    Showing <span id="visible-count"><?= count($data['competitions']) ?></span> of <?= count($data['competitions']) ?> competitions
    <span class="count-badge" id="total-badge"><?= count($data['competitions']) ?></span>
</p>
<table class="comp-table" id="comp-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Competition</th>
            <th>Organization</th>
            <th>Location</th>
            <th>Participants</th>
            <th>Heats</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['competitions'] as $c): ?>
        <tr data-status="<?= out($c->status ?? '') ?>">
            <td style="color:#555;"><?= (int)$c->id ?></td>
            <td>
                <div style="font-weight:600;color:#eee;"><?= out($c->name) ?></div>
                <?php if (!empty($c->year)): ?>
                    <div style="color:#777;font-size:0.83em;"><?= out($c->year) ?></div>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($c->org_id): ?>
                    <a class="org-link" href="<?= BASE_URL ?>trongate_administrators/org_detail/<?= (int)$c->org_id ?>">
                        <?= out($c->org_name ?? '—') ?>
                    </a>
                <?php else: ?>
                    <span style="color:#555;">—</span>
                <?php endif; ?>
            </td>
            <td style="color:#999;"><?= out($c->location ?? '—') ?></td>
            <td style="color:#bbb;text-align:center;"><?= (int)$c->participant_count ?: '—' ?></td>
            <td style="color:#bbb;text-align:center;"><?= (int)$c->heat_count ?: '—' ?></td>
            <td><?= comp_pill($c->status ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <div class="empty-state">No active competitions found.</div>
<?php endif; ?>

<script>
function filterComps() {
    var q      = document.getElementById('comp-search').value.toLowerCase();
    var status = document.getElementById('status-filter').value.toLowerCase();
    var rows   = document.querySelectorAll('#comp-table tbody tr');
    var visible = 0;
    rows.forEach(function(row) {
        var text   = row.textContent.toLowerCase();
        var rstat  = (row.getAttribute('data-status') || '').toLowerCase();
        var show   = (!q || text.includes(q)) && (!status || rstat === status);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var el = document.getElementById('visible-count');
    if (el) el.textContent = visible;
}
</script>
