<?php
/**
 * Platform overview dashboard for SurfPan administrators.
 */
?>
<style>
    .dash-header {
        margin: 28px 0 20px;
    }
    .dash-header h1 {
        margin: 0 0 4px;
        font-size: 1.8em;
    }
    .dash-header p {
        margin: 0;
        color: #aaa;
        font-size: 0.95em;
    }

    /* Section tables */
    .dash-section {
        margin-bottom: 32px;
    }
    .dash-section h2 {
        font-size: 1.1em;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #aaa;
        margin: 0 0 10px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 6px;
    }
    .status-pill {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 0.78em;
        font-weight: bold;
        text-transform: capitalize;
    }
    .status-active   { background: rgba(0,200,100,0.2); color: #0c6; }
    .status-inactive { background: rgba(200,100,0,0.2); color: #f80; }
    .status-running  { background: rgba(0,180,255,0.2); color: #0cf; }
    .status-open     { background: rgba(100,200,0,0.2); color: #8d0; }
    .status-closed   { background: rgba(150,150,150,0.2); color: #aaa; }
    .status-finished { background: rgba(150,150,150,0.15); color: #999; }
    .status-generated { background: rgba(180,100,255,0.2); color: #b7f; }
    .status-scheduled { background: rgba(255,200,0,0.2); color: #fc0; }
    .status-default  { background: rgba(255,255,255,0.1); color: #ccc; }

    .dash-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    @media (max-width: 700px) {
        .dash-two-col { grid-template-columns: 1fr; }
        .stat-grid { grid-template-columns: repeat(2, 1fr); }
    }

</style>

<?php
// Helper to render a status pill
function dash_status_pill(string $status): string {
    $known = ['active','inactive','running','open','closed','finished','generated','scheduled'];
    $cls = in_array($status, $known) ? 'status-'.$status : 'status-default';
    return '<span class="status-pill '.$cls.'">'.out($status).'</span>';
}
?>

<div class="dash-header">
    <h1>Platform Dashboard</h1>
    <p>SurfPan — overview as of <?= date('d M Y') ?></p>
</div>

<!-- Stat cards -->
<div class="stat-row">
    <div class="stat-card">
        <div class="stat val"><?= (int)$data['total_orgs'] ?></div>
        <div class="stat lbl">Organizations</div>
        <?php if ($data['active_orgs'] > 0): ?>
            <span class="stat badge"><?= (int)$data['active_orgs'] ?> active</span>
        <?php endif; ?>
    </div>

    <div class="stat-card<?= $data['live_comps'] > 0 ? ' live' : '' ?>">
        <div class="stat val"><?= (int)$data['total_comps'] ?></div>
        <div class="stat lbl">Competitions</div>
        <?php if ($data['live_comps'] > 0): ?>
            <span class="stat badge"><?= (int)$data['live_comps'] ?> live</span>
        <?php endif; ?>
    </div>

    <div class="stat-card">
        <div class="stat val"><?= (int)$data['total_users'] ?></div>
        <div class="stat lbl">Participants</div>
    </div>

    <div class="stat-card">
        <div class="stat val"><?= (int)$data['total_judges'] ?></div>
        <div class="stat lbl">Judges</div>
    </div>

    <div class="stat-card money">
        <div class="stat val">€<?= number_format((float)$data['total_revenue'], 0) ?></div>
        <div class="stat lbl">Total Revenue</div>
    </div>
</div>

<!-- Two-column section: recent orgs + recent competitions -->
<div class="dash-two-col">

    <div class="dash-section">
        <h2>Recent Organizations</h2>
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['recent_orgs'])): ?>
                    <?php foreach ($data['recent_orgs'] as $org): ?>
                        <tr>
                            <td>
                                <div><?= out($org->organization) ?></div>
                                <div style="color:#888;font-size:0.8em;"><?= out($org->email) ?></div>
                            </td>
                            <td><?= dash_status_pill($org->status ?? 'inactive') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-row"><td colspan="2">No organizations yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="dash-section">
        <h2>Recent Competitions</h2>
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Competition</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['recent_comps'])): ?>
                    <?php foreach ($data['recent_comps'] as $comp): ?>
                        <tr>
                            <td><?= out($comp->name . ' ' . $comp->year) ?></td>
                            <td><?= dash_status_pill($comp->status ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-row"><td colspan="2">No competitions yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
