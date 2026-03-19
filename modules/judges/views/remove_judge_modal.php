<div class="modal-delete-body ">
    <h2>Remove Judge From Competition</h2>
    <form class="grid-2" mx-delete="judges/submit_remove_judge/<?= $comp_id ?>/<?= $judge_id ?>" mx-close-on-success="true" mx-target="#response" mx-on-success="#judge-assign">
        <div style="grid-column: 1 / -1;"><p>Are you sure?</p>
        <p>You are about to remove judge from competition.  This cannot be undone.  Do you really want to do this?</p>
        <div class="mt-2 gap-1" style="display:grid;margin-top:1rem;justify-items:center;grid-template-columns:1fr 1fr;">
        <?php
        echo form_submit('submit', 'Yes - Remove Now', array("class" => 'modal-delete'));
        echo form_button('close', 'cancel', ['class' => 'close', 'onclick' => 'closeModal()']);
        echo '</div></div>';
    echo form_close();
    ?>
</div>