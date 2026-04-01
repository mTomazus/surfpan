<?php
/**
 * Public athlete profile page — /users/profile/{slug}
 * $athlete:    array  (id, name, country, club_name, gender, dob, age, avatar, slug)
 * $history:    array  (comp_id, comp_name, year, location, comp_status, division, round, total_score, best_wave)
 * $comp_count: int    total competitions entered
 * $div_count:  int    total division entries
 * $best_round: string best round reached across all competitions
 */

$round_order = ['Final'=>1,'Semifinal'=>2,'Quarterfinal'=>3,'Round 2'=>4,'Repechage 2'=>5,'Repechage 1'=>6,'Round 1'=>7];

function round_badge(string $round): string {
    $classes = [
        'final'        => 'badge-gold',
        'semifinal'    => 'badge-silver',
        'quarterfinal' => 'badge-bronze',
    ];
    $key = strtolower($round);
    $cls = $classes[$key] ?? 'badge-default';
    return '<span class="round-badge ' . $cls . '">' . htmlspecialchars($round) . '</span>';
}
?>
<style>
  /* ── layout ── */
  .profile-wrap  { max-width: 860px; margin: 0 auto; padding: 1.5rem 1rem 4rem; }

  /* ── hero card ── */
  .hero-card {
    display: grid;
    grid-template-columns: auto 1fr;
    font-family: "Lato";
    font-weight: 900;
    gap: 1.5rem;
    align-items: center;
    background: hsla(0, 0%, 100%, 0.05);
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  .hero-avatar {
    width: 200px; height: 200px;
    border-radius: 50%;border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem; font-weight: 700; overflow: hidden; flex-shrink: 0;
  }
  .hero-avatar img { width: 100%; height: 100%; object-fit: cover; border: 2px solid var(--border); }
  .hero-country {
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: var(--ok);
    letter-spacing: 0.5rem;
    text-transform: uppercase;
    position: relative;
  }
  .hero-country + div {
    margin-block: auto;
    height: 1px;
    width: 100px;
    background: var(--ok);
  }
  .hero-name  {
    font-size: 4rem;
    text-wrap: wrap;
    font-weight: 700;
    margin: 0 0 0.3rem;
    width: 50%;
    line-height: 4.5rem;
    text-transform: uppercase;
  }
  .hero-meta  { font-size: 0.88rem; color: var(--text-light); display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; }
  .hero-meta span::before { content: '· '; opacity: .5; }
  .hero-meta span:first-child::before { content: ''; }

  /* ── stats bar ── */
  .stats-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
  }
  .stat-box {
    background: var(--bg);
    border: 1px solid var(--primary-60);
    box-shadow: 0 0 3px var(--primary-60), 0 0 3px var(--primary-60) inset;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
  }
  .stat-box .stat-val { font-size: 1.7rem; font-weight: 700; line-height: 1; color: var(--primary-60); }
  .stat-box .stat-lbl { font-size: 0.5rem; letter-spacing: 20%; color: var(--ok); margin-top: 0.25rem; text-transform: uppercase;}

  /* ── history table ── */
  .section-title { color: var(--text-light); font-size: 1.1rem; font-weight: 600; letter-spacing: 20%; margin: 0 0 0.75rem; border-bottom: 1px dashed var(--primary); text-transform: uppercase; }
  .section-title span { color: var(--primary); }
  .history-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
  .history-table th {
    background: var(--primary-20);
    text-align: left; padding: 0.5rem 0.75rem;
    font-size: 0.75rem; text-transform: uppercase;
    color: var(--text-light); border-bottom: 1px solid var(--border);
  }
  .history-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
  .history-table tr:last-child td { border-bottom: none; }
  .history-table tr:hover td { background: var(--bg-hover, rgba(255,255,255,.04)); }

  /* comp status pill */
  .status-pill {
    display: inline-block; font-size: 0.7rem; padding: 2px 8px;
    border-radius: 20px; font-weight: 600; text-transform: capitalize;
  }
  .status-finished  { background: #1a5c2a; color: #6effa0; }
  .status-running   { background: #5c3a00; color: #ffb84d; }
  .status-open      { background: #0d2a5c; color: #7fc3ff; }
  .status-created, .status-closed, .status-generated { background: var(--border); color: var(--text-light); }

  /* round badges */
  .round-badge { display: inline-block; font-size: 0.75rem; padding: 2px 9px; border-radius: 20px; font-weight: 600; }
  .badge-gold    { background: #6b4f00; color: #ffd700; }
  .badge-silver  { background: #3a3a3a; color: #c0c0c0; }
  .badge-bronze  { background: #4a2a00; color: #cd7f32; }
  .badge-default { background: var(--border); color: var(--text-light); }

  .score-cell { font-variant-numeric: tabular-nums; }

  .empty-state { text-align: center; padding: 3rem; color: var(--text-light); }

  /* ── breadcrumb ── */
  .breadcrumb { font-size: 0.82rem; color: var(--text-light); margin-bottom: 1.2rem; }
  .breadcrumb a { color: var(--text-light); text-decoration: none; }
  .breadcrumb a:hover { text-decoration: underline; }

  @media (max-width: 520px) {
    .hero-card  { grid-template-columns: 1fr; text-align: center; }
    .hero-meta  { justify-content: center; }
    .hero-avatar { margin: 0 auto; }
    .stats-bar  { grid-template-columns: 1fr 1fr; }
    .stats-bar .stat-box:last-child { grid-column: span 2; }
    .d-sm-none  { display: none; }
  }
</style>

<div class="profile-wrap">

  <!-- breadcrumb -->
  <div class="breadcrumb">
    <a href="<?= BASE_URL ?>users/athletes">← Athletes</a>
  </div>

  <!-- hero card -->
  <div class="hero-card">
    <div class="hero-avatar">
      <?php if (!empty($athlete['avatar'])): ?>
        <img src="<?= out($athlete['avatar']) ?>" alt="<?= out($athlete['name']) ?>">
      <?php else: ?>
        <?= mb_strtoupper(mb_substr($athlete['name'] ?? '?', 0, 1)) ?>
      <?php endif; ?>
    </div>
    <div>
        <div class="flex">
        <?php if (!empty($athlete['country'])): ?>
          <span class="hero-country"><?= out($athlete['country_name'] ?? strtoupper($athlete['country'])) ?></span>
        <?php endif; ?>
          <div></div>
        </div>
      <div class="hero-name"><?= out($athlete['name']) ?></div>
      <div class="hero-meta">
        <?php if (!empty($athlete['club_name'])): ?>
          <span><?= out($athlete['club_name']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- stats bar -->
  <div class="stats-bar">
    <div class="stat-box">
      <div class="stat-lbl">Competition<?= $comp_count != 1 ? 's' : '' ?></div>
      <div class="stat-val"><?= (int)$comp_count ?></div>
    </div>
    <div class="stat-box">
      <div class="stat-lbl">Total Points</div>
      <div class="stat-val"><?= number_format($total_points) ?></div>
    </div>
    <div class="stat-box">
      <div class="stat-lbl">Global Rank</div>
      <div class="stat-val"><?= $global_rank > 0 ? '#' . $global_rank : '—' ?></div>
    </div>
  </div>

  <!-- competition history -->
  <div class="section-title">Competition <span>History</span></div>

  <?php if (empty($history)): ?>
    <div class="empty-state">No competition results yet.</div>
  <?php else: ?>
    <div style="overflow-x:auto">
      <table class="history-table">
        <thead>
          <tr>
            <th>Place</th>
            <th>Competition</th>
            <th>Division</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($history as $row): ?>
          <?php
            $place = (int)($row['place'] ?? 0);
            $place_label = $place === 1 ? '1st' : ($place === 2 ? '2nd' : ($place === 3 ? '3rd' : $place . 'th'));
            $place_style = $place <= 3 ? 'font-size:1.1rem' : 'font-size:0.9rem;color:var(--text-light)';
            $pts_map = [1=>1000,2=>700,3=>500,4=>400,5=>320,6=>260,7=>220,8=>180];
            $row_pts = $place > 0 ? ($pts_map[$place] ?? ($place <= 16 ? 130 : 80)) : 0;
          ?>
          <tr>
            <td style="text-align:center;<?= $place_style ?>">
              <?= $place > 0 ? $place_label : '—' ?>
              <?php if ($row_pts > 0): ?>
                <div style="font-size:0.7rem;color:var(--ok);margin-top:2px;font-family:'Lato', sans-serif"><?= $row_pts ?> pts</div>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= BASE_URL ?>results/show/<?= (int)$row['comp_id'] ?>" style="color:inherit;text-decoration:none;font-weight:500">
                <?= out($row['comp_name']) . ' ' . (int)$row['year'] ?>
                <div style="font-size:0.75rem;color:var(--text-light)"><?= out($row['location']) ?></div>
              </a>
            </td>
            <td><?= out($row['division']) ?></td>
            <td>
              <span class="status-pill status-<?= out($row['comp_status']) ?>">
                <?= out($row['comp_status']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
