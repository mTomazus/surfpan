<div id="heat-schedule">
    <div id="just-heats" mx-get="heats/heat_schedule_page" mx-trigger="load" mx-select="#heat-schedule">
        <h2 class="mb-0"><span>Schedule</span> Heats</h2>
        <?php if (empty($heats)) {
            echo '<p class="text-right">No heats available<br>Create competition and generate heats first</p>';
        } else {
            echo '<p class="text-right">Choose start time and length of heat and click save.</p>';
        } ?>

        <div id="#result"></div>
        <div class="wrapper">
        <?php
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
                    $only_time = $dt_local->format('H:i');

                    if (($heat->status === 'pending') || ($heat->status === 'scheduled')) {
                    echo '<section class="heat-length">';
                    echo '<div style="display:flex;gap: 0.1rem;color: var(--primary-60);margin-inline: 1rem 2rem;"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><i class="fa fa-ellipsis-v" aria-hidden="true"></i></div>';
                    // echo '<h4>-' . $heat->name . '-</h4>';
                    echo '<span class="status-chip ' . $heat->status . '">' . $heat->status . '</span>';
                    echo '<div class="grid-1"><h4>Heat ' . $heat->heat_number . '</h4><span class="heat-time">' . $only_time . '</span></div>';
                    echo '<div class="grid-1"><h3 style="text-transform:uppercase;">' . $heat->division . '</h3><h4>' . $heat->round . '</h4></div>';
                    ?>
                    <form mx-post="heats/update_heat_schedule" mx-on-success="#just-heats" mx-target="#result">  
                    <?php
                    $attr['class'] = 'datetime-picker';
                    $attr['type'] = 'text';
                    echo form_input('start_time', $date_time, $attr);
                    $options = array(
                        '10'    => '10 mins.',
                        '15'  => '15 mins.',
                        '20'    => '20 mins.',
                        '25'  => '25 mins.',
                        '30' => '30 mins.',
                    );
                    echo form_dropdown('heat-length', $options, '20');
                    echo form_hidden('heat_id', $heat->id);
                    echo form_submit('submit', 'save');
                    echo form_close();
                    echo '</section>';
                }
            }
        ?>
        <script>
            function setNow(inputName) {
                let now = new Date().toISOString().slice(0, 16).replace("T", " "); // Format YYYY-MM-DD HH:MM
                document.querySelector(`[name="${inputName}"]`).value = now;
            }
        </script>
        <script src="/js/trongate-datetime.js"></script>
        </div>
    </div>
</div>