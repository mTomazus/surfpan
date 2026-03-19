<?php
/**
 * Admin: Billing overview — competition charges and free event passes.
 */
?>
<style>
    .page-header { margin: 28px 0 20px; }
    .page-header h1 { margin: 0 0 4px; font-size: 1.8em; }
    .page-header p  { margin: 0; color: #aaa; font-size: 0.9em; }

    .stat-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 14px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        padding: 18px 16px;
        text-align: center;
    }
    .stat-card .val { font-size: 2.2em; font-weight: bold; line-height: 1; margin-bottom: 5px; }
    .stat-card .lbl { font-size: 0.75em; color: #aaa; text-transform: uppercase; letter-spacing: 0.06em; }
    .stat-card.money .val { color: #0f9; }
    .stat-card.warn  .val { color: #fc0; }
    .stat-card.info  .val { color: #0cf; }

    .section { margin-bottom: 36px; }
    .section h2 {
        font-size: 0.82em;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #888;
        margin: 0 0 12px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .section h2 span { font-size: 0.9em; color: #555; font-weight: normal; text-transform: none; letter-spacing: 0; }

    .billing-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88em;
    }
    .billing-table th {
        background: rgba(255,255,255,0.07);
        color: #888;
        font-size: 0.76em;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 9px 12px;
        text-align: left;
        font-weight: 600;
    }
    .billing-table td {
        padding: 9px 12px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        color: #ccc;
        vertical-align: middle;
    }
    .billing-table tr:last-child td { border-bottom: none; }
    .billing-table tr:hover td { background: rgba(255,255,255,0.03); }

    .org-link { color: #0cf; text-decoration: none; }
    .org-link:hover { text-decoration: underline; }

    .pill {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 0.76em;
        font-weight: bold;
    }
    .pill-paid    { background: rgba(0,200,100,0.18); color: #0c6; }
    .pill-pending { background: rgba(255,200,0,0.18); color: #fc0; }
    .pill-failed  { background: rgba(255,80,80,0.18); color: #f55; }
    .pill-default { background: rgba(255,255,255,0.08); color: #888; }

    .tier-badge {
        background: rgba(255,255,255,0.07);
        color: #999;
        border-radius: 4px;
        padding: 1px 6px;
        font-size: 0.78em;
    }

    .empty-note { color: #fff; font-style: italic; padding: 20px 12px; }
</style>

<?php
function bill_pill(string $status): string {
    $cls = match($status) {
        'paid'    => 'pill-paid',
        'pending' => 'pill-pending',
        'failed'  => 'pill-failed',
        default   => 'pill-default',
    };
    return '<span class="pill '.$cls.'">'.htmlspecialchars($status).'</span>';
}
?>

<div class="page-header">
    <h1>Billing Overview</h1>
    <p>Competition charges and free event passes across all organizations</p>
</div>

<!-- Summary -->
<div class="stat-row">
    <div class="stat-card money">
        <div class="val">€<?= number_format($data['total_collected'], 0) ?></div>
        <div class="lbl">Total Collected</div>
    </div>
    <div class="stat-card warn">
        <div class="val">€<?= number_format($data['total_pending'], 0) ?></div>
        <div class="lbl">Pending</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= (int)$data['paid_comps'] ?></div>
        <div class="lbl">Paid Events</div>
    </div>
    <div class="stat-card info">
        <div class="val"><?= (int)$data['free_events_granted'] ?></div>
        <div class="lbl">Free Events Granted</div>
    </div>
</div>

<!-- Competition charges -->
<div class="section">
    <h2>
        Competition Charges
        <span><?= count($data['charges']) ?> records (last 100)</span>
    </h2>
    <?php if (!empty($data['charges'])): ?>
    <table class="billing-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Organization</th>
                <th>Competition</th>
                <th>Participants</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['charges'] as $c): ?>
            <tr>
                <td style="color:#555;"><?= (int)$c->id ?></td>
                <td>
                    <?php if ($c->org_id): ?>
                    <a class="org-link" href="<?= BASE_URL ?>trongate_administrators/org_detail/<?= (int)$c->org_id ?>">
                        <?= htmlspecialchars($c->org_name ?? '—') ?>
                    </a>
                    <?php else: ?>
                        <span style="color:#666;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($c->comp_name): ?>
                        <?= htmlspecialchars($c->comp_name) ?>
                        <?php if ($c->comp_year): ?>
                            <span style="color:#666;font-size:0.85em;"><?= htmlspecialchars($c->comp_year) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#555;">—</span>
                    <?php endif; ?>
                </td>
                <td style="color:#999;"><?= $c->participants_count ? (int)$c->participants_count : '—' ?></td>
                <td style="font-weight:600;">
                    <?php if ($c->amount > 0): ?>
                        <?= strtoupper($c->currency ?? 'EUR') ?> <?= number_format((float)$c->amount, 2) ?>
                    <?php else: ?>
                        <span style="color:#0c6;">Free</span>
                    <?php endif; ?>
                </td>
                <td><?= bill_pill($c->status ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="empty-note">No competition charges recorded yet.</p>
    <?php endif; ?>
</div>

<!-- Free event passes granted by admin -->
<div class="section">
    <h2>
        Free Event Passes Granted
        <span><?= count($data['event_passes']) ?> records</span>
    </h2>
    <?php if (!empty($data['event_passes'])): ?>
    <table class="billing-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Organization</th>
                <th>Qty</th>
                <th>Used In</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['event_passes'] as $ep): ?>
            <tr>
                <td style="color:#555;"><?= (int)$ep->id ?></td>
                <td>
                    <?php if ($ep->org_id): ?>
                    <a class="org-link" href="<?= BASE_URL ?>trongate_administrators/org_detail/<?= (int)$ep->org_id ?>">
                        <?= htmlspecialchars($ep->org_name ?? '—') ?>
                    </a>
                    <?php else: ?>
                        <span style="color:#666;">—</span>
                    <?php endif; ?>
                </td>
                <td><?= (int)($ep->quantity ?? 1) ?></td>
                <td>
                    <?php if (!empty($ep->used_in_comp_name)): ?>
                        <span style="color:#aaa;font-size:0.88em;"><?= htmlspecialchars($ep->used_in_comp_name) ?></span>
                    <?php else: ?>
                        <span style="color:#0c6;font-size:0.85em;"><i class="fa fa-circle"></i> Available</span>
                    <?php endif; ?>
                </td>
                <td><?= bill_pill($ep->status ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="empty-note">No free event passes granted yet.</p>
    <?php endif; ?>
</div>
