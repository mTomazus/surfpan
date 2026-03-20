<?php
/**
 * Admin: Organization detail page.
 * @var object $data['org']
 */
$org = $data['org'];
$is_active = ($org->status === 'active');
?>
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #aaa;
        text-decoration: none;
        font-size: 0.85em;
        margin: 22px 0 16px;
    }
    .back-link:hover { color: #0cf; }

    /* ── Header card ── */
    .org-header {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 22px 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .org-header h1 { margin: 0 0 6px; font-size: 1.7em; }
    .org-meta { color: #aaa; font-size: 0.88em; line-height: 1.8; }
    .org-meta span { margin-right: 18px; }
    .org-meta i { margin-right: 4px; opacity: 0.6; }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 7px;
        padding: 8px 14px;
        font-size: 0.85em;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: opacity 0.15s;
    }
    .btn-action:hover { opacity: 0.85; }
    .btn-activate   { background: rgba(0,200,100,0.2);  color: #0c6;  border: 1px solid rgba(0,200,100,0.35); }
    .btn-deactivate { background: rgba(255,80,80,0.15); color: #f66;  border: 1px solid rgba(255,80,80,0.3); }
    .btn-impersonate{ background: rgba(0,180,255,0.15); color: #0cf;  border: 1px solid rgba(0,180,255,0.3);margin: 1em 0.1em 0 0; }

    /* ── Status pill ── */
    .status-pill {
        display: inline-block;
        padding: 3px 11px;
        border-radius: 20px;
        font-size: 0.78em;
        font-weight: bold;
        text-transform: capitalize;
        margin-left: 10px;
        vertical-align: middle;
    }
    .s-active   { background: rgba(0,200,100,0.2);  color: #0c6; }
    .s-inactive { background: rgba(200,100,0,0.2);  color: #f80; }
    .s-trialing { background: rgba(0,180,255,0.2);  color: #0cf; }
    .s-past_due { background: rgba(255,80,80,0.2);  color: #f55; }
    .s-canceled { background: rgba(150,150,150,0.15); color: #888; }
    .s-none     { background: rgba(255,255,255,0.07); color: #666; }

    /* ── Stat cards ── */
    .stat-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 14px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        padding: 16px;
        text-align: center;
    }
    .stat-card .val { font-size: 2.2em; font-weight: bold; line-height: 1; margin-bottom: 5px; }
    .stat-card .lbl { font-size: 0.75em; color: #16dcae; text-transform: uppercase; letter-spacing: 0.06em; }

    /* ── Two-column layout ── */
    .detail-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    @media (max-width: 720px) { .detail-cols { grid-template-columns: 1fr; } }

    /* ── Section ── */
    .section { margin-bottom: 28px; }
    .section h2 {
        font-size: 0.78em;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #888;
        margin: 0 0 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    /* ── Info grid ── */
    .info-grid {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 6px 16px;
        font-size: 0.88em;
    }
    .info-grid .lbl { color: #777; }
    .info-grid .val { color: #ddd; word-break: break-all; }
    
    /* Subscription card */
    .sub-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 9px;
        padding: 16px;
        font-size: 0.88em;
    }
    .sub-card .sub-plan {
        font-size: 1.3em;
        font-weight: bold;
        margin-bottom: 10px;
    }
    form {
        width: auto;
    }

    .comp-status { font-size: 0.78em; font-weight: bold; text-transform: capitalize; }
    .cs-running  { color: #0cf; }
    .cs-open     { color: #8d0; }
    .cs-closed   { color: #aaa; }
    .cs-finished { color: #888; }
    .cs-generated{ color: #b7f; }
    .cs-scheduled{ color: #fc0; }
    .cs-default  { color: #999; }

    .charge-paid    { color: #0c6; }
    .charge-pending { color: #fc0; }
    .charge-failed  { color: #f55; }
    .charge-default { color: #999; }

    .empty-note { color: #555; font-style: italic; font-size: 0.85em; padding: 10px 0; }
</style>

<?php
function adm_status_pill(string $status): string {
    $map = ['active','inactive','trialing','past_due','canceled'];
    $cls = in_array($status, $map) ? 's-'.$status : 's-none';
    return '<span class="status-pill '.$cls.'">'.out($status).'</span>';
}
function comp_status_cls(string $s): string {
    $m = ['running'=>'cs-running','open'=>'cs-open','closed'=>'cs-closed',
          'finished'=>'cs-finished','generated'=>'cs-generated','scheduled'=>'cs-scheduled'];
    return $m[$s] ?? 'cs-default';
}
function charge_cls(string $s): string {
    return ['paid'=>'charge-paid','pending'=>'charge-pending','failed'=>'charge-failed'][$s] ?? 'charge-default';
}
?>

<?php if (!empty($data['flash_msg'])): ?>
<div style="background:rgba(0,200,100,0.15);border:1px solid rgba(0,200,100,0.3);color:#0c6;border-radius:7px;padding:10px 16px;margin:18px 0 0;font-size:0.9em;">
    <i class="fa fa-check-circle"></i> <?= out($data['flash_msg']) ?>
</div>
<?php endif; ?>

<a class="back-link" href="<?= BASE_URL ?>trongate_administrators/organizations_list">
    <i class="fa fa-arrow-left"></i> All Organizations
</a>

<!-- Header card -->
<div class="org-header">
    <div>
        <h1>
            <?= out($org->organization) ?>
            <?= adm_status_pill($org->status ?? 'inactive') ?>
        </h1>
        <div class="org-meta">
            <span><i class="fa fa-envelope"></i><?= out($org->email) ?></span>
            <?php if (!empty($org->phone)): ?>
            <span><i class="fa fa-phone"></i><?= out($org->phone) ?></span>
            <?php endif; ?>
            <span><i class="fa fa-globe"></i><?= out($data['country_name']) ?></span>
            <?php if (!empty($org->timezone)): ?>
            <span><i class="fa fa-clock-o"></i><?= out($org->timezone) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-actions">
        <!-- Toggle status -->
        <form method="post" action="<?= BASE_URL ?>trongate_administrators/toggle_org_status/<?= (int)$org->id ?>" style="margin:0;">
            <?php if ($is_active): ?>
                <button type="submit" class="btn-action btn-deactivate"
                        onclick="return confirm('Deactivate <?= out(addslashes($org->organization)) ?>?')">
                    <i class="fa fa-ban"></i> Deactivate
                </button>
            <?php else: ?>
                <button type="submit" class="btn-action btn-activate">
                    <i class="fa fa-check"></i> Activate
                </button>
            <?php endif; ?>
        </form>
        <!-- Grant free event -->
        <form method="post" action="<?= BASE_URL ?>trongate_administrators/grant_free_event/<?= (int)$org->id ?>" style="margin:0;">
            <button type="submit" class="btn-action" style="background:rgba(180,100,255,0.15);color:#b7f;border:1px solid rgba(180,100,255,0.3);"
                    onclick="return confirm('Grant 1 free event pass to <?= out(addslashes($org->organization)) ?>?')">
                <i class="fa fa-gift"></i> Grant Free Event
                <?php if ($data['event_pass_credits'] > 0): ?>
                    <span style="background:rgba(180,100,255,0.3);border-radius:20px;padding:1px 7px;font-size:0.85em;margin-left:4px;">
                        <?= (int)$data['event_pass_credits'] ?> credit<?= $data['event_pass_credits'] != 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </button>
        </form>
        <!-- Impersonate -->
        <a class="btn-action btn-impersonate"
           href="<?= BASE_URL ?>trongate_administrators/impersonate/<?= (int)$org->id ?>">
            <i class="fa fa-sign-in"></i> Login as
        </a>
    </div>
</div>

<!-- Stat cards -->
<div class="stat-row">
    <div class="stat-card">
        <div class="val"><?= (int)$data['total_comps'] ?></div>
        <div class="lbl">Competitions</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= (int)$data['total_participants'] ?></div>
        <div class="lbl">Participants</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= (int)$data['total_judges'] ?></div>
        <div class="lbl">Judges</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= count($data['charges']) ?></div>
        <div class="lbl">Charges</div>
    </div>
    <div class="stat-card">
        <div class="val"><?= (int)$data['event_pass_credits'] ?></div>
        <div class="lbl">Event Pass Credits</div>
    </div>
</div>

<!-- Two columns -->
<div class="detail-cols">

    <!-- LEFT: Org info + Competitions -->
    <div>
        <div class="section">
            <h2>Organization Info</h2>
            <div class="info-grid">
                <span class="lbl">ID</span>
                <span class="val"><?= (int)$org->id ?></span>

                <span class="lbl">Slug</span>
                <span class="val"><?= out($org->slug ?? '—') ?></span>

                <?php if (!empty($org->address)): ?>
                <span class="lbl">Address</span>
                <span class="val"><?= out($org->address) ?></span>
                <?php endif; ?>

                <?php if (!empty($org->company_code)): ?>
                <span class="lbl">Company code</span>
                <span class="val"><?= out($org->company_code) ?></span>
                <?php endif; ?>

                <span class="lbl">Confirmed</span>
                <span class="val"><?= ($org->confirmed ?? 0) ? '<span style="color:#0c6;">Yes</span>' : '<span style="color:#f80;">No</span>' ?></span>
            </div>
        </div>

        <div class="section">
            <h2>Competitions (last 10)</h2>
            <?php if (!empty($data['competitions'])): ?>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Year</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['competitions'] as $comp): ?>
                    <tr>
                        <td><?= out($comp->name) ?>
                            <?php if (!empty($comp->location)): ?>
                            <div style="color:#666;font-size:0.8em;"><?= out($comp->location) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="color:#888;"><?= out($comp->year ?? '') ?></td>
                        <td><span class="comp-status <?= comp_status_cls($comp->status ?? '') ?>">
                            <?= out($comp->status ?? '—') ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="empty-note">No competitions yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT: Subscription + Charges -->
    <div>
        <div class="section">
            <h2>Subscription</h2>
            <?php if ($data['subscription']): ?>
            <?php $sub = $data['subscription']; ?>
            <div class="sub-card">
                <div class="sub-plan">
                    <?= out(strtoupper($sub->product_code ?? 'Unknown plan')) ?>
                    <?= adm_status_pill($sub->status ?? 'unknown') ?>
                </div>
                <div class="info-grid">
                    <span class="lbl">Period start</span>
                    <span class="val"><?= out($sub->current_period_start ?? '—') ?></span>

                    <span class="lbl">Period end</span>
                    <span class="val"><?= out($sub->current_period_end ?? '—') ?></span>

                    <?php if (!empty($sub->period_months)): ?>
                    <span class="lbl">Term</span>
                    <span class="val"><?= (int)$sub->period_months ?> month<?= $sub->period_months != 1 ? 's' : '' ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
                <div class="sub-card">
                    <div class="sub-plan" style="color:#888;">Starter (Free)</div>
                    <div style="color:#666;font-size:0.85em;">No paid subscription on record.</div>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Recent Charges</h2>
            <?php if (!empty($data['charges'])): ?>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['charges'] as $charge): ?>
                    <tr>
                        <td style="color:#555;"><?= (int)$charge->id ?></td>
                        <td><?= out($charge->product ?? '—') ?></td>
                        <td>
                            <?= strtoupper($charge->currency ?? 'EUR') ?>
                            <?= number_format((float)($charge->amount ?? 0), 2) ?>
                        </td>
                        <td><span class="<?= charge_cls($charge->status ?? '') ?>">
                            <?= out($charge->status ?? '—') ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="empty-note">No billing charges on record.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
