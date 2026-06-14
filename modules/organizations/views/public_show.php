<?php
/**
 * FILE: modules/organizations/views/public_show.php
 * PURPOSE: Public profile page for an Organization (surf club / organiser)
 *
 * Expects from controller (see Organizations::show):
 *  $org (object): organization, slug, email, phone, address, country (code), status,
 *                 logo, timezone, and optional social fields (instagram/facebook/youtube/tiktok/website/founded_year)
 *  $country (string|null): resolved country name
 *  $stats (array): ['events'=>int,'participants'=>int,'judges'=>int]
 *  $upcoming (array): rows [id,name,year,start_date,end_date,location,status,divisions[]]
 *  $recent_results (array): rows [id,name,location,start_date,end_date]
 *  $judges (array): rows [name,role,status]
 *  $gallery (array): images [url,alt]
 *  $socials (array): ['instagram','facebook','youtube','tiktok']
 *  $contact (array): ['email','phone']
 *
 * Utilities: out() helper, BASE_URL constant
 */

$org_name  = $org->organization ?? 'Organization';
$org_logo  = !empty($org->logo)
    ? BASE_URL . 'organizations_module/images/org_avatars/' . out($org->logo)
    : '';
$logo_full = $org_logo; // absolute URL for SEO

// Collect available social/external links (all null-safe — columns may not exist)
$links = array_filter([
    'Website'   => $org->website ?? null,
    'Instagram' => $socials['instagram'] ?? null,
    'Facebook'  => $socials['facebook'] ?? null,
    'YouTube'   => $socials['youtube'] ?? null,
    'TikTok'    => $socials['tiktok'] ?? null,
]);

// Status label/stage for upcoming events
$status_meta = [
    'open'      => ['label' => 'Registration Open', 'stage' => 'Join now'],
    'scheduled' => ['label' => 'Scheduled',         'stage' => 'Coming soon'],
    'closed'    => ['label' => 'Closed',            'stage' => 'Entries locked'],
];
?>

