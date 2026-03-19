<h2>Edit participant</h2>
<div id="response"></div>
<form mx-post="competitions/submit_create_participant/<?= $id ?>" mx-target="#response" mx-close-on-success="true" mx-on-success="#participants-list">
    <input type="hidden" name="id" value="<?= $id ?>">
<?php
    echo form_input('name', $name);
    echo form_input('email', $email);
    echo form_input('email', $email);
    echo form_hidden('comp_id', $row->id);
    $division_options = [];
    foreach ($divisions as $division) {
        $division_options[$division->id] = $division->name;
    }
    echo form_dropdown('division_id', $division_options, $division_id  );
?>
    <div class="modal-footer">
        <button type="submit" class="modal-submit" name="update">update</button>
        <button class="close" onclick="closeModal()">close</button>
    </div>
<?php
    echo form_close();
?>