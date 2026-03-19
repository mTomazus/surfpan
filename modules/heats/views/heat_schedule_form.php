<div id="heat-schedule">
    <div id="just-heats" mx-get="heats/heat_schedule_page" mx-trigger="load" mx-select="#heat-schedule">
        <h2 class="mb-0"><span>Schedule</span> Heats</h2>
        <?php if (empty($heats)) {
            echo '<p class="text-right mb-1">No heats available<br>Create competition and generate heats first</p>';
        } else {
            echo '<p class="text-right">Choose start time and length of heat and click save.</p>';
        } ?>
        <!-- Competition schedule control box -->
        <div id="competition-schedule-control" class="mb-1" data-comp-id="<?= $comp_id ?>">
            <div class="comp-time text-center">
                <h3>time left</h3>
                <span><?php 
                $not_finished = $heats[0]->heats_not_finished ?? 0;
                $heat_length = $heats[0]->duration_min ?? 20 ; // Default to 20 minutes if heat_length is not set
                $timeleft = $not_finished * ($heat_length + 5); // Adding 5 minutes buffer time for each heat
                $hours = floor($timeleft / 60);
                $minutes = $timeleft % 60;
                echo sprintf("%02d:%02d", $hours, $minutes);
                ?></span>
            </div>
            <div class="remaining-heats text-center">
                <h3>remaining heats</h3>
                <span> <?= $not_finished ?> / <?= $heats[0]->total_heats ?? 0 ?> Total</span>
            </div>
            <div class="actions">
                <button type="button" class="btn primary" mx-build-modal="auto-schedule" mx-get="heats-schedules/auto_schedule_modal/<?= $comp_id ?>"><i class="fa fa-bolt" aria-hidden="true"></i> Auto Schedule</button>
                <button type="button" class="btn danger" mx-post="heats-schedules/clear_schedule/<?= $comp_id ?>" mx-target="result" mx-on-success="#heat-list"><i class="fa fa-trash" aria-hidden="true"></i> Clear Schedule</button>
            </div>
        </div>

        <!-- Result of saving heat schedule will be shown here -->
        <div id="result"></div>
        <div id="heat-list" class="wrapper" mx-get="heats/heat_schedule_page" mx-trigger="load" mx-select="#heat-list">
        <?= flashdata('<p style="padding: 0.2rem;margin: 0;color: var(--ok);border: 1px dashed;"><i class="mr-2 fa fa-exclamation-triangle" aria-hidden="true"></i>', '<i class="ml-2 fa fa-exclamation-triangle" aria-hidden="true"></i></p>'); ?>
        <?php

            if (!empty($heats) || ($heats[0]->heats_not_finished ?? 0) > 0) {
                echo '<h3 class="heat-list-pending">Pending and <span style="color: var(--chip-scheduled);">Scheduled</span> Heats</h3>';
            
                foreach($heats as $heat) {

                        $orgTz = new DateTimeZone($timezone);
                        if ($heat->start_time === null){
                            $dt_local = new DateTimeImmutable('now', $orgTz);
                        } else {
                            $dt_utc = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $heat->start_time, new DateTimeZone('UTC'));
                            if (!$dt_utc) { throw new Exception('Bad datetime from DB'); }
                            $dt_local = $dt_utc->setTimezone($orgTz);
                        }
                        $date_time = $dt_local->format('Y-m-d H:i');
                        $only_time = $dt_local->format('d M H:i');

                        if (($heat->status === 'pending') || ($heat->status === 'scheduled')) {
                        echo '<section class="heat-length heat-card" data-heat-id="' . $heat->id . '" data-division-id="' . $heat->division . '" data-status="' . $heat->status . '" style="border: 1px solid var(--chip-' . $heat->status . ');box-shadow: 0 0 3px var(--chip-' . $heat->status . '), 0 0 3px var(--chip-' . $heat->status . ') inset;">';
                        echo '<div class="drag-handle" style="color: var(--primary-60);margin-inline: 1rem 2rem;font-size: 1.2rem;font-weight: 900;letter-spacing: -2px;">⋮⋮</div>';
                        // echo '<h4>-' . $heat->name . '-</h4>';
                        echo '<span class="status-chip ' . $heat->status . '">' . $heat->status . '</span>';
                        echo '<div class="grid-1"><h4>Heat ' . $heat->heat_number . '</h4><span class="heat-time">' . $only_time . '</span></div>';
                        echo '<div class="grid-1"><h3 style="text-transform:uppercase;">' . $heat->division . '</h3><h4>' . $heat->round . '</h4></div>';
                        ?>
                        <button type="button"  style="min-width: 110px;" mx-get="heats-schedules/set_time_modal/<?= $comp_id ?>/<?= $heat->id ?>" mx-build-modal="set_time_modal">Set Time</button>
                        <?php
                
                        echo '</section>';
                    }
                }
            };

            if (!empty($heats) || ($heats[0]->total_heats ?? 0) > $not_finished) {
                echo '<h3 class="heat-list-finished">Finished and <span style="color: var(--chip-running);">Running</span> Heats</h3>';
            
                foreach($heats as $heat) {

                        $orgTz = new DateTimeZone($timezone);
                        if ($heat->start_time === null){
                            $dt_local = new DateTimeImmutable('now', $orgTz);
                        } else {
                            $dt_utc = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $heat->start_time, new DateTimeZone('UTC'));
                            if (!$dt_utc) { throw new Exception('Bad datetime from DB'); }
                            $dt_local = $dt_utc->setTimezone($orgTz);
                        }
                        $date_time = $dt_local->format('Y-m-d H:i');
                        $only_time = $dt_local->format('d M H:i');

                        if (($heat->status === 'finished' || $heat->status === 'running')) {
                        echo '<section class="heat-' . $heat->status . '" data-heat-id="' . $heat->id . '" data-division-id="' . $heat->division . '" data-status="' . $heat->status . '" style="border: 1px solid var(--chip-' . $heat->status . ');box-shadow: 0 0 3px var(--chip-' . $heat->status . '), 0 0 3px var(--chip-' . $heat->status . ') inset;">';
                        echo '<div style=";margin-inline: 1rem 2rem;">#</div>';
                        // echo '<h4>-' . $heat->name . '-</h4>';
                        echo '<span class="status-chip ' . $heat->status . '">' . $heat->status . '</span>';
                        echo '<div class="grid-1"><h4>Heat ' . $heat->heat_number . '</h4><span class="heat-time">' . $only_time . '</span></div>';
                        echo '<div class="grid-1"><h3 style="text-transform:uppercase;">' . $heat->division . '</h3><h4>' . $heat->round . '</h4></div>';
                        ?>
                        <a class="btn" href="heats/show_heats_draw/<?= $comp_id ?>/" target="_blank" style="min-width: 110px;" type="button">result</a>
                        <?php
                
                        echo '</section>';
                    }
                }
            };
        ?>
        <script>
            function setNow(inputName) {
                let now = new Date().toISOString().slice(0, 16).replace("T", " "); // Format YYYY-MM-DD HH:MM
                document.querySelector(`[name="${inputName}"]`).value = now;
            }
        </script>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>js/drag.js"></script>