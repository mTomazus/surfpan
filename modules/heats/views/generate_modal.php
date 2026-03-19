<div class="modal-genetate-body">
    <div id="response"></div>
    <h2 style="font-size: 2rem;text-transform: uppercase;text-align: center;">Generate heats</h2>

    <form mx-post="heats/generate_all_heats/<?= $comp_id ?>" mx-close-on-success="true" mx-target="#response" mx-on-success="#comp-show-table" style="display: grid;grid-template-columns: 1fr;color:white;">
        <p>Are you sure?</p>
        <p>You are about to generate heats for competition.</p>
        <?php
        echo '<div class="generate-heat" style="display:flex;align-items:center;justify-content:space-around;">';
        echo form_label('ELIMINATION:');
        echo form_radio('elimination', 'single', true, ['id' => 'single-input', 'class' => 'entry']);
        echo form_label('single', ['for' => 'single-input']);
        echo form_radio('elimination', 'double', false, ['id' => 'double-input', 'class' => 'entry']);
        echo form_label('double', ['for' => 'double-input']);
        echo '</div>';
        echo form_submit('submit', 'Yes - Generate Now', array("class" => 'modal-generate'));
        echo form_button('close', 'cancel', ['class' => 'close', 'onclick' => 'closeModal()']);
    echo form_close();
    ?>

</div>