

    <div id="judge_profile" class="span-12 mt-4">
        <h2><span>Edit</span> Profile</h2>
        <p>Keep your profile information up to date.</p>
        <div id="response"></div>
        <?php
            $form_attr = [
                'mx-post' => 'competitions/submit_edit_judge/' . $id,
                'mx-target' => '#response',
                'style' => 'display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: center;'
            ];
            echo form_open('#', $form_attr);
            echo '<label for="full_name" class="field">Full Name:';
            $attr = [
                'placeholder' => 'Enter your full name here...',
                'autocomplete' => 'off',
                'style' => 'margin: auto;appearance: none; text-align: center;',
                'id' => 'full_name'
            ];
            echo form_input('name', $name, $attr);
            echo '</label>';
            echo '<label for="email" class="field">Email:';
            $attr = [
                'placeholder' => 'Enter your email here...',
                'autocomplete' => 'off',
                'style' => 'margin: auto;appearance: none; text-align: center;',
                'id' => 'email'
            ];
            echo form_email('email', $email, $attr);
            echo '</label>';
            
            echo '<label for="phone" class="field">Phone:';
            $attr = [
                'placeholder' => 'Enter your phone here...',
                'autocomplete' => 'off',
                'style' => 'margin: auto;appearance: none; text-align: center;',
                'id' => 'phone'
            ];
            echo form_input('phone', $phone, $attr);
            echo '</label>';
            echo '<div style="display:flex;margin-top:2rem;grid-column: 1 / -1;justify-content: center;gap: 1rem;">';
            echo form_submit('submit', 'Save Changes', ['class' => 'btn primary']);
            echo '<button type="button" class="btn" mx-get="competitions/judge_profile" mx-build-modal="pass-change-modal" mx-select="#change-password-modal">Change Password</button>';
            if (from_trongate_mx()) {
                echo '<button type="button" class="btn" onclick="closeModal()">Close</button>';
            }
            echo '</div>';

            echo form_close();
        ?>
    </div>

<!-- Change Password Modal (hidden by default) -->
<div id="change-password-modal">
    <h2 class="mb-2"><span>Change</span> Password</h2>
        <?php
        $pw_form_attr = [
            'mx-post' => 'competitions/submit_change_password/' . $id,
            'mx-target' => '#response'
        ];
        echo form_open('#', $pw_form_attr);
        echo '<label for="new_password" class="field">New Password:';
        echo form_password('new_password', '', ['placeholder' => 'Enter new password']);
        echo '</label><label for="repeat_password" class="field">Repeat New Password:';
        echo form_password('repeat_password', '', ['placeholder' => 'Repeat new password']);
        echo '</label>';
        
        echo '<div style="display:flex;margin-top:2rem;grid-column: 1 / -1;justify-content: center;gap: 1rem;">';
        echo form_submit('submit', 'Update Password', ['class' => 'btn primary']);
        echo '<button type="button" class="btn" mx-get="competitions/judge_profile" mx-build-modal="user-info-modal" mx-select="#judge_profile" mx-target="#user-info-modal .modal-body">Back</button>';
        echo '<button type="button" class="btn" onclick="closeModal()">Cancel</button>';
        echo '</div>';
        echo form_close();
        ?>
</div>
