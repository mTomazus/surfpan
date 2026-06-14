<?php
    // --- tiny view helpers (scoped to this render) ---
    $rslt_fmt = fn($v) => number_format((float) $v, 2);
    $rslt_flag = function ($country) {
        $cc = strtoupper(trim((string) $country));
        if (preg_match('/^[A-Z]{2}$/', $cc)) {
            return html_entity_decode(
                '&#' . (127397 + ord($cc[0])) . ';&#' . (127397 + ord($cc[1])) . ';',
                ENT_QUOTES,
                'UTF-8'
            );
        }
        return '';
    };
    $rslt_date = new DateTime($comp_data->end_date ?: 'now');
?>
<div id="results-wrapper" class="rslt">

    <!-- ===== Hero ===== -->
    <div class="rslt-hero">
        <p class="rslt-eyebrow">Final Results</p>
        <h1 class="rslt-title">
            <?= out($comp_data->name) ?> <span class="rslt-year"><?= out($comp_data->year) ?></span>
        </h1>
        <p class="rslt-date"><?= $rslt_date->format('l, F j, Y') ?></p>
        <ul class="rslt-meta">
            <li><span>Location</span><strong><?= out($comp_data->location) ?></strong></li>
            <li><span>Athletes</span><strong><?= (int) $participant_count ?></strong></li>
            <li><span>Divisions</span><strong><?= (int) $divisions_count ?></strong></li>
        </ul>
    </div>

    <?php if (count($divisions) > 1): // division switcher ?>
    <nav class="rslt-tabs" aria-label="Divisions">
        <?php foreach ($divisions as $division):
            $converted = strtolower(str_replace(' ', '_', $division));
            $is_active = ($active_division === $division); ?>
            <button type="button"
                    class="rslt-tab<?= $is_active ? ' is-active' : '' ?>"
                    <?= $is_active ? 'aria-current="true"' : '' ?>
                    mx-get="results/show/<?= $comp_id ?>/<?= $converted ?>"
                    mx-target="#results-wrapper" mx-select="#results-wrapper" mx-push-url="true">
                <?= out($division) ?>
            </button>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <div class="rslt-section-head">
        <h2>Standings</h2>
        <?php if (!empty($active_division)): ?>
            <span class="rslt-div-pill"><?= out($active_division) ?></span>
        <?php endif; ?>
    </div>

    <?php if (empty($results)): ?>

        <div class="rslt-empty">
            <div class="rslt-empty-icon" aria-hidden="true">🏄</div>
            <h3>No results yet</h3>
            <p>Scores for this division haven&rsquo;t been published. Check back after the final.</p>
        </div>

    <?php else:

        $top  = array_slice($results, 0, 3);
        $rest = array_slice($results, 3);
        $podCount = count($top);
        $places = ['gold', 'silver', 'bronze'];
        $labels = ['Champion', 'Runner-up', 'Third'];
        // center the champion when a full podium exists: render 2nd, 1st, 3rd
        $order = ($podCount === 3) ? [1, 0, 2] : range(0, $podCount - 1);
    ?>

        <!-- ===== Podium (top 3) ===== -->
        <section class="rslt-podium rslt-podium--<?= $podCount ?>" aria-label="Podium">
            <?php foreach ($order as $slot):
                $r = $top[$slot];
                $flag = $rslt_flag($r['country']); ?>
                <article class="rslt-pod rslt-pod--<?= $places[$slot] ?>" style="--i:<?= $slot ?>">
                    <div class="rslt-pod-medal" aria-hidden="true"><?= $slot + 1 ?></div>
                    <p class="rslt-pod-place"><?= $labels[$slot] ?></p>
                    <h3 class="rslt-pod-name"><?= out($r['name']) ?></h3>
                    <p class="rslt-pod-country">
                        <?php if ($flag): ?><span class="rslt-flag" aria-hidden="true"><?= $flag ?></span><?php endif; ?>
                        <?= out($r['country'] ?: '—') ?>
                    </p>
                    <div class="rslt-pod-score">
                        <span class="rslt-pod-total"><?= $rslt_fmt($r['total_score']) ?></span>
                        <span class="rslt-pod-total-label">Total Score</span>
                    </div>
                    <div class="rslt-pod-best">
                        <span>Best wave</span><strong><?= $rslt_fmt($r['best_wave']) ?></strong>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <?php if (!empty($rest)): ?>
        <!-- ===== Standings 4th onward ===== -->
        <section class="rslt-list" aria-label="Remaining standings">
            <div class="rslt-row rslt-row--head" aria-hidden="true">
                <span class="rslt-c-rank">#</span>
                <span class="rslt-c-name">Surfer</span>
                <span class="rslt-c-best">Best wave</span>
                <span class="rslt-c-total">Total</span>
            </div>
            <?php $pos = 3; foreach ($rest as $r):
                $pos++;
                $flag = $rslt_flag($r['country']); ?>
                <div class="rslt-row" style="--i:<?= min($pos - 3, 12) ?>">
                    <span class="rslt-c-rank"><?= $pos ?></span>
                    <span class="rslt-c-name">
                        <span class="rslt-name"><?= out($r['name']) ?></span>
                        <span class="rslt-sub">
                            <?php if ($flag): ?><span class="rslt-flag" aria-hidden="true"><?= $flag ?></span><?php endif; ?>
                            <?= out($r['country'] ?: '—') ?>
                        </span>
                    </span>
                    <span class="rslt-c-best"><span class="rslt-lbl">Best</span><?= $rslt_fmt($r['best_wave']) ?></span>
                    <span class="rslt-c-total"><span class="rslt-lbl">Total</span><?= $rslt_fmt($r['total_score']) ?></span>
                </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

    <?php endif; ?>

</div>