<div class="org-public">

    <!-- ===== Hero: identity + actions ===== -->
    <section class="op-hero" aria-label="Organization overview">
        <div class="op-hero-glow" aria-hidden="true"></div>

        <div class="op-identity">
            <div class="op-avatar" aria-hidden="true">
                <?php if ($org_logo): ?>
                    <img src="<?= $org_logo ?>" alt="">
                <?php else: ?>
                    <span><?= out(mb_strtoupper(mb_substr($org_name, 0, 1))) ?></span>
                <?php endif; ?>
            </div>
            <div class="op-identity-text">
                <span class="op-eyebrow">Surf Organizer</span>
                <h1 class="op-org-name"><?= out($org_name) ?></h1>
                <div class="op-org-meta">
                    <?php if (!empty($country)): ?>
                        <span class="op-meta-item">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= out($country) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($org->founded_year)): ?>
                        <span class="op-sep" aria-hidden="true"></span>
                        <span class="op-meta-item">Est. <?= (int)$org->founded_year ?></span>
                    <?php endif; ?>
                    <?php if (($org->status ?? '') === 'active'): ?>
                        <span class="op-sep" aria-hidden="true"></span>
                        <span class="op-verified">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.4 1.7 2.9-.3 1.2 2.7 2.6 1.3-.6 2.9 1.6 2.4-2 2.1.1 2.9-2.9.7-1.3 2.6-2.9-.5L12 22l-2.4-1.3-2.9.5-1.3-2.6-2.9-.7.1-2.9-2-2.1L2.3 10.3l-.6-2.9 2.6-1.3 1.2-2.7 2.9.3z"/><path d="M9.2 12.2l1.9 1.9 3.9-3.9" fill="none" stroke="var(--bg)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Verified
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($links)): ?>
                    <div class="op-socials">
                        <?php foreach ($links as $label => $url): ?>
                            <a class="op-social" href="<?= out($url) ?>" target="_blank" rel="noopener"><?= out($label) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="op-hero-actions">
            <a class="op-cta" href="<?= BASE_URL ?>competitions/browse?org=<?= (int)$org->id ?>">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                View competitions
            </a>
            <a class="op-ghost" href="#contact">Contact</a>
        </div>
    </section>

    <!-- ===== Stats ===== -->
    <section class="op-stats" aria-label="Organization statistics">
        <div class="op-stat">
            <span class="op-stat-k"><?= (int)($stats['events'] ?? 0) ?></span>
            <span class="op-stat-l">Events Hosted</span>
        </div>
        <div class="op-stat">
            <span class="op-stat-k"><?= (int)($stats['participants'] ?? 0) ?></span>
            <span class="op-stat-l">Athletes</span>
        </div>
        <div class="op-stat">
            <span class="op-stat-k"><?= (int)($stats['judges'] ?? 0) ?></span>
            <span class="op-stat-l">Judges</span>
        </div>
    </section>

    <!-- ===== Main grid: upcoming events + sidebar ===== -->
    <div class="op-grid">

        <!-- Upcoming events -->
        <section class="op-card op-main" aria-labelledby="op-upcoming-title">
            <h2 id="op-upcoming-title">Upcoming <span>Events</span></h2>
            <p>Scheduled and open competitions hosted by this organizer.</p>

            <?php if (!empty($upcoming)): ?>
                <div class="op-event-list">
                    <?php foreach ($upcoming as $ev):
                        $status = strtolower($ev['status'] ?? 'closed');
                        $meta   = $status_meta[$status] ?? ['label' => ucfirst($status), 'stage' => ''];
                        $sd     = strtotime($ev['start_date']);
                        $ed     = strtotime($ev['end_date'] ?? $ev['start_date']);
                        if ($ed && date('Y-m-d', $ed) !== date('Y-m-d', $sd)) {
                            $range = date('M j', $sd) . ' – ' . date('M j, Y', $ed);
                        } else {
                            $range = date('M j, Y', $sd);
                        }
                    ?>
                    <article class="op-event" style="--st: var(--chip-<?= out($status) ?>)">
                        <div class="op-event-date" aria-hidden="true">
                            <span class="op-event-day"><?= date('j', $sd) ?></span>
                            <span class="op-event-mon"><?= strtoupper(date('M', $sd)) ?></span>
                        </div>
                        <div class="op-event-main">
                            <h3 class="op-event-name">
                                <a href="<?= BASE_URL ?>heats/show_heats_draw/<?= (int)$ev['id'] ?>"><?= out($ev['name'] . ' ' . $ev['year']) ?></a>
                            </h3>
                            <div class="op-event-meta">
                                <span><?= $range ?></span>
                                <?php if (!empty($ev['location'])): ?>
                                    <span class="op-sep" aria-hidden="true"></span>
                                    <span><?= out($ev['location']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($ev['divisions'])): ?>
                                <div class="op-divisions">
                                    <?php foreach ($ev['divisions'] as $d): ?>
                                        <span class="op-div"><?= out($d) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="op-event-side">
                            <span class="op-status-chip"><span class="op-dot" aria-hidden="true"></span><?= out($meta['label']) ?></span>
                            <?php if ($status === 'open'): ?>
                                <a class="op-register" mx-get="users/competition/<?= (int)$ev['id'] ?>" mx-build-modal="competitionModal">Register</a>
                            <?php elseif (!empty($meta['stage'])): ?>
                                <span class="op-stage"><?= out($meta['stage']) ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="op-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 16c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/><path d="M2 20c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/><path d="M4 12c0-4.5 3.5-8 8-8 3 0 5.5 1.5 7 4"/></svg>
                    <p>No upcoming competitions announced.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Sidebar: contact -->
        <aside class="op-side">

            <section class="op-card" id="contact" aria-labelledby="op-contact-title">
                <h2 id="op-contact-title">Get in <span>Touch</span></h2>
                <p>Reach out about events, entries or officiating.</p>
                <ul class="op-contact">
                    <?php if (!empty($contact['email'])): ?>
                        <li>
                            <span class="op-contact-ic" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></span>
                            <a href="mailto:<?= out($contact['email']) ?>"><?= out($contact['email']) ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($contact['phone'])): ?>
                        <li>
                            <span class="op-contact-ic" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                            <a href="tel:<?= out(preg_replace('/[^0-9+]/', '', $contact['phone'])) ?>"><?= out($contact['phone']) ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($org->address)): ?>
                        <li>
                            <span class="op-contact-ic" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                            <span><?= out($org->address) ?><?= !empty($country) ? ', ' . out($country) : '' ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </section>

        </aside>
    </div>

    <!-- ===== Recent results ===== -->
    <section class="op-card" aria-labelledby="op-results-title">
        <h2 id="op-results-title">Recent <span>Results</span></h2>
        <p>Recently completed events with published results.</p>

        <?php if (!empty($recent_results)): ?>
            <div class="op-result-list">
                <?php foreach ($recent_results as $res):
                    $rd = strtotime($res['end_date'] ?? $res['start_date']);
                ?>
                    <article class="op-result">
                        <div class="op-result-main">
                            <h3 class="op-result-name"><?= out($res['name']) ?></h3>
                            <div class="op-event-meta">
                                <?php if (!empty($res['location'])): ?>
                                    <span><?= out($res['location']) ?></span>
                                    <span class="op-sep" aria-hidden="true"></span>
                                <?php endif; ?>
                                <span><?= date('M j, Y', $rd) ?></span>
                            </div>
                        </div>
                        <div class="op-result-actions">
                            <a class="op-ghost" href="<?= BASE_URL ?>heats/show_heats_draw/<?= (int)$res['id'] ?>">Draw</a>
                            <a class="op-cta op-cta-sm" href="<?= BASE_URL ?>results/show/<?= (int)$res['id'] ?>">Results</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="op-muted">No results to show yet.</p>
        <?php endif; ?>
    </section>

    <!-- ===== Gallery (optional) ===== -->
    <?php if (!empty($gallery)): ?>
        <section class="op-card" aria-labelledby="op-gallery-title">
            <h2 id="op-gallery-title">Gallery</h2>
            <div class="op-gallery">
                <?php foreach ($gallery as $g): ?>
                    <figure class="op-g"><img src="<?= out($g['url']) ?>" alt="<?= out($g['alt'] ?? 'Photo') ?>" loading="lazy"></figure>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<!-- JSON-LD schema for SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SportsOrganization",
  "name": "<?= out($org_name) ?>",
  "url": "<?= BASE_URL ?>organizations/show/<?= out($org->slug ?? '') ?>"<?php if ($logo_full): ?>,
  "logo": "<?= $logo_full ?>"<?php endif; ?>,
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "<?= out($org->address ?? '') ?>",
    "addressCountry": "<?= out($country ?? '') ?>"
  }
}
</script>

