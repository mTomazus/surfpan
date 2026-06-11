<div class="modal-delete-body ">
    <h2>DELETE PARTICIPANT</h2>
    <form mx-post="competitions/submit_delete_participant/<?= $update_id ?>" mx-close-on-success="true" mx-target="#response" mx-on-success="#participants-list" mx-animate-success="true">
        <p style="grid-column: -1/1;">Are you sure?</p>
        <p style="grid-column: -1/1;">You are about to delete participant from competition.  This cannot be undone.  Do you really want to do this?</p> 
        <?php
        echo form_submit('submit', 'Yes - Delete Now', array("class" => 'modal-delete'));
        echo form_button('close', 'cancel', ['class' => 'close', 'onclick' => 'closeModal()']);
    echo form_close();
    ?>
</div>