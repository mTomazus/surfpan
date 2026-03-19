<div id="full-draw">
    <div id="heat-draw">

        <h1 class="comp-title"><?= out($comp_name) ?></h1>

        <a href="organizations/show/<?= out($org['slug']) ?>" class="org-link">
            <?= out($org['organization']) ?>
        </a>

        <h3 class="draw-subtitle">Complete Heat Draw</h3>

        <?php if (!empty($errors)): ?>
            <div class="generation-errors">
                <?php foreach ($errors as $err): ?>
                    <p><?= out($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="container-xxl">

            <?php if (count($divisions) > 1): ?>
                <!-- Button nav: larger screens -->
                <nav class="div-nav div-nav--buttons">
                    <?php foreach ($divisions as $label => $value): ?>
                        <button mx-get="heats/show_heats_draw/<?= $comp_id ?>/<?= $value ?>"
                                mx-target=".wrapper" mx-select=".wrapper"
                                mx-push-url="true" class="btn">
                            <?= out($label) ?>
                        </button>
                    <?php endforeach; ?>
                </nav>

                <!-- Select nav: small screens -->
                <label class="field div-nav--select">
                    <i class="fa fa-arrow-right"></i>
                    <select mx-get="heats/show_heats_draw/<?= $comp_id ?>/${this.value}"
                            mx-target=".wrapper" mx-select=".wrapper"
                            mx-trigger="change">
                        <?php foreach ($divisions as $label => $value): ?>
                            <option value="<?= $value ?>"><?= out($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>

            <div class="wrapper">
                <?php foreach ($heats as $heat):
                    $is_active = in_array($heat['status'], ['running', 'finished']);
                    $open_tag  = $is_active
                        ? '<div class="card-heat card-heat--active" mx-build-modal="heatScore" mx-get="heats/heat_scores/' . $heat['id'] . '">'
                        : '<div class="card-heat">';
                    echo $open_tag;
                ?>

                    <!-- Heat Header -->
                    <div class="heat-header">
                        <h2 class="heat-title">
                            <?php if ($heat['round'] === 'Final'): ?>
                                <?= out($heat['round']) ?>
                            <?php else: ?>
                                <?= out($heat['round']) ?> <span class="heat-number">H<?= out($heat['heat_number']) ?></span>
                            <?php endif; ?>
                        </h2>
                        <div class="heat-meta">
                            <?= Modules::run('heats/render_heat_status', $heat, $timezone) ?>
                        </div>
                    </div>

                    <!-- Participants Table — filled slots + pending source placeholders -->
                    <table class="heat-table">
                        <tbody>
                        <?php
                        $ordinals      = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'];
                        $filled_count  = count($heat['participants']);
                        $is_final      = $heat['round'] === 'Final';

                        // Filled slots — real participants
                        foreach ($heat['participants'] as $participant):
                            $rank  = $participant['rank']        ?? null;
                            $score = $participant['total_score'] ?? null;
                            $route = ($rank && isset($heat['routes'][$rank]))
                                     ? $heat['routes'][$rank] : null;
                        ?>
                            <tr class="<?= $rank ? 'has-result' : '' ?>">
                                <td><div class="jersey <?= out($participant['jersey_color']) ?>"></div></td>
                                <td class="participant-name"><?= out($participant['name']) ?></td>
                                <td class="participant-score"><?= $score !== null ? out($score) : '' ?></td>
                                <td>
                                    <?php if ($rank): ?>
                                        <span class="rank-badge <?= $is_final ? 'rank-badge--final' : 'rank-badge--heat' ?>">
                                            <?= out($rank) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php
                        // Pending slots — sources not yet filled by a participant
                        $seeded_from_ids = array_map('intval', array_column($heat['participants'], 'seeded_from'));
                        $pending_sources = array_filter(
                            $heat['sources'],
                            fn($src) => !in_array((int)$src['from_heat_id'], $seeded_from_ids, true)
                        );
                        foreach ($pending_sources as $src):
                            $label = Modules::run('heats/abbr_round', $src['from_round'])
                                   . ' H' . $src['from_heat_number']
                                   . ' ' . ($ordinals[$src['from_position']] ?? $src['from_position'] . 'th');
                        ?>
                            <tr class="slot--pending">
                                <td><div class="jersey jersey--tbd"></div></td>
                                <td class="participant-name slot-label"><?= out($label) ?></td>
                                <td></td><td></td><td></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table> 
                </div><!-- /.card-heat -->
                <?php endforeach; ?>
            </div><!-- /.wrapper -->

        </div><!-- /.container-xxl -->
    </div><!-- /#heat-draw -->
</div><!-- /#full-draw -->

<style>
    * { box-sizing: border-box; }

    /* ---- Competition header ---- */
    .comp-title {
        font-size: 2.5rem;
        text-align: center;
        background: linear-gradient(to right, red, rgb(152,0,104) 52.1%, purple);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: Impact, sans-serif;
        margin: 1rem 1rem 0;
    }
    .org-link {
        display: flex;
        justify-content: center;
        text-align: center;
        margin: 1rem auto;
        padding: 0.5rem 2rem;
        border: 1px solid;
        width: fit-content;
        text-decoration: none;
        box-shadow: 0 2px 5px;
        text-transform: uppercase;
    }
    .draw-subtitle {
        text-align: center;
        margin: 0;
    }

    /* ---- Generation errors ---- */
    .generation-errors {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        margin: 1rem auto;
        max-width: 600px;
        color: #856404;
        font-size: 0.85rem;
    }
    .generation-errors p { margin: 0.2rem 0; }

    /* ---- Division navigation ---- */
    .div-nav { padding: 1rem 0; margin: auto 2rem; }
    .div-nav--buttons {
        display: none;
        justify-content: center;
        gap: 1rem;
    }
    .div-nav--buttons .btn {
        background: var(--bg);
        box-shadow: 0 2px 6px var(--text);
        border: 1px solid;
        color: var(--text);
        cursor: pointer;
        border-radius: 5px;
        transition: all 0.3s;
    }
    .div-nav--buttons .btn:hover {
        background: var(--text);
        color: var(--bg);
        box-shadow: 0 2px 8px var(--text);
    }
    .div-nav--select {
        display: flex;
        width: auto;
        margin: 1rem auto;
        align-items: center;
        justify-content: center;
        background: var(--bg-dark);
    }
    .div-nav--select i {
        padding-inline: 0.5rem;
        color: light-dark(var(--accent), var(--opposite));
    }
    @media (min-width: 768px) {
        .div-nav--buttons { display: flex; }
        .div-nav--select  { display: none; }
    }

    /* ---- Heat grid ---- */
    .wrapper {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        margin-bottom: 2rem;
    }

    /* ---- Heat card ---- */
    .card-heat {
        background: rgba(255,255,255,0.05);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .card-heat--active { cursor: pointer; }
    .card-heat--active:hover { border-color: rgba(255,255,255,0.3); }

    .heat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0.75rem;
        background: rgba(255,255,255,0.07);
        & .heat-title {
            font-size: 1rem;
            color: var(--alt);
            border-bottom: 1px dashed;
            margin: 0;
            & span {
                font-size: 0.8rem;
                color: var(--primary);
            }
        }
    }
    .heat-number { font-size: 0.8rem; opacity: 0.7; }
    .heat-meta { text-align: right; }
    .heat-division { margin: 0; font-size: 0.7rem; opacity: 0.7; text-transform: uppercase; }
    .participant-name { font-weight: 700;font-family: "Lato", sans-serif; }
    /* ---- Status badges ---- */
    .status { margin: 0; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
    .status--ended    { color: crimson; }
    .status--soon     { color: var(--white, #fff); }
    .status--scheduled{ color: yellow; text-align: right; }
    .status--live {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: flex-end;
        color: greenyellow;
    }

    /* ---- Table ---- */
    .heat-table {
        width: 100%;
        border-collapse: collapse;
    }
    .heat-table tbody { font-weight: 900; font-family: Baskerville, serif; }
    .heat-table td {
        border: none;
        text-transform: uppercase;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        vertical-align: middle;
    }
    .participant-name  { flex: 1; }
    .participant-score { text-align: right; opacity: 0.8; }

    /* ---- Rank badge ---- */
    .rank-badge {
        display: inline-block;
        font-size: 0.75rem;
        padding: 0.1rem 0.4rem;
        border-radius: 3px;
        color: white;
        font-weight: 700;
    }
    .rank-badge--final { background: crimson; }
    .rank-badge--heat  { background: cornflowerblue; }

    /* ---- Advancement destination ---- */
    .dest {
        font-size: 0.62rem;
        padding: 0.1rem 0.35rem;
        border-radius: 3px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .dest--adv { background: rgba(134,239,172,0.15); color: #86efac; border: 1px solid #86efac55; }
    .dest--out { background: rgba(248,113,113,0.15); color: #f87171; border: 1px solid #f8717155; }

    /* ---- Unseeded slots ---- */
    .heat-table--unseeded { opacity: 0.55; }
    .slot-label { font-style: italic; color: #94a3b8; letter-spacing: 0.03em; }
    .slot--pending td { opacity: 0.5; }
    .jersey--tbd {
        background: repeating-linear-gradient(
            45deg, #334155, #334155 4px, #1e293b 4px, #1e293b 8px
        );
        box-shadow: none;
        border: 1px solid #475569;
    }

    /* ---- Route legend (unscored heats) ---- */
    .route-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
        padding: 0.4rem 0.6rem;
        border-top: 1px solid rgba(255,255,255,0.07);
    }
    .route-legend__item {
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.15rem 0.4rem;
        border-radius: 3px;
    }
    .route-legend__item--adv { background: rgba(134,239,172,0.1); color: #86efac; border: 1px solid #86efac44; }
    .route-legend__item--out { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid #f8717144; }

    #scores-section {
        font-size: 0.75rem;
        font-family: "Lota";
        font-weight: 900;
        padding: 1rem;
        border: 1px solid;
        border-radius: var(--radius);
        background: hsla(0, 9%, 0.6%, 0.6);
    }

    /* ---- Jersey circles ---- */
    .jersey {
        width: 28px; height: 28px;
        border-radius: 50%;
        margin: auto;
        border: 1px solid black;
        box-shadow: 0 0 5px;
    }
    .white { background: white; box-shadow: 0 0 5px #888; }
    .red   { background: radial-gradient(circle at 40% 40%, rgba(251,80,54,.8), rgb(255,51,51) 82%, rgb(152,2,2)); box-shadow: 0 0 5px red; }
    .green { background: radial-gradient(circle at 40% 40%, #01d42c, rgb(77,144,10) 79%, #044700); box-shadow: 0 0 5px green; }
    .blue  { background: radial-gradient(circle at 40% 40%, #5cabff, rgb(10,118,233) 79%, #2216a6); box-shadow: 0 0 5px blue; }
</style>