<style>
/* =========================================================================
   ORGANIZATION PUBLIC PROFILE — "Night Swell"
   Matches the organizer dashboard design language (see settings.php).
   ========================================================================= */
.org-public {
    --op-accent: light-dark(var(--accent), var(--opposite));
    --op-line: color-mix(in oklab, var(--op-accent), transparent 78%);
    --op-surface: light-dark(hsla(0, 0%, 100%, 0.55), hsla(240, 25%, 8%, 0.45));
    --op-ease: cubic-bezier(0.16, 1, 0.3, 1);
    width: min(1100px, 100%);
    margin-inline: auto;
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
}

/* Staggered entry */
.org-public > * {
    opacity: 0;
    transform: translateY(10px);
    animation: op-in 0.55s var(--op-ease) forwards;
}
.org-public > *:nth-child(2) { animation-delay: 60ms; }
.org-public > *:nth-child(3) { animation-delay: 120ms; }
.org-public > *:nth-child(4) { animation-delay: 180ms; }
.org-public > *:nth-child(5) { animation-delay: 240ms; }
@keyframes op-in { to { opacity: 1; transform: none; } }

/* ===== Cards ===== */
.op-card {
    background: var(--op-surface);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    border: 1px solid var(--op-line);
    border-radius: var(--radius);
    padding: 1.25rem 1.25rem 1.5rem;
    box-shadow: 0 2px 12px light-dark(var(--accent-shadow), hsla(240, 60%, 5%, 0.5));
    margin-bottom: 1rem;
}
.org-public h2 {
    font-size: 1.4rem;
    border-bottom: 2px solid var(--op-line);
    color: var(--op-accent);
    text-align: right;
    margin: 0 0 0.15rem;
    padding-bottom: 0.35rem;
}
.org-public h2 span { color: light-dark(black, white); }
.org-public h2 + p {
    color: color-mix(in oklab, var(--op-accent), transparent 15%);
    text-align: right;
    font-size: 0.92rem;
    margin: 0 0 1.25rem;
}
.op-muted { color: color-mix(in oklab, var(--text), transparent 35%); }

