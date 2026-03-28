<?php
echo '<h2>Contact <span>Form</span></h2>';
echo '<p>We will return to you shortly.</p>';
$form_attributes = [
    'name' => 'contact_form',
    'id' => 'contact_form',
    'class' => 'contact-form',
];
echo form_open('welcome/submit_form', $form_attributes);
echo '<div class="field">';
echo form_label('Name');
echo form_input('name', '', ['required' => 'required']);
echo '</div><div class="field">';
echo form_label('Email');
echo form_input('email', '', ['required' => 'required', 'type' => 'email']);
echo '</div><div class="field textarea">';
echo form_label('Message');
echo form_textarea('message', '', ['required' => 'required']);
echo '</div><div class="flex mt-1 mb-1">';
echo form_button('back', 'Back', ['type' => 'button', 'mx-get' => 'welcome/contacts', 'mx-build_modal' => 'contact-modal', 'mx-target' => '.modal-body']);
echo form_submit('submit', 'Submit', ['class' => 'primary btn']);
echo '</div>';
echo form_close();
?>
<style>
    .field.textarea {
      height: auto;
      display:grid;
      & label {
          margin-block-start: 0.5rem;
      }
      & textarea {
          background: no-repeat;
          border: none;
          color: var(--menu-hover);
      }
    }
</style>