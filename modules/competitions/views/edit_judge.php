<h2><span>Edit</span> <?= out($name) ?></h2>
<p>You have rights to update status and role.</p>
<div id="response"></div>
<form mx-post="competitions/submit_edit_judge/<?= $id ?>" mx-target="#response" mx-close-on-success="true" mx-animate-success="true" mx-on-success=".show-judge">
<?php
    echo '<div class="field">';
    $attr = ['disabled' => true];
    echo form_label('Full name:');
    echo form_input('name', $name, $attr);
    echo '</div><div class="field">';
    echo form_label('Email:');
    echo form_input('email', $email, $attr);
    echo '</div><div class="field">';
    echo form_label('Phone:');
    echo form_input('phone', $phone, $attr);
    echo '</div><div class="field">';
    echo form_label('Created:');
    echo form_input('date_created', date('Y-m-d', strtotime($date_created)), $attr);
    echo '</div><div class="field">';
    echo form_label('Role:');
    $options = ['head_judge' => 'Head Judge', 'judge' => 'Judge'];
    echo form_dropdown('role', $options, $role, ['id' => 'role-select', 'class' => 'judge-role', 'style' => 'appearance: none;']);
    echo '</div><div class="field">';
    echo form_label('Status:');
    $opt_status = ['active' => 'Active', 'suspended' => 'Suspended'];
    echo form_dropdown('status', $opt_status, $status, ['id' => 'status-select', 'class' => 'judge-role', 'style' => 'appearance: none;']);
    echo '</div>';
?>
    <div class="modal-footer">
        <button type="submit" class="modal-submit btn primary" name="update">update</button>
        <button class="close" onclick="closeModal()">close</button>
    </div>
<?php
    echo form_close();
?>