/* ===== Hero ===== */
.op-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    background: var(--op-surface);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    border: 1px solid var(--op-line);
    border-radius: var(--radius);
    padding: 1.75rem 1.75rem;
    box-shadow: 0 2px 16px light-dark(var(--accent-shadow), hsla(240, 60%, 5%, 0.5));
}
.op-hero-glow {
    position: absolute;
    inset: auto -10% 40% auto;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, color-mix(in oklab, var(--op-accent), transparent 70%), transparent 65%);
    filter: blur(20px);
    pointer-events: none;
    z-index: 0;
}
.op-identity { position: relative; z-index: 1; display: flex; align-items: center; gap: 1.25rem; min-width: 0; }
.op-avatar {
    flex: none;
    width: 84px;
    height: 84px;
    border-radius: 22px;
    display: grid;
    place-items: center;
    overflow: hidden;
    border: 1px solid color-mix(in oklab, var(--op-accent), transparent 40%);
    box-shadow: 0 0 0 5px color-mix(in oklab, var(--op-accent), transparent 90%),
                0 8px 20px light-dark(var(--accent-shadow), hsla(240, 60%, 5%, 0.6));
    background: color-mix(in oklab, var(--op-accent), transparent 90%);
}
.op-avatar img { width: 100%; height: 100%; object-fit: cover; }
.op-avatar span { font-size: 2.2rem; font-weight: 900; color: var(--op-accent); }
.op-identity-text { display: grid; gap: 0.3rem; min-width: 0; }
.op-eyebrow {
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: color-mix(in oklab, var(--op-accent), transparent 15%);
}
.op-org-name {
    font-size: clamp(1.5rem, 4vw, 2.1rem);
    font-weight: 900;
    line-height: 1.1;
    margin: 0;
    border: none;
    padding: 0;
    color: var(--text);
    text-align: left;
}
.op-org-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.82rem;
    color: color-mix(in oklab, var(--text), transparent 25%);
}
.op-meta-item { display: inline-flex; align-items: center; gap: 0.35rem; }
.op-sep { width: 3px; height: 3px; border-radius: 50%; background: var(--op-accent); flex: none; }
.op-verified {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 700;
    color: var(--ok);
}

/* Social pills */
.op-socials { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.35rem; }
.op-social {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-decoration: none;
    padding: 0.3rem 0.8rem;
    border-radius: 999px;
    color: var(--op-accent);
    border: 1px solid color-mix(in oklab, var(--op-accent), transparent 60%);
    background: color-mix(in oklab, var(--op-accent), transparent 92%);
    transition: transform 0.18s var(--op-ease), background-color 0.18s var(--op-ease), color 0.18s var(--op-ease);
}
.op-social:hover {
    transform: translateY(-1px);
    background: var(--op-accent);
    color: var(--bg);
}

/* Hero actions */
.op-hero-actions { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 0.65rem; margin-left: auto; }
.op-cta {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.3rem;
    border-radius: 999px;
    border: 1px solid transparent;
    background: var(--op-accent);
    color: var(--bg);
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 10px color-mix(in oklab, var(--op-accent), transparent 55%);
    transition: transform 0.15s var(--op-ease), box-shadow 0.15s var(--op-ease), filter 0.15s var(--op-ease);
}
.op-cta:hover { transform: translateY(-1px); color: var(--bg); filter: brightness(1.05); box-shadow: 0 6px 18px color-mix(in oklab, var(--op-accent), transparent 40%); }
.op-cta:active { transform: translateY(0) scale(0.98); transition-duration: 80ms; }
.op-cta:focus-visible { outline: none; box-shadow: 0 0 0 3px color-mix(in oklab, var(--op-accent), transparent 50%); }
.op-cta-sm { padding: 0.45rem 1rem; font-size: 0.78rem; }

