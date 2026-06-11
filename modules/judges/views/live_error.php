<div id="form-container">
    <form style="grid-template-columns: 1fr;">
        <h2><span>Live</span> Judging</h2>
        <?php 
            if (!empty($heat[0])) { 
        ?>
            <p><?= $error ?></p>
            <div>
                <h3 class="xl text-center" style="color:light-dark(var(--accent), var(--opposite))">NEXT HEAT</h3>
                <h3 class="text-center xl"><?= $heat[0]->division ?> · <?= $heat[0]->round ?> · Heat <?= $heat[0]->heat_number ?></h3>

                <?php 
                    // Convert UTC start_time to local organizer timezone
                    $utc   = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $heat[0]->start_time, new DateTimeZone('UTC'));
                    $local = $utc->setTimezone(new DateTimeZone($heat[0]->timezone)); 
                    $local_formated = $local->format('Y M jS');
                ?>

                <h3 class="text-center"><?= $local_formated ?></h3>
                <h3 id="time-left" class="text-center">STARTS IN: <?php if (!empty($heat[0])): ?><?php
                    $start_utc = new DateTimeImmutable($heat[0]->start_time, new DateTimeZone('UTC'));
                    $now_utc   = new DateTimeImmutable('now', new DateTimeZone('UTC'));
                    $diff_secs = max(0, $start_utc->getTimestamp() - $now_utc->getTimestamp());
                    echo gmdate('H:i:s', $diff_secs);
                ?><?php endif; ?></h3>
                <div class="flex justify-center gap-1">
                    <p class="text-center chip" style="background: var(--chip-<?= strtolower($heat[0]->status) ?>); color: white;"><?= $heat[0]->status ?></p>
                    <p class="chip">LOCAL:
                        <?php
                            if ($local !== false) {
                                echo $local->format('H:i');
                            } else {
                                echo '—'; // bad datetime in DB
                            }
                            ?>
                    </p>
                    <p class="chip">UTC: <?= date("H:i", strtotime($heat[0]->start_time)); ?></p>
                </div>
            </div>
        <?php 
            } else {
                echo '<p>' . $error . '</p>';
                echo '<h4 class="text-center">There are no scheduled heats available</h4>';
                echo '<p class="blink lg text-center">Come back later</p>';
            }
        ?>
    </form>
</div>
    
