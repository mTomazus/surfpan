<div class="modal-delete-body ">
        <h2><span>Delete</span> competition</h2>
    <form mx-post="competitions/submit_delete_comp/<?= $update_id ?>" mx-close-on-success="true" mx-target="#response" mx-on-success="#load-on" mx-animate-success="true">
        <p>Are you sure?</p>
        <p>You are about to delete a competition.  This cannot be undone.  Do you really want to do this?</p> 
        <?php
        echo '<div style="display:flex;gap:1rem;justify-content:center;">';
        echo form_submit('submit', 'Yes - Delete Now', array("class" => 'modal-delete'));
        echo form_button('close', 'cancel', ['class' => 'close', 'onclick' => 'closeModal()']);
        echo '</div>';
    echo form_close();
    ?>
</div>