.op-ghost {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.7rem 1.3rem;
    border-radius: 999px;
    border: 1px solid color-mix(in oklab, var(--op-accent), transparent 55%);
    background: transparent;
    color: var(--op-accent);
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: transform 0.15s var(--op-ease), background-color 0.15s var(--op-ease), color 0.15s var(--op-ease);
}
.op-ghost:hover { transform: translateY(-1px); background: color-mix(in oklab, var(--op-accent), transparent 88%); color: var(--op-accent); }
.op-result .op-ghost { padding: 0.45rem 1rem; font-size: 0.78rem; }

/* ===== Stats ===== */
.op-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}
.op-stat {
    display: grid;
    gap: 0.25rem;
    justify-items: center;
    padding: 1.25rem 0.75rem;
    border-radius: var(--radius);
    border: 1px solid var(--op-line);
    background: var(--op-surface);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    transition: transform 0.2s var(--op-ease), box-shadow 0.2s var(--op-ease), border-color 0.2s var(--op-ease);
}
.op-stat:hover {
    transform: translateY(-2px);
    border-color: color-mix(in oklab, var(--op-accent), transparent 50%);
    box-shadow: 0 6px 18px light-dark(var(--accent-shadow), var(--opposite-shadow));
}
.op-stat-k {
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
    font-variant-numeric: tabular-nums;
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 800;
    line-height: 1;
    color: var(--text);
}
.op-stat-l {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: color-mix(in oklab, var(--text), transparent 40%);
}

/* ===== Main grid ===== */
.op-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr);
    gap: 1.25rem;
    align-items: start;
}
.op-side { display: grid; gap: 1.25rem; }

/* ===== Event list ===== */
.op-event-list { display: grid; gap: 0.85rem; }
.op-event {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: 1.1rem;
    padding: 0.85rem 1.1rem;
    border-radius: calc(var(--radius) - 4px);
    border: 1px solid var(--op-line);
    border-inline-start: 3px solid color-mix(in oklab, var(--st), transparent 25%);
    background: light-dark(hsla(0, 0%, 100%, 0.4), hsla(240, 25%, 10%, 0.4));
    transition: transform 0.2s var(--op-ease), box-shadow 0.2s var(--op-ease), border-color 0.2s var(--op-ease);
}
.op-event:hover {
    transform: translateY(-2px);
    border-color: color-mix(in oklab, var(--st), transparent 45%);
    border-inline-start-color: var(--st);
    box-shadow: 0 6px 18px color-mix(in oklab, var(--st), transparent 80%);
}
.op-event-date {
    display: grid;
    justify-items: center;
    gap: 0.1rem;
    min-width: 54px;
    padding: 0.5rem 0.65rem;
    border-radius: 12px;
    background: color-mix(in oklab, var(--st), transparent 90%);
    border: 1px solid color-mix(in oklab, var(--st), transparent 70%);
}
.op-event-day {
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1;
    color: var(--text);
}
.op-event-mon { font-size: 0.6rem; font-weight: 700; letter-spacing: 2px; color: color-mix(in oklab, var(--st), var(--text) 25%); }
.op-event-main { display: grid; gap: 0.35rem; min-width: 0; }
.op-event-name { font-size: 1.05rem; font-weight: 800; margin: 0; padding: 0; line-height: 1.2; }
.op-event-name a { color: var(--text); text-decoration: none; transition: color 0.15s var(--op-ease); }
.op-event-name a:hover { color: var(--op-accent); }
.op-event-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.78rem;
    color: color-mix(in oklab, var(--text), transparent 30%);
}
.op-divisions { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.15rem; }
.op-div {
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    color: color-mix(in oklab, var(--text), transparent 20%);
    border: 1px solid color-mix(in oklab, var(--text), transparent 80%);
    background: color-mix(in oklab, var(--text), transparent 92%);
}
.op-event-side { display: grid; gap: 0.4rem; justify-items: end; }
.op-status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.7rem;
    border-radius: 999px;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    white-space: nowrap;
    color: color-mix(in oklab, var(--st), var(--text) 20%);
    border: 1px solid color-mix(in oklab, var(--st), transparent 55%);
    background: color-mix(in oklab, var(--st), transparent 90%);
}
.op-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--st); box-shadow: 0 0 6px var(--st); }
.op-stage { font-size: 0.68rem; color: color-mix(in oklab, var(--text), transparent 45%); }
.op-register {
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-decoration: none;
    padding: 0.4rem 0.9rem;
    border-radius: 999px;
    cursor: pointer;
    color: var(--bg);
    background: var(--op-accent);
    box-shadow: 0 2px 8px color-mix(in oklab, var(--op-accent), transparent 55%);
    transition: transform 0.15s var(--op-ease), filter 0.15s var(--op-ease);
}
.op-register:hover { transform: translateY(-1px); color: var(--bg); filter: brightness(1.05); }

