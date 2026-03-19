<?php
/**
 * Admin: Global user search — participants and judges.
 */
$q         = $data['q'] ?? '';
$searching = strlen($q) >= 2;
$participants = $data['participants'] ?? [];
$judges       = $data['judges'] ?? [];
$total        = count($participants) + count($judges);
?>
<style>
    .page-header { margin: 28px 0 20px; }
    .page-header h1 { margin: 0 0 4px; font-size: 1.8em; }
    .page-header p  { margin: 0; color: #aaa; font-size: 0.9em; }

    .search-form {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .search-input {
        flex: 1;
        min-width: 240px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 8px;
        color: #eee;
        padding: 10px 16px;
        font-size: 1em;
        outline: none;
        transition: border-color 0.15s;
    }
    .search-input:focus { border-color: rgba(0,200,255,0.5); }
    .search-input::placeholder { color: #666; }

    .btn-search {
        background: rgba(0,180,255,0.2);
        color: #0cf;
        border: 1px solid rgba(0,180,255,0.35);
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 0.95em;
        cursor: pointer;
        font-weight: 600;
    }
    .btn-search:hover { background: rgba(0,180,255,0.35); }

    .results-summary {
        margin-bottom: 20px;
        font-size: 0.9em;
        color: #888;
    }
    .results-summary strong { color: #eee; }

    .section { margin-bottom: 32px; }
    .section-title {
        font-size: 0.78em;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #888;
        margin: 0 0 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-count {
        background: rgba(255,255,255,0.08);
        color: #777;
        border-radius: 20px;
        padding: 1px 8px;
        font-size: 0.9em;
        font-weight: normal;
        text-transform: none;
        letter-spacing: 0;
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88em;
    }
    .user-table th {
        background: hsla(200, 100%, 50%, 1);
        color: #fff;
        font-size: 0.76em;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        padding: 10px 14px;
        text-align: left;
        font-weight: 600;
    }
    .user-table td {
        background: hsl(200, 35%, 20%);
        padding: 9px 12px;
        border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 0.9em;
    }
    .user-table tr:nth-child(odd) td {
        background: hsl(200, 35%, 30%);
    }
    .user-table tr:last-child td { border-bottom: none; }
    .user-table tr:hover td { background: hsl(200, 35%, 10%); }

    .user-name { font-weight: 600; color: #eee; }
    .user-email { color: #eee; font-size: 0.85em; }
    .user-phone { color: #eee; font-size: 0.82em; }

    .role-pill {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .role-participant { background: rgba(0,200,100,0.15); color: #0c6; }
    .role-judge       { background: rgba(180,100,255,0.15); color: #b7f; }

    .org-link { color: #0cf; text-decoration: none; font-size: 0.85em; }
    .org-link:hover { text-decoration: underline; }

    .comp-list { color: #eee; font-size: 0.82em; margin-top: 2px; }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #555;
    }
    .empty-state .icon { font-size: 2.5em; margin-bottom: 10px; opacity: 0.4; }
    .empty-state p { margin: 4px 0; }

    .hint { color: #555; font-size: 0.85em; margin-top: 6px; }

    mark {
        background: rgba(0,200,255,0.25);
        color: #fff;
        border-radius: 2px;
        padding: 0 2px;
    }
</style>

<div class="page-header">
    <h1>User Search</h1>
    <p>Search participants and judges across all organizations</p>
</div>

<form class="search-form" method="get" action="<?= BASE_URL ?>trongate_administrators/user_search">
    <input class="search-input" type="text" name="q" value="<?= htmlspecialchars($q) ?>"
           placeholder="Name or email address…" autofocus autocomplete="off">
    <button class="btn-search" type="submit">
        <i class="fa fa-search"></i> Search
    </button>
</form>

<?php if (!$searching && $q === ''): ?>
    <div class="empty-state">
        <div class="icon"><i class="fa fa-search"></i></div>
        <p>Enter a name or email above to search.</p>
        <p class="hint">Searches participants and judges platform-wide.</p>
    </div>

<?php elseif ($searching && $total === 0): ?>
    <div class="empty-state">
        <div class="icon"><i class="fa fa-user-times"></i></div>
        <p>No users found for <strong style="color:#eee;">"<?= htmlspecialchars($q) ?>"</strong></p>
        <p class="hint">Try a partial name or check the email spelling.</p>
    </div>

<?php else: ?>

<?php
// Highlight search term in a string
function hl(string $text, string $q): string {
    if ($q === '') return htmlspecialchars($text);
    return preg_replace(
        '/(' . preg_quote(htmlspecialchars($q), '/') . ')/i',
        '<mark>$1</mark>',
        htmlspecialchars($text)
    );
}
?>

<div class="results-summary">
    Found <strong><?= $total ?></strong> result<?= $total !== 1 ? 's' : '' ?>
    for "<strong><?= htmlspecialchars($q) ?></strong>"
    <?php if (count($participants)): ?> — <?= count($participants) ?> participant<?= count($participants) !== 1 ? 's' : '' ?><?php endif; ?>
    <?php if (count($judges)): ?> — <?= count($judges) ?> judge<?= count($judges) !== 1 ? 's' : '' ?><?php endif; ?>
</div>

<!-- Participants -->
<?php if (!empty($participants)): ?>
<div class="section">
    <div class="section-title">
        <span class="role-pill role-participant">Participants</span>
        <span class="section-count"><?= count($participants) ?></span>
    </div>
    <table class="user-table">
        <thead>
            <tr>
                <th>Name / Email</th>
                <th>Phone</th>
                <th>Competitions</th>
                <th>Organization</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $u): ?>
            <tr>
                <td>
                    <div class="user-name"><?= hl($u->name ?? '—', $q) ?></div>
                    <div class="user-email"><?= hl($u->email ?? '', $q) ?></div>
                </td>
                <td class="user-phone"><?= htmlspecialchars($u->phone ?? '—') ?></td>
                <td>
                    <?php if ($u->comp_count > 0): ?>
                        <span style="color:#aaa;"><?= (int)$u->comp_count ?> comp<?= $u->comp_count != 1 ? 's' : '' ?></span>
                        <?php if (!empty($u->recent_comps)): ?>
                        <div class="comp-list"><?= htmlspecialchars($u->recent_comps) ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#555;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($u->org_id)): ?>
                        <a class="org-link" href="<?= BASE_URL ?>trongate_administrators/org_detail/<?= (int)$u->org_id ?>">
                            <?= htmlspecialchars($u->org_name ?? '—') ?>
                        </a>
                    <?php else: ?>
                        <span style="color:#555;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Judges -->
<?php if (!empty($judges)): ?>
<div class="section">
    <div class="section-title">
        <span class="role-pill role-judge">Judges</span>
        <span class="section-count"><?= count($judges) ?></span>
    </div>
    <table class="user-table">
        <thead>
            <tr>
                <th>Name / Email</th>
                <th>Phone</th>
                <th>Username</th>
                <th>Organizations</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($judges as $j): ?>
            <tr>
                <td>
                    <div class="user-name"><?= hl($j->name ?? '—', $q) ?></div>
                    <div class="user-email"><?= hl($j->email ?? '', $q) ?></div>
                </td>
                <td class="user-phone"><?= htmlspecialchars($j->phone ?? '—') ?></td>
                <td style="color:#888;font-size:0.85em;"><?= htmlspecialchars($j->username ?? '—') ?></td>
                <td>
                    <?php if ($j->org_count > 0): ?>
                        <span style="color:#aaa;font-size:0.85em;"><?= htmlspecialchars($j->orgs ?? '') ?></span>
                    <?php else: ?>
                        <span style="color:#555;">No org assigned</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php endif; ?>
