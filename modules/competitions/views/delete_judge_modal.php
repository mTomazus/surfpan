<div class="modal-delete-body ">
        <h2 class="mb-0"><span>Delete</span> Judge</h2>
        <p class="mb-1">Remove a judge from your organization.</p>
    <form mx-post="competitions/submit_delete_judge/<?= $update_id ?>" mx-close-on-success="true" mx-target="#response" mx-on-success=".show-judge" >
        <div class="d-grid" style="grid-column:1 / -1;color: var(--text);">
            <p>Are you sure?</p>
            <p class="bm-0">You are about to remove a judge:</p>
            <p class="bt-0 lg"><?= out($name) ?></p>
            <p class="mb-1">This can not be undone.  <strong>Do you really want to do this?</strong></p> 
        </div>
    <?php
        echo form_submit('submit', 'Yes - Delete Now', array("class" => 'modal-delete'));
        echo form_button('close', 'back', ['class' => 'close', 'onclick' => 'closeModal()']);
        echo form_close();
    ?>
</div>