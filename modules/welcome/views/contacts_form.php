<?php
echo '<h2>Contact <span>Form</span></h2>';
echo '<p>We will return to you shortly.</p>';
echo '<div id="response"></div>';
$form_attr = [
    'mx-post' => 'welcome/submit_form',
    'mx-target' => '#response'
];
echo form_open('#', $form_attr);
echo '</div><div class="field textarea">';
echo form_label('Message');
echo form_textarea('message', '', ['required' => 'required']);
echo '</div><div class="flex mt-1 mb-1">';
echo form_button('back', 'Back', ['type' => 'button', 'mx-get' => 'welcome/contacts', 'mx-build_modal' => 'contact-modal', 'mx-target' => '.modal-body']);
echo form_submit('submit', 'Send', ['class' => 'primary btn']);
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