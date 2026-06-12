<?php
// ---- Presentation data -------------------------------------------------
$org_title = $org->organization ?? 'Your Organization';
$org_logo  = !empty($org->logo) ? BASE_URL . 'organizations_module/images/org_avatars/' . out($org->logo) : '';

$is_pro     = is_object($active_subscription);
$plan_label = $is_pro
    ? ucwords(str_replace(['_', '-'], ' ', $active_subscription->product_code))
    : 'Starter Plan';

$status_meta = [
    'created'   => ['label' => 'Draft',       'stage' => 'Setup in progress'],
    'scheduled' => ['label' => 'Scheduled',   'stage' => 'Awaiting registration'],
    'open'      => ['label' => 'Open',        'stage' => 'Registration open'],
    'closed'    => ['label' => 'Closed',      'stage' => 'Ready for heat draw'],
    'generated' => ['label' => 'Heats Ready', 'stage' => 'Draw generated'],
    'running'   => ['label' => 'Live',        'stage' => 'Competition running'],
    'finished'  => ['label' => 'Finished',    'stage' => 'Results final'],
];
?>

<div class="org-dash">

    <?= flashdata('<div class="od-flash" role="status"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>', '</div>') ?>

    <!-- ===== Hero: identity + plan + primary action ===== -->
    <section class="od-hero" aria-label="Organization overview">
        <div class="od-identity">
            <div class="od-avatar" aria-hidden="true">
                <?php if ($org_logo): ?>
                    <img src="<?= $org_logo ?>" alt="">
                <?php else: ?>
                    <span><?= mb_strtoupper(mb_substr($org_title, 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <div class="od-identity-text">
                <span class="od-eyebrow">Organizer Dashboard</span>
                <h1 class="od-org-name"><?= out($org_title) ?></h1>
                <div class="od-org-meta">
                    <?php if (!empty($org->country)): ?><span><?= out($org->country) ?></span><span class="od-sep" aria-hidden="true"></span><?php endif; ?>
                    <span><?= out($org->timezone) ?></span>
                    <span class="od-sep" aria-hidden="true"></span>
                    <span class="od-org-status is-<?= out($org->status) ?>"><?= out($org->status) ?></span>
                </div>
            </div>
        </div>
        <div class="od-hero-side">
            <div class="od-plan">
                <span class="od-plan-pill<?= $is_pro ? ' is-pro' : '' ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.26 6.6 1.04-4.75 4.4 1.15 6.55L12 17.1l-5.9 3.15 1.15-6.55L2.5 9.3l6.6-1.04z"/></svg>
                    <?= out($plan_label) ?>
                </span>
                <span class="od-plan-note">
                    <?php if ($free_competitions_left === 'unlimited'): ?>
                        Unlimited events
                    <?php else: ?>
                        <?= (int) $free_competitions_left ?> free event<?= (int) $free_competitions_left === 1 ? '' : 's' ?> left
                        <?php if (!empty($event_pass_credits)): ?> · <?= (int) $event_pass_credits ?> event pass<?= (int) $event_pass_credits === 1 ? '' : 'es' ?><?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>
            <button type="button" class="od-cta" mx-get="competitions/create_comp" mx-target="#form-container" mx-select="#form-table">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Event
            </button>
        </div>
    </section>

    <!-- ===== Stats ===== -->
    <section class="od-stats" aria-label="Organization statistics">
        <div class="od-stat">
            <span class="od-stat-k"><?= (int) $stats['events'] ?></span>
            <span class="od-stat-l">Events Hosted</span>
        </div>
        <div class="od-stat">
            <span class="od-stat-k"><?= (int) $competitions_this_year ?></span>
            <span class="od-stat-l">This Year</span>
        </div>
        <div class="od-stat">
            <span class="od-stat-k"><?= (int) $stats['participants'] ?></span>
            <span class="od-stat-l">Athletes</span>
        </div>
        <div class="od-stat">
            <span class="od-stat-k"><?= (int) $stats['judges'] ?></span>
            <span class="od-stat-l">Judges</span>
        </div>
    </section>

    <!-- ===== Upcoming events ===== -->
    <section class="od-card od-events" aria-labelledby="od-events-title">
        <h2 id="od-events-title">Upcoming <span>Events</span></h2>
        <p>Click an event to edit details or change its status. Set status to &ldquo;closed&rdquo; to enable heat generation.</p>

        <div id="upcoming_comps" mx-get="organizations/dashboard" mx-select="#upcoming_comps" mx-target="#upcoming_comps" mx-trigger="load" class="od-event-list">
            <?php if (!empty($upcoming_comps)): ?>
                <?php foreach ($upcoming_comps as $comp):
                    $status = strtolower(out($comp->status));
                    $meta   = $status_meta[$status] ?? ['label' => $status, 'stage' => ''];
                    $sd = date_create($comp->start_date);
                    $ed = date_create($comp->end_date);
                    if ($comp->start_date === $comp->end_date) {
                        $range = $sd->format('j M Y');
                    } elseif ($sd->format('Y-m') === $ed->format('Y-m')) {
                        $range = $sd->format('j') . '–' . $ed->format('j M Y');
                    } else {
                        $range = $sd->format('j M') . ' – ' . $ed->format('j M Y');
                    }
                ?>
                <article class="od-event" style="--st: var(--chip-<?= $status ?>)"
                         mx-get="competitions/edit_comp/<?= (int) $comp->id ?>" mx-build-modal="comp-edit-modal"
                         tabindex="0" role="button" aria-label="Edit <?= out($comp->name) ?>">
                    <div class="od-event-date" aria-hidden="true">
                        <span class="od-event-day"><?= $sd->format('j') ?></span>
                        <span class="od-event-mon"><?= strtoupper($sd->format('M')) ?></span>
                    </div>
                    <div class="od-event-main">
                        <h3 class="od-event-name"><?= out($comp->name) ?> <span class="od-event-year"><?= out($comp->year) ?></span></h3>
                        <div class="od-event-meta">
                            <span><?= $range ?></span>
                            <?php if (!empty($comp->location)): ?>
                                <span class="od-sep" aria-hidden="true"></span>
                                <span><?= out($comp->location) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="od-event-status">
                        <span class="od-status-chip"><span class="od-dot" aria-hidden="true"></span><?= out($meta['label']) ?></span>
                        <?php if ($meta['stage']): ?><span class="od-stage"><?= out($meta['stage']) ?></span><?php endif; ?>
                    </div>
                    <div class="od-event-actions">
                        <?php
                        // -- Only show generate button when status is closed
                        if ($comp->status === 'closed' || $comp->status === 'generated') {
                            $btn_attr = [
                                'mx-get'    => 'heats/heat_generation_page',
                                'mx-target' => '#form-container',
                                'mx-select' => '#form-container',
                                'class'     => 'btn secondary'
                            ];
                            echo form_button('generate_button', '<i class="fa fa-tasks" aria-hidden="true"></i> Generate', $btn_attr);
                        }

                        // -- Only show delete button when status is open or unknown
                        $del_arr = ['closed', 'generated', 'running', 'finished'];
                        if (!in_array($comp->status, $del_arr)) {
                            $del_attr = [
                                'mx-get'         => 'competitions/delete_modal/' . (int) $comp->id,
                                'mx-build-modal' => 'comp-delete-modal',
                                'class'          => 'btn danger'
                            ];
                            echo form_button('del_button', '<i class="fa fa-trash" aria-hidden="true"></i> Delete', $del_attr);
                        }
                        ?>
                        <span class="od-edit-hint"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</span>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="od-empty">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 16c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/><path d="M2 20c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/><path d="M4 12c0-4.5 3.5-8 8-8 3 0 5.5 1.5 7 4"/></svg>
                    <h3>No upcoming events</h3>
                    <p class="od-empty-sub">Your next competition starts here. Create an event, open registration and run it live.</p>
                    <button type="button" class="od-cta" mx-get="competitions/create_comp" mx-target="#form-container" mx-select="#form-table">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create your first event
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== Event lifecycle + tips ===== -->
    <section class="od-card od-guide" aria-labelledby="od-guide-title">
        <h2 id="od-guide-title">Event <span>Workflow</span></h2>
        <p>Every event moves through five stages, from draft to live scoring.</p>

        <ol class="od-flow">
            <li style="--st: var(--chip-created)"><span class="od-flow-dot">1</span><span class="od-flow-label">Create</span><span class="od-flow-sub">Set dates &amp; divisions</span></li>
            <li style="--st: var(--chip-open)"><span class="od-flow-dot">2</span><span class="od-flow-label">Open</span><span class="od-flow-sub">Athletes register</span></li>
            <li style="--st: var(--chip-closed)"><span class="od-flow-dot">3</span><span class="od-flow-label">Close</span><span class="od-flow-sub">Lock the entry list</span></li>
            <li style="--st: var(--chip-generated)"><span class="od-flow-dot">4</span><span class="od-flow-label">Generate</span><span class="od-flow-sub">Draw the heats</span></li>
            <li style="--st: var(--chip-running)"><span class="od-flow-dot">5</span><span class="od-flow-label">Run Live</span><span class="od-flow-sub">Judges score waves</span></li>
        </ol>

        <ul class="od-tips">
            <li>Change status to <strong>open</strong> for participants to join.</li>
            <li>Update status to <strong>closed</strong> when registration ends.</li>
            <li>Organization status <strong>private</strong> hides you from searches.</li>
            <li>Make sure your <strong>time zone</strong> is set correctly.</li>
            <li>Link <strong>team members</strong> to your organization.</li>
            <li>Contact <strong>support</strong> for any billing inquiries.</li>
        </ul>
    </section>

</div>

<style>
    /* ===== Organizer dashboard (Night Swell) ===== */
    .org-dash {
        --od-accent: light-dark(var(--accent), var(--opposite));
        --od-line: color-mix(in oklab, var(--od-accent), transparent 78%);
        --od-surface: light-dark(hsla(0, 0%, 100%, 0.55), hsla(240, 25%, 8%, 0.45));
        --od-ease: cubic-bezier(0.16, 1, 0.3, 1);
        --chip-created: hsl(220, 10%, 62%);
        width: min(1100px, 100%);
        margin-inline: auto;
        display: grid;
        gap: 1.25rem;
    }

    /* Staggered entry */
    .org-dash > * {
        opacity: 0;
        transform: translateY(10px);
        animation: od-in 0.55s var(--od-ease) forwards;
    }
    .org-dash > *:nth-child(2) { animation-delay: 60ms; }
    .org-dash > *:nth-child(3) { animation-delay: 120ms; }
    .org-dash > *:nth-child(4) { animation-delay: 180ms; }
    .org-dash > *:nth-child(5) { animation-delay: 240ms; }
    @keyframes od-in { to { opacity: 1; transform: none; } }

    /* Flash messages */
    .od-flash {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        padding: 0.65rem 1.1rem;
        border-radius: var(--radius);
        border: 1px solid color-mix(in oklab, var(--ok), transparent 55%);
        background: color-mix(in oklab, var(--ok), transparent 90%);
        color: var(--ok);
        font-weight: 700;
        font-size: 0.9rem;
    }

    /* Cards */
    .od-card {
        background: var(--od-surface);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        border: 1px solid var(--od-line);
        border-radius: var(--radius);
        padding: 1.25rem 1.25rem 1.5rem;
        box-shadow: 0 2px 12px light-dark(var(--accent-shadow), hsla(240, 60%, 5%, 0.5));
    }

    /* ===== Hero ===== */
    .od-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        background: var(--od-surface);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        border: 1px solid var(--od-line);
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 12px light-dark(var(--accent-shadow), hsla(240, 60%, 5%, 0.5));
    }
    .od-identity { display: flex; align-items: center; gap: 1rem; min-width: 0; }
    .od-avatar {
        flex: none;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 2px solid color-mix(in oklab, var(--od-accent), transparent 35%);
        box-shadow: 0 0 0 4px color-mix(in oklab, var(--od-accent), transparent 88%);
        background: color-mix(in oklab, var(--od-accent), transparent 90%);
    }
    .od-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .od-avatar span {
        font-size: 1.6rem;
        font-weight: 900;
        color: var(--od-accent);
    }
    .od-identity-text { display: grid; gap: 0.15rem; min-width: 0; }
    .od-eyebrow {
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: color-mix(in oklab, var(--od-accent), transparent 20%);
    }
    .od-org-name {
        font-size: clamp(1.25rem, 3vw, 1.6rem);
        font-weight: 900;
        line-height: 1.15;
        margin: 0;
        border: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .od-org-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.78rem;
        color: color-mix(in oklab, var(--text), transparent 30%);
    }
    .od-sep { width: 3px; height: 3px; border-radius: 50%; background: var(--od-accent); flex: none; }
    .od-org-status { text-transform: capitalize; font-weight: 700; }
    .od-org-status.is-active { color: var(--ok); }
    .od-org-status.is-private, .od-org-status.is-inactive, .od-org-status.is-suspended { color: var(--warn); }

    .od-hero-side {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1.25rem;
        margin-left: auto;
    }
    .od-plan { display: grid; gap: 0.3rem; justify-items: end; }
    .od-plan-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.85rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--od-accent);
        border: 1px solid color-mix(in oklab, var(--od-accent), transparent 50%);
        background: color-mix(in oklab, var(--od-accent), transparent 92%);
    }
    .od-plan-pill.is-pro {
        color: var(--bg);
        background: var(--od-accent);
        box-shadow: 0 1px 8px color-mix(in oklab, var(--od-accent), transparent 50%);
    }
    .od-plan-note { font-size: 0.72rem; color: color-mix(in oklab, var(--text), transparent 35%); }

    .od-cta {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.25rem;
        margin: 0;
        border-radius: 999px;
        border: 1px solid transparent;
        background: var(--od-accent);
        color: var(--bg);
        font-size: 0.9rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        cursor: pointer;
        box-shadow: 0 2px 10px color-mix(in oklab, var(--od-accent), transparent 55%);
        transition: transform 0.15s var(--od-ease), box-shadow 0.15s var(--od-ease), background-color 0.15s var(--od-ease);
    }
    .od-cta:hover {
        transform: translateY(-1px);
        background: var(--od-accent);
        background-image: none;
        color: var(--bg);
        border: 1px solid transparent;
        box-shadow: 0 4px 16px color-mix(in oklab, var(--od-accent), transparent 40%);
    }
    .od-cta:active { transform: translateY(0) scale(0.98); transition-duration: 80ms; }
    .od-cta:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px color-mix(in oklab, var(--od-accent), transparent 50%);
    }

    /* ===== Stats ===== */
    .od-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
    }
    .od-stat {
        display: grid;
        gap: 0.2rem;
        justify-items: center;
        padding: 1rem 0.75rem;
        border-radius: var(--radius);
        border: 1px solid var(--od-line);
        background: var(--od-surface);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        transition: transform 0.2s var(--od-ease), box-shadow 0.2s var(--od-ease), border-color 0.2s var(--od-ease);
    }
    .od-stat:hover {
        transform: translateY(-2px);
        border-color: color-mix(in oklab, var(--od-accent), transparent 50%);
        box-shadow: 0 6px 18px light-dark(var(--accent-shadow), var(--opposite-shadow));
    }
    .od-stat-k {
        font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
        font-variant-numeric: tabular-nums;
        font-size: clamp(1.6rem, 3.5vw, 2.1rem);
        font-weight: 800;
        line-height: 1;
        color: var(--text);
    }
    .od-stat-l {
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: color-mix(in oklab, var(--text), transparent 40%);
    }

    /* ===== Events list ===== */
    .od-event-list { display: grid; grid-template-columns: 1fr; justify-items: stretch; gap: 0.85rem; margin-top: 1.25rem; }
    .od-event {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 1.25rem;
        padding: 0.85rem 1.1rem;
        border-radius: calc(var(--radius) - 4px);
        border: 1px solid var(--od-line);
        border-inline-start: 3px solid color-mix(in oklab, var(--st), transparent 25%);
        background: light-dark(hsla(0, 0%, 100%, 0.4), hsla(240, 25%, 10%, 0.4));
        cursor: pointer;
        transition: transform 0.2s var(--od-ease), box-shadow 0.2s var(--od-ease), border-color 0.2s var(--od-ease);
    }
    .od-event:hover, .od-event:focus-visible {
        transform: translateY(-2px);
        border-color: color-mix(in oklab, var(--st), transparent 45%);
        border-inline-start-color: var(--st);
        box-shadow: 0 6px 18px color-mix(in oklab, var(--st), transparent 80%);
        outline: none;
    }
    .od-event-date {
        display: grid;
        justify-items: center;
        gap: 0.1rem;
        min-width: 52px;
        padding: 0.45rem 0.6rem;
        border-radius: 10px;
        background: color-mix(in oklab, var(--st), transparent 90%);
        border: 1px solid color-mix(in oklab, var(--st), transparent 70%);
    }
    .od-event-day {
        font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1;
        color: var(--text);
    }
    .od-event-mon {
        font-size: 0.6rem;
        font-weight: 700;
        letter-spacing: 2px;
        color: color-mix(in oklab, var(--st), var(--text) 25%);
    }
    /* judges.css sets `section div { justify-content: center }` — restore natural alignment */
    .org-dash .od-event-main { display: grid; gap: 0.25rem; min-width: 0; text-align: left; justify-items: start; justify-content: start; }
    .org-dash .od-event-meta, .org-dash .od-org-meta { justify-content: flex-start; }
    .org-dash .od-event-status { justify-content: end; }
    .od-event-name {
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0;
        padding: 0;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .od-event-year { font-weight: 400; color: color-mix(in oklab, var(--text), transparent 40%); }
    .od-event-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.78rem;
        color: color-mix(in oklab, var(--text), transparent 30%);
    }
    .od-event-status { display: grid; gap: 0.25rem; justify-items: end; }
    .od-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: color-mix(in oklab, var(--st), var(--text) 20%);
        border: 1px solid color-mix(in oklab, var(--st), transparent 55%);
        background: color-mix(in oklab, var(--st), transparent 90%);
    }
    .od-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--st);
        box-shadow: 0 0 6px var(--st);
    }
    .od-stage { font-size: 0.68rem; color: color-mix(in oklab, var(--text), transparent 45%); }
    .od-event-actions {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .od-event-actions .btn { margin: 0; white-space: nowrap; }
    .od-edit-hint {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: color-mix(in oklab, var(--od-accent), transparent 25%);
        opacity: 0.45;
        transition: opacity 0.2s var(--od-ease);
    }
    .od-event:hover .od-edit-hint, .od-event:focus-visible .od-edit-hint { opacity: 1; }

    /* Empty state (neutralise legacy #upcoming_comps > div rule from judges.css) */
    #upcoming_comps > div.od-empty {
        background: none;
        border: none;
        max-width: none;
        -webkit-backdrop-filter: none;
        backdrop-filter: none;
    }
    #upcoming_comps > div.od-empty:hover { border: none; }
    .od-empty {
        display: grid;
        justify-items: center;
        gap: 0.6rem;
        padding: 2.5rem 1rem;
        text-align: center;
        color: color-mix(in oklab, var(--text), transparent 25%);
    }
    .od-empty svg { color: var(--od-accent); opacity: 0.7; }
    .od-empty h3 { padding: 0; font-size: 1.1rem; font-weight: 800; }
    .od-empty-sub { max-width: 36ch; font-size: 0.85rem; margin: 0; }
    .od-empty .od-cta { margin-top: 0.5rem; }

    /* ===== Workflow ===== */
    .od-flow {
        list-style: none;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
        margin: 1.5rem 0 0;
        padding: 0;
        counter-reset: none;
    }
    .od-flow li {
        position: relative;
        display: grid;
        justify-items: center;
        gap: 0.3rem;
        padding: 0.85rem 0.5rem;
        border-radius: calc(var(--radius) - 4px);
        border: 1px solid color-mix(in oklab, var(--st), transparent 75%);
        background: color-mix(in oklab, var(--st), transparent 94%);
    }
    .od-flow-dot {
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 800;
        font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
        color: var(--bg);
        background: color-mix(in oklab, var(--st), var(--text) 10%);
        box-shadow: 0 0 8px color-mix(in oklab, var(--st), transparent 50%);
    }
    .od-flow-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--text);
    }
    .od-flow-sub { font-size: 0.66rem; text-align: center; color: color-mix(in oklab, var(--text), transparent 40%); }

    /* Tips */
    .od-tips {
        list-style: none;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 0.5rem 1.5rem;
        margin: 1.5rem 0 0;
        padding: 0;
        border: none;
        box-shadow: none;
    }
    .od-tips li {
        position: relative;
        padding-left: 1.4rem;
        font-size: 0.82rem;
        text-align: left;
        color: color-mix(in oklab, var(--text), transparent 15%);
    }
    .od-tips li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.3em;
        width: 0.55rem;
        height: 0.3rem;
        border-left: 2px solid var(--od-accent);
        border-bottom: 2px solid var(--od-accent);
        transform: rotate(-45deg);
    }
    .od-tips strong { color: var(--od-accent); font-weight: 700; }

    /* ===== Responsive ===== */
    @media screen and (max-width: 860px) {
        .od-event { grid-template-columns: auto minmax(0, 1fr) auto; }
        .od-event-actions { grid-column: 1 / -1; justify-content: flex-end; padding-top: 0.25rem; border-top: 1px dashed var(--od-line); }
        .od-stage { display: none; }
    }
    @media screen and (max-width: 560px) {
        .od-hero { padding: 1rem; }
        .od-hero-side { width: 100%; justify-content: space-between; margin-left: 0; }
        .od-plan { justify-items: start; }
        .od-event { gap: 0.75rem; padding: 0.75rem; }
        .od-event-status { justify-items: start; grid-column: 2; grid-row: 2; }
        .od-event-date { grid-row: 1 / 3; align-self: start; }
    }

    @media (prefers-reduced-motion: reduce) {
        .org-dash > * { animation: none; opacity: 1; transform: none; }
        .od-cta, .od-stat, .od-event, .od-edit-hint { transition: none; }
    }

    /* ===== Shared form styles (modals opened over this view rely on these) ===== */
    .field {
        transition: all 1s ease;
    }
    .field:hover, .field:focus-within {
        transform: translate(0, 1px);
        background: light-dark(hsla(260, 100%, 50%, 0.03), hsla(185, 100%, 50%, 0.03));
        box-shadow: 2px 4px 8px light-dark(var(--accent-shadow), var(--opposite-shadow));
    }

    form {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }

    @media screen and (max-width: 750px) {
        form {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }
</style>
