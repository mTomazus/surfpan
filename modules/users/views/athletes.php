<?php
/**
 * Public athletes listing — /users/athletes
 * $athletes: objects (id, name, slug, country, country_name, club_name, avatar, comp_count, total_points, ranking)
 */
?>
<style>
  .athletes-hero { text-align: center; padding: 2.5rem 1rem 1.5rem; }
  .athletes-hero h1 { font-size: 2rem; margin-bottom: 0.25rem; }
  .athletes-hero p  { color: var(--text-light); margin: 0; }

  .athletes-wrap { max-width: 1000px; margin: 0 auto; padding: 0 1rem 4rem; }

  .athletes-search {
    max-width: 420px;
    margin: 1.5rem auto 1.5rem;
    position: relative;
  }
  .athletes-search input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    background: var(--bg);
    color: var(--text);
    font-size: 0.95rem;
    box-sizing: border-box;
    text-align: center;
  }
  .athletes-search .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.45;
    pointer-events: none;
  }

  .athletes-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);}
  .athletes-table th {
    text-align: left;
    padding: 0.5rem 0.75rem;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-light);
    border-bottom: 1px solid var(--border);
    background: var(--primary-20);
    white-space: nowrap;
  }
  .athletes-table td {
    padding: 0.65rem 0.75rem;
    vertical-align: middle;
  }
  .athletes-table tr { border-bottom: 1px solid var(--border); align-items: center; }
  .athletes-table tr td:nth-child(3) { color: var(--ok); }
  .athletes-table tr td:nth-child(5) { color: var(--primary); font-family: 'Lato', sans-serif; }
  .athletes-table tr:last-child td { border-bottom: none; }
  .athletes-table tr:hover td { background: var(--bg-hover, rgba(255,255,255,.04)); }

  .rank-cell {
    font-size: 0.95rem;
    font-weight: 700;
    text-align: center;
    color: var(--text-light);
    width: 48px;
  }
  .rank-cell.top1 { color: #ffd700;font-size: 1.35rem;}
  .rank-cell.top2 { color: #c0c0c0;font-size: 1.3rem; }
  .rank-cell.top3 { color: #cd7f32;font-size: 1.25rem; }

  .athlete-cell { display: flex; align-items: center; gap: 0.75rem; }
  .a-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 700; overflow: hidden; flex-shrink: 0;
  }
  .a-avatar img { width: 100%; height: 100%; object-fit: cover; }
  .a-name  { font-family: 'Lato', sans-serif; font-weight: 600; font-size: 0.9rem; }
  .a-club  { font-family: 'Lato', sans-serif; font-size: 0.75rem; color: var(--text-light); margin-top: 1px; }

  .points-cell { font-weight: 700; font-variant-numeric: tabular-nums; }
  .points-zero { color: var(--text-light); font-weight: 400; }

  .btn-profile {
    display: inline-block;
    padding: 0.3rem 0.85rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    font-size: 0.78rem;
    text-decoration: none;
    color: inherit;
    white-space: nowrap;
    transition: background .15s, border-color .15s;
  }
  .btn-profile:hover { background: var(--primary-20); border-color: var(--primary-60); }
  .tg-showing-statement { font-size: 0.75rem; background: var(--primary-20); padding: 0.5rem; }
  .pagination { font-size: 0.75rem; float: right;}
  .pagination a { color: var(--text-light); transition: all 0.15s; }
  .pagination a.active { color: var(--bg); font-weight: 700; background: var(--primary-60); }
  .pagination a:hover { color: var(--bg); background: var(--primary-60); }
  .no-results { text-align: center; padding: 3rem; color: var(--text-light); display: none; }
</style>

<h2>Athletes <span>List</span></h2>
<p>Browse all registered competitors on Surf Panel</p>

<div class="athletes-wrap">
  <form class="athletes-search field" style="background: var(--bg);" method="GET" action="">
    <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
    <input type="text" name="q" id="athlete-search" placeholder="Search by name, club or country…"
           value="<?= out($search_query ?? '') ?>" oninput="filterAthletes(this.value)" autocomplete="off">
  </form>

  <div style="overflow-x:auto">
    <table class="athletes-table">
      <thead>
        <tr>
          <th style="text-align:center">Rank</th>
          <th>Athlete</th>
          <th>Country</th>
          <th style="text-align:center">Events</th>
          <th style="text-align:center">Points</th>
          <th style="text-align:center">Action</th>
        </tr>
      </thead>
      <tbody id="athletes-tbody">
        <?php foreach ($athletes as $a):
          $rank    = (int)$a->ranking;
          $initial = mb_strtoupper(mb_substr($a->name ?? '?', 0, 1));
          $rankClass = $rank === 1 ? 'top1' : ($rank === 2 ? 'top2' : ($rank === 3 ? 'top3' : ''));
          $pts = (int)$a->total_points;
        ?>
        <tr data-search="<?= strtolower(out($a->name) . ' ' . ($a->club_name ?? '') . ' ' . ($a->country_name ?? '') . ' ' . ($a->country ?? '')) ?>">
          <td class="rank-cell <?= $rankClass ?>"><?= $rank > 0 ? '#' . $rank : '—' ?></td>
          <td>
            <div class="athlete-cell">
              <div class="a-avatar">
                <?php if (!empty($a->avatar)): ?>
                  <img src="users_module/images/avatars/<?= out($a->avatar) ?>" alt="<?= out($a->name) ?>">
                <?php else: ?>
                  <?= $initial ?>
                <?php endif; ?>
              </div>
              <div>
                <div class="a-name"><?= out($a->name) ?></div>
                <?php if (!empty($a->club_name)): ?>
                  <div class="a-club"><?= out($a->club_name) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= out($a->country_name ?? $a->country ?? '—') ?></td>
          <td style="text-align:center"><?= (int)$a->comp_count ?></td>
          <td style="text-align:center" class="<?= $pts > 0 ? 'points-cell' : 'points-zero' ?>"><?= $pts > 0 ? number_format($pts) : '—' ?></td>
          <td style="text-align:center">
            <a class="btn-profile" href="<?= BASE_URL ?>users/profile/<?= out($a->slug) ?>">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <p class="no-results" id="no-results">No athletes match your search.</p>

  <?php if ($pagination_data): Pagination::display($pagination_data); endif; ?>
</div>

<script>
function filterAthletes(q) {
  q = q.toLowerCase().trim();
  let visible = 0;
  document.querySelectorAll('#athletes-tbody tr').forEach(row => {
    const match = !q || row.dataset.search.includes(q);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  document.getElementById('no-results').style.display = (visible === 0 && q) ? 'block' : 'none';
}
</script>