/* ===== Contact ===== */
.op-contact { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.7rem; }
.op-contact li { display: flex; align-items: center; gap: 0.7rem; font-size: 0.88rem; }
.op-contact a { color: var(--text); text-decoration: none; transition: color 0.15s var(--op-ease); word-break: break-word; }
.op-contact a:hover { color: var(--op-accent); }
.op-contact-ic {
    flex: none;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    color: var(--op-accent);
    background: color-mix(in oklab, var(--op-accent), transparent 90%);
    border: 1px solid color-mix(in oklab, var(--op-accent), transparent 65%);
}

/* ===== Recent results ===== */
.op-result-list { display: grid; gap: 0.85rem; }
.op-result {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.1rem;
    border-radius: calc(var(--radius) - 4px);
    border: 1px solid var(--op-line);
    background: light-dark(hsla(0, 0%, 100%, 0.4), hsla(240, 25%, 10%, 0.4));
    transition: transform 0.2s var(--op-ease), box-shadow 0.2s var(--op-ease), border-color 0.2s var(--op-ease);
}
.op-result:hover {
    transform: translateY(-2px);
    border-color: color-mix(in oklab, var(--op-accent), transparent 50%);
    box-shadow: 0 6px 18px light-dark(var(--accent-shadow), var(--opposite-shadow));
}
.op-result-main { display: grid; gap: 0.25rem; min-width: 0; }
.op-result-name { font-size: 1rem; font-weight: 800; margin: 0; padding: 0; color: var(--text); }
.op-result-actions { display: flex; align-items: center; gap: 0.5rem; }

/* ===== Empty ===== */
.op-empty {
    display: grid;
    justify-items: center;
    gap: 0.6rem;
    padding: 2.25rem 1rem;
    text-align: center;
    color: color-mix(in oklab, var(--text), transparent 25%);
}
.op-empty svg { color: var(--op-accent); opacity: 0.7; }
.op-empty p { margin: 0; }

/* ===== Gallery ===== */
.op-gallery { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; }
.op-g { margin: 0; height: 130px; border-radius: 12px; overflow: hidden; border: 1px solid var(--op-line); }
.op-g img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.4s var(--op-ease); }
.op-g:hover img { transform: scale(1.05); }

/* ===== Responsive ===== */
@media screen and (max-width: 900px) {
    .op-grid { grid-template-columns: 1fr; }
    .op-gallery { grid-template-columns: repeat(2, 1fr); }
}
@media screen and (max-width: 600px) {
    .op-hero { padding: 1.25rem; }
    .op-hero-actions { width: 100%; }
    .op-hero-actions .op-cta, .op-hero-actions .op-ghost { flex: 1; justify-content: center; }
    .op-stats { grid-template-columns: 1fr; }
    .op-event { grid-template-columns: auto minmax(0, 1fr); }
    .op-event-side { grid-column: 2; justify-items: start; flex-direction: row; }
    .op-avatar { width: 64px; height: 64px; border-radius: 18px; }
    .op-avatar span { font-size: 1.7rem; }
}

@media (prefers-reduced-motion: reduce) {
    .org-public > * { animation: none; opacity: 1; transform: none; }
    .op-stat, .op-event, .op-result, .op-social, .op-cta, .op-ghost, .op-register, .op-g img { transition: none; }
}
</style>
