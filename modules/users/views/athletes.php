<?php
/**
 * Public athletes listing page — /users/athletes
 * $athletes: array of objects (id, name, slug, country, club_name, gender, age, comp_count)
 */
?>
<style>
  .athletes-hero {
    text-align: center;
    padding: 2.5rem 1rem 1.5rem;
  }
  .athletes-hero h1 { font-size: 2rem; margin-bottom: 0.25rem; }
  .athletes-hero p  { color: var(--text-light); margin: 0; }

  .athletes-search {
    max-width: 420px;
    margin: 1.5rem auto;
    position: relative;
  }
  .athletes-search input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    border-radius: 8px;
    border: 1px solid var(--border);
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

  .athletes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    padding: 0 1rem 3rem;
    max-width: 1100px;
    margin: 0 auto;
  }

  .athlete-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.2rem 1rem;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: transform .15s, box-shadow .15s;
    display: block;
  }
  .athlete-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,.12);
  }
  .athlete-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--border);
    margin: 0 auto 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    overflow: hidden;
  }
  .athlete-avatar img { width: 100%; height: 100%; object-fit: cover; }
  .athlete-name {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .athlete-meta {
    font-size: 0.78rem;
    color: var(--text-light);
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .athlete-badge {
    display: inline-block;
    font-size: 0.72rem;
    background: var(--primary, #0d9);
    color: #000;
    border-radius: 20px;
    padding: 2px 10px;
    font-weight: 600;
  }
  .no-results {
    text-align: center;
    padding: 3rem;
    color: var(--text-light);
    display: none;
  }
</style>

<div class="athletes-hero">
  <h1>Athletes</h1>
  <p>Browse all registered competitors on Surf Panel</p>
</div>

<div class="athletes-search">
  <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
  <input type="text" id="athlete-search" placeholder="Search by name, club or country…" oninput="filterAthletes(this.value)">
</div>

<div class="athletes-grid" id="athletes-grid">
<?php foreach ($athletes as $a): ?>
  <?php
    $flag    = !empty($a->country) ? strtolower($a->country) : '';
    $initial = mb_strtoupper(mb_substr($a->name ?? '?', 0, 1));
    $meta    = implode(' · ', array_filter([
        $flag ? out($a->country_name ?? strtoupper($a->country)) : '',
        !empty($a->club_name) ? out($a->club_name) : '',
        !empty($a->age) ? $a->age . ' yrs' : '',
    ]));
  ?>
  <a class="athlete-card"
     href="<?= BASE_URL ?>users/profile/<?= out($a->slug) ?>"
     data-search="<?= strtolower(out($a->name) . ' ' . ($a->club_name ?? '') . ' ' . ($a->country ?? '')) ?>">

    <div class="athlete-avatar">
      <?php if (!empty($a->avatar)): ?>
        <img src="<?= out($a->avatar) ?>" alt="<?= out($a->name) ?>">
      <?php else: ?>
        <?= $initial ?>
      <?php endif; ?>
    </div>

    <div class="athlete-name"><?= out($a->name) ?></div>
    <div class="athlete-meta"><?= $meta ?></div>
    <span class="athlete-badge"><?= (int)$a->comp_count ?> comp<?= $a->comp_count != 1 ? 's' : '' ?></span>
  </a>
<?php endforeach; ?>
</div>

<p class="no-results" id="no-results">No athletes match your search.</p>

<script>
function filterAthletes(q) {
  q = q.toLowerCase().trim();
  let visible = 0;
  document.querySelectorAll('.athlete-card').forEach(card => {
    const match = !q || card.dataset.search.includes(q);
    card.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  document.getElementById('no-results').style.display = (visible === 0 && q) ? 'block' : 'none';
}
</script>
