<div class="change-pass-body ">
        <h2 class="mb-2"><span>Change</span> Password</h2>

<?php

$form_attr = [
    'mx-post' => 'organizations/submit_change_pass',
    'mx-target' => '#response',
    'style' => 'display: grid;grid-template-columns: 1fr;',
    'class' => 'highlight-errors',
    'mx-close-on-success' => 'true'
];
echo form_open('#', $form_attr);
echo '<label for="new_password" class="field">New Password';
echo form_password('new_password', '', ['class' => 'form-control']);
echo '</label><label for="repeat_password" class="field mb-2">Repeat Password';
echo form_password('repeat_password', '', ['class' => 'form-control']);
echo '</label><div style="grid-column:1 / -1;display:flex;gap:1rem;justify-content:space-evenly;">';
echo form_submit('submit', 'Change Password', ['class' => 'btn']);
echo form_button('#', 'Cancel', ['class' => 'btn', 'onclick' => 'closeModal()']);
echo '</div>';
echo form_close();

?>

</div>

<style>
    label {
        position: relative;
    }
    .validation-error-report {
        position: absolute;
        bottom: -23px;
        right: 0;
    }
    .modal .form-field-validation-error, .form-field-validation-error {
        border: none !important;
        background-color: inherit !important;
    }
    .modal .validation-error-report > div, .validation-error-report > div {
        font-size: 11px !important;
    }
</style>