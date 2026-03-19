<div class="modal-regen-body ">
        <h2 class="mb-0"><span>Heats</span> Regeneration</h2>
        <p class="mb-1">Regenerate all heats for the selected competition.</p>
    <form mx-delete="heats/regenerate/<?= $update_id ?>" mx-close-on-success="true" mx-target="#response" mx-on-success="#generation-table" >
        <div class="d-grid" style="grid-column:1 / -1;color: var(--text);">
            <p style="text-align: center;font-family: 'lato';font-size: 1.5rem;margin-block-end:0;">Are you sure?</p>
            <p class="bm-0">You are about to regenerate all heats for </p>
            <p class="bt-0 lg mb-2"><?= out($name) ?></p>
        </div>
    <?php
        echo '<div style="display: flex;gap: 1rem;max-width: 500px;margin: auto;" class="mb-1">';
        echo form_submit('submit', 'Yes - Regenerate Now', array("class" => 'primary btn', 'style' => 'margin: 0;'));
        echo form_button('close', 'back', ['class' => 'close', 'onclick' => 'closeModal()']);
        echo '</div>';
        echo form_close();
    ?>
</div>