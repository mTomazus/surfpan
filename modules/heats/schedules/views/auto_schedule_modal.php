<?php
echo '<h2>Auto <span>Schedule</span></h2>';
echo '<p>Automatically generate a schedule based on bellow settings.</p>';

$form_attr = [
    'mx-post' => 'heats-schedules/auto_schedule/'.$comp_id,
    'mx-target' => '#result',
    'mx-on-success' => '#heat-list',
    'mx-close-on-success' => 'true',
    'id' => 'auto-schedule-form',
    'style' => 'display: grid; grid-template-columns: 1fr; gap: 1rem;padding: 0 1rem 1rem;'
    ];
echo form_open('#', $form_attr);
echo '<label class="field" style="color: var(--primary);" for="start_time">Start Time:';
echo '<input id="start_time" class="txt" type="datetime-local" name="start_time" required><i style="font-size:1.2rem" class="fa fa-clock-o" aria-hidden="true"></i></label>';
echo validation_errors('start_time');
echo '<label class="field" style="color: var(--primary);" for="duration">Duration:';
$options = [
    '' => '-- Select Duration --',
    '15' => '15 minutes',
    '20' => '20 minutes',
    '30' => '30 minutes',
    '45' => '45 minutes'
];
$attributes = ['required' => 'required', 'id' => 'duration', 'class' => 'txt'];
echo form_dropdown('duration', $options, '20', $attributes);
echo '<i style="font-size:1.2rem"class="fa fa-caret-down" aria-hidden="true"></i></label>';
echo validation_errors('duration');
echo '<label class="field" style="color: var(--primary);" for="changeover">Changeover Time:';
echo form_number('changeover', '5', ['required' => 'required', 'id' => 'changeover', 'class' => 'txt']);
echo 'minutes </label>';
echo validation_errors('changeover');
echo '<label class="field" style="color: var(--primary);" for="snap_min">Snap Time:';
echo form_number('snap_min', '0', ['required' => 'required', 'id' => 'snap_min', 'class' => 'txt']);
echo 'minutes </label>';
echo validation_errors('snap_min');
echo '<label class="field" style="color: var(--primary);" for="mode">Mode:';
$options = [
    'unscheduled_only' => 'Only unscheduled heats',
    'overwrite' => 'Overwrite existing schedule'
];
echo form_dropdown('mode', $options, 'unscheduled_only', ['id' => 'mode', 'class' => 'txt']);
echo '<i style="font-size:1.2rem"class="fa fa-caret-down" aria-hidden="true"></i></label>';
echo validation_errors('mode');
echo '<p style="font-size: 0.9rem;color: var(--text);">Snap time means that if a heat is scheduled within the snap time window before or after the calculated time, it will be snapped to that time. This can help to create a more compact schedule.</p>';
echo '<div style="display: flex; align-items: center; gap: 0.5rem;margin-inline: auto 0;">';
echo '<a class="btn secondary" style="padding: 0.5rem 1rem;" onclick="closeModal()">Cancel</a>';
echo form_submit('submit', 'Schedule heats', ['class' => 'btn primary', 'style' => 'padding: 0.5rem 1rem;']);
echo '</div>';
echo form_close();