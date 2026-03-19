<?php
/**
 * FILE: modules/organizations/views/public_show.php
 * PURPOSE: Public profile page for an Organization (surf club / organiser)
 *
 * Expects from controller:
 *  $org (object): id, name, slug, logo_url, city, country, about_html, founded_year, website
 *  $stats (array): ['events'=>int,'participants'=>int,'judges'=>int]
 *  $upcoming (array): list of competitions (id, name, year, start_date, end_date, location, divisions[], cover_url, status)
 *  $recent_results (array): past events with podiums
 *  $judges (array): public roster (name, role, country, avatar_url)
 *  $gallery (array): images (url, alt)
 *  $socials (array): ['instagram'=>url,'facebook'=>url,'youtube'=>url,'tiktok'=>url]
 *  $contact (array): ['email','phone']
 *
 * Utilities: out() helper, BASE_URL constant
 */
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= out($org->name) ?> · Profile</title>
<meta name="description" content="<?= out($org->name) ?> — surf competitions, results, roster, and news.">
<meta property="og:title" content="<?= out($org->name) ?>">
<meta property="og:description" content="Surf competitions, results, roster, and news.">
<meta property="og:type" content="website">
<?php if (!empty($org->logo_url)): ?>
<meta property="og:image" content="<?= out($org->logo_url) ?>">
<?php endif; ?>
<style>
  /* RESPONSIVE HEADER */
  @media (min-width: 960px) {
    .grid-3-auto {
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin: 1rem;
    }
  }
  @media screen and (max-width: 600px) {
    .d-sm-none {
      display: none;
    }
  }
  h2 {
      margin-top: 0;
      font-size: 1.5rem;
  }
  main p {
      font-size: 1rem;
      line-height: 1.6em;
      color: var(--text-light);
  }
  /* STATS */
  .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:10px}
  .stat{
      padding: 12px;
      border-radius: 14px;
      background: var(--bg);
      border: 1px dashed light-dark(var(--accent), var(--opposite));
      box-shadow: 1px 1px 3px light-dark(var(--accent-shadow), var(--opposite-shadow));
  }
  .stat .k{font-size:22px;font-weight:900}
  .stat .l{font-size:12px;color:var(--sub)}

  /* LISTS */
  .events{display:grid;gap:12px;font-size:14px;}
  .event{
    display:grid;
    grid-template-columns:2fr 1fr auto;
    gap:12px;
    align-items:center;
    padding:12px;
    border-radius:14px;
    background:var(--bg);
    border:1px dashed var(--border);
    @media screen and (max-width: 600px) {
      grid-template-columns:1fr auto;
    }
  }
  .cover{width:100%;height:160px;border-radius:14px;border:1px solid var(--border);background:#0a1230 url('') center/cover no-repeat}

  /* SIDEBAR */
  .roster{display:grid;gap:10px}
  .judge{display:grid;grid-template-columns:40px 1fr auto;gap:10px;align-items:center;padding:8px;border-radius:12px;background:#0c1430;border:1px solid var(--border)}
  .avatar{width:40px;height:40px;border-radius:10px;overflow:hidden;background:#09102a;border:1px solid var(--border)}
  .avatar img{width:100%;height:100%;object-fit:cover}
  .pill{font-size:11px;padding:4px 8px;border-radius:999px;border:1px solid var(--border);background:#0a1230;color:var(--sub)}

  /* GALLERY */
  .gallery{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
  @media (max-width: 900px){.gallery{grid-template-columns:repeat(2,1fr)}}
  .g{height:120px;border-radius:12px;border:1px solid var(--border);overflow:hidden;background:#0a1230}
  .g img{width:100%;height:100%;object-fit:cover}

  /* FOOTER CARD */
  .contact{display:grid;gap:8px}
  .row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
  .icon{width:18px;height:18px;border-radius:4px;background:linear-gradient(135deg,var(--brand),var(--accent));display:inline-block}

</style>

<!-- org head -->
  <section class="head mt-2 mb-2 grid-3-auto">
      <div class="logo">
      <?php if(!empty($org->logo)): ?>
          <img src="<?= out($org->logo) ?>" alt="<?= out($org->organization) ?> logo" style="background: var(--primary-color);border-radius: 14px;max-width:100px;object-fit:contain;">
      <?php else: ?>
          <span class="badge">No Logo</span>
      <?php endif; ?>
      </div>
      <div>
          <h1 style="margin:0 0 4px; font-size:28px; letter-spacing:.2px;"><?= out($org->organization) ?></h1>
          <div class="h-meta">
              <?= $country ? out($country) : '' ?>
              <?php if (!empty($org->founded_year)): ?> · Founded <?= (int)$org->founded_year ?><?php endif; ?>
          </div>
          <div class="row" style="margin-top:8px;gap:8px;">
              <?php if (!empty($socials['instagram'])): ?><a class="badge" href="<?= out($socials['instagram']) ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
              <?php if (!empty($socials['facebook'])): ?><a class="badge" href="<?= out($socials['facebook']) ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
              <?php if (!empty($socials['youtube'])): ?><a class="badge" href="<?= out($socials['youtube']) ?>" target="_blank" rel="noopener">YouTube</a><?php endif; ?>
              <?php if (!empty($org->website)): ?><a class="badge" href="<?= out($org->website) ?>" target="_blank" rel="noopener">Website</a><?php endif; ?>
          </div>
      </div>
      <div class="h-actions">
          <a class="btn" href="<?= BASE_URL ?>competitions/browse?org=<?= (int)$org->id ?>">View competitions</a>
          <a class="btn secondary" href="#contact">Contact</a>
      </div>
  </section>

<!-- COMPS & STATS -->
  <div class="grid grid-12 mb-1">
      <!-- MAIN -->
      <section class="card pad span-7">
      <h2>Upcoming <span>Events</span></h2>
      <p>Scheduled events are displayed here.</p>
      <?php if (!empty($upcoming)): ?>
          <div class="events">
          <?php foreach ($upcoming as $ev): ?>
              <article class="event">
                <div>
                  <div class="lg"><a href="<?= BASE_URL ?>heats/show_heats_draw/<?= (int)$ev['id'] ?>"><?= out($ev['name'] . ' ' . $ev['year']) ?></a></div>
                  <?php $sd = strtotime($ev['start_date']); $ed = strtotime($ev['end_date'] ?? $ev['start_date']); ?>
                  <div><?= date('M j', $sd) ?><?= $ed && date('M j',$ed)!==date('M j',$sd) ? '–'.date('M j',$ed) : '' ?></div>
                  <div class="muted"><?= out($ev['location'] ?? ($org->country ? out($org->country) : '')) ?></div>
                </div>
                <?php if (!empty($ev['divisions'])): ?>
                <div class="d-sm-none" style="margin-top:6px;">
                    <?php foreach ($ev['divisions'] as $d): ?>
                    <span class="chip"><?= out($d) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div>
                    <?php if (($ev['status'] ?? '')==='open'): ?>
                    <a class="btn" mx-get="users/competition/<?= (int)$ev['id'] ?>" mx-build-modal="competitionModal">Register</a>
                    <?php endif; ?>
                    <span class="chip status-<?= out($ev['status'] ?? 'closed') ?>"><?= out(ucfirst($ev['status'] ?? 'closed')) ?></span>
                </div>
              </article>
          <?php endforeach; ?>
          </div>
      <?php else: ?>
          <p class="muted">No upcoming competitions announced.</p>
      <?php endif; ?>
      </section>

      <!-- SIDEBAR -->
      <section class="card pad span-5">
          <h2>Organizer <span>Stats</span></h2>
          <p>Total historic statistics displayed here</p>
          <div class="stats">
              <div class="stat"><div class="k"><?= (int)($stats['events'] ?? 0) ?></div><div class="l">Events</div></div>
              <div class="stat"><div class="k"><?= (int)($stats['participants'] ?? 0) ?></div><div class="l">Participants</div></div>
              <div class="stat"><div class="k"><?= (int)($stats['judges'] ?? 0) ?></div><div class="l">Judges</div></div>
          </div>
      </section>
  </div>

<!-- RECENT RESULTS -->
<section class="card pad mb-1" style="margin-top:var(--gap);">
  <h2>Recent <span>Results</span></h2>
  <p>Recently completed events with published results.</p>
  <?php if (!empty($recent_results)): ?>
    <div class="events">
      <?php foreach ($recent_results as $res): ?>
        <article class="stat grid" style="grid-template-columns: auto auto;justify-content: space-between;">
          <div class="grid" style="gap:8px;">
            <div class="e-name"><h3><?= out($res['name']) ?></h3></div>
            <div class="muted"><?= out($res['location'] ?? '') ?> - <?= date('M j, Y', strtotime($res['end_date'] ?? $res['start_date'])) ?></div>
          </div>
          <div class="row" style="gap:8px;">
            <button onclick="window.location.href='<?= BASE_URL ?>heats/show_heats_draw/<?= (int)$res['id'] ?>'">View Draw</button>
            <button onclick="window.location.href='<?= BASE_URL ?>results/show/<?= (int)$res['id'] ?>'">View Results</button>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="muted">No results to show yet.</p>
  <?php endif; ?>
</section>

<!-- GALLERY -->
<?php if (!empty($gallery)): ?>
  <section class="card pad" style="margin-top:var(--gap);">
    <h2>Gallery</h2>
    <div class="gallery">
      <?php foreach ($gallery as $g): ?>
        <figure class="g"><img src="<?= out($g['url']) ?>" alt="<?= out($g['alt'] ?? 'Photo') ?>"></figure>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>


<!-- JSON-LD schema for SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SportsOrganization",
  "name": "<?= out($org->name) ?>",
  "url": "<?= BASE_URL ?>organizations/<?= out($org->slug) ?>",
  "logo": "<?= out($org->logo_url) ?>",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "<?= out($org->address) ?>",
    "addressCountry": "<?= out($org->country) ?>"
  }
}
</script>

