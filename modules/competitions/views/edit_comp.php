<h2><span>Edit</span> Competition</h2>
<p>Here you change status of competition.</p>
<div id="response"></div>

<?php
    $form_attr = [
        'mx-post' => 'competitions/submit_create_comp/' . $id,
        'mx-target' => '#response',
        'mx-close-on-success' => 'true',
        'mx-on-success' => '#upcoming_comps',
        'mx-animate-success' => 'true',
        'style' => 'display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: center;'
    ];
    echo form_open('#', $form_attr);
    echo '<label class="field">Name:';
    echo form_input('name', $name);
    echo '</label><div class="grid"><label class="field">Year:';
    echo form_input('year', $year);
    echo '</label><label class="field">Location:';
    echo form_input('location', $location);
    echo '</label></div><div class="grid"><label class="field">Status:';
    if ($status === 'running') {
        $options = ['running' => 'running', 'finished' => 'finished'];
    } elseif ($status === 'generated') {
        $options = ['generated' => 'generated', 'running' => 'running'];
    } elseif ($status === 'created' || $status === 'scheduled') {
        $options = ['created' => 'created', 'scheduled' => 'scheduled', 'open' => 'open'];
    } elseif ($status === 'open' || $status === 'scheduled') {
        $options = ['scheduled' => 'scheduled', 'open' => 'open', 'closed' => 'closed'];
    } elseif ($status === 'closed') {
        $options = ['scheduled' => 'scheduled', 'open' => 'open', 'closed' => 'closed'];
    }
    echo form_dropdown('status', $options, $status);
    echo '</label><label class="field">Entry Type:';
    $options_entry = ['free entry' => 'free entry', 'entry fee' => 'entry fee'];
    echo form_dropdown('entry_type', $options_entry, $entry_type);
    echo '</label></div><div class="grid"><label class="field">Start Date:';
    echo form_input('start_date', $start_date);
    echo '</label><label class="field">End Date:';
    echo form_input('end_date', $end_date);
    echo '</label></div><div style="display:flex;gap:1rem;justify-content:center;margin-top:1.5rem;">';
    echo form_submit('submit', 'update', ['class' => 'btn primary', 'style' => 'flex:1;margin:auto;']);
    echo form_button('close', 'close', ['class' => 'btn close', 'onclick' => 'closeModal()', 'style' => 'flex:1;margin:auto;']);
    echo '</div>';
    echo form_close();
?>

<style>
    label {
        min-width:120px;
        text-align: center;
        margin: 0;
    }
    .grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        width: 100%;
    }
    .field {
        text-wrap: nowrap;
        margin:0;
        & select {
            appearance: none;
            width: auto;
        }
    }

    h2 {
        color: light-dark(var(--accent), var(--opposite));
        text-align: right;
        margin-bottom: 0;
        & span {
            color: var(--text);
        }
    }

    @media screen and (max-width: 755px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }


</style>