<?php
/**
 * Admin: All organizations with impersonate action.
 */
?>
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 28px 0 20px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .page-header h1 { margin: 0; font-size: 1.8em; }
    .page-header p  { margin: 0; color: #aaa; font-size: 0.9em; }

    .search-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }
    .search-bar input {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 6px;
        color: #eee;
        padding: 7px 12px;
        font-size: 0.9em;
        width: 260px;
        outline: none;
    }
    .search-bar input::placeholder { color: #777; }
    .search-bar input:focus { border-color: rgba(0,200,255,0.5); }

    .s-active    { background: rgba(0,200,100,0.2);  color: #0c6; }
    .s-inactive  { background: rgba(200,100,0,0.2);  color: #f80; }
    .s-trialing  { background: rgba(0,180,255,0.2);  color: #0cf; }
    .s-past_due  { background: rgba(255,50,50,0.2);  color: #f55; }
    .s-canceled  { background: rgba(150,150,150,0.15); color: #888; }
    .s-none      { background: rgba(255,255,255,0.07); color: #666; }

    .btn-impersonate {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(0,180,255,0.15);
        color: #0cf;
        border: 1px solid rgba(0,180,255,0.3);
        border-radius: 6px;
        padding: 5px 11px;
        font-size: 0.8em;
        text-decoration: none;
        transition: background 0.15s;
        white-space: nowrap;
    }
    .btn-impersonate:hover {
        background: rgba(0,180,255,0.3);
        color: #fff;
    }

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

    .empty-state {
        text-align: center;
        padding: 48px;
        color: #666;
        font-style: italic;
    }

    @media (max-width: 700px) {
        .orgs-table th:nth-child(3),
        .orgs-table td:nth-child(3),
        .orgs-table th:nth-child(4),
        .orgs-table td:nth-child(4) { display: none; }
    }
</style>

<?php
function org_status_pill(string $status, string $prefix = 's'): string {
    $map = ['active' => 'active', 'inactive' => 'inactive', 'trialing' => 'trialing',
            'past_due' => 'past_due', 'canceled' => 'canceled'];
    $cls = $prefix . '-' . ($map[$status] ?? 'none');
    return '<span class="status-pill ' . $cls . '">' . htmlspecialchars($status ?: '—') . '</span>';
}
?>

<div class="page-header">
    <div>
        <h1>Organizations <span class="count-badge"><?= count($data['orgs']) ?></span></h1>
        <p>All registered organizations on the platform</p>
    </div>
</div>

<div class="search-bar">
    <input type="text" id="org-search" placeholder="Filter by name or email…" onkeyup="filterOrgs(this.value)">
</div>

<?php if (!empty($data['orgs'])): ?>
<table class="orgs-table" id="orgs-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Organization</th>
            <th>Email</th>
            <th>Country</th>
            <th>Status</th>
            <th>Subscription</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['orgs'] as $org): ?>
        <tr>
            <td class="org-id"><?= (int)$org->id ?></td>
            <td>
                <a href="<?= BASE_URL ?>trongate_administrators/org_detail/<?= (int)$org->id ?>"
                   style="color:#eee;text-decoration:none;">
                    <?= htmlspecialchars($org->organization) ?>
                </a>
            </td>
            <td style="color:#aaa;"><?= htmlspecialchars($org->email) ?></td>
            <td style="color:#aaa;"><?= htmlspecialchars(strtoupper($org->country ?? '—')) ?></td>
            <td><?= org_status_pill($org->org_status ?? 'inactive') ?></td>
            <td><?= org_status_pill($org->sub_status ?? 'none') ?></td>
            <td style="display:flex;gap:6px;align-items:center;">
                <a class="btn-impersonate"
                   href="<?= BASE_URL ?>trongate_administrators/org_detail/<?= (int)$org->id ?>"
                   style="background:rgba(255,255,255,0.08);color:#ccc;border-color:rgba(255,255,255,0.15);">
                    <i class="fa fa-eye"></i>
                </a>
                <a class="btn-impersonate"
                   href="<?= BASE_URL ?>trongate_administrators/impersonate/<?= (int)$org->id ?>">
                    <i class="fa fa-sign-in"></i> Login as
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <div class="empty-state">No organizations registered yet.</div>
<?php endif; ?>

<script>
function filterOrgs(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#orgs-table tbody tr').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
