<div id="judge-login-form">

    <?= flashdata() ?>

    <h1 class="text-center">Login</h1>
    
    <?= validation_errors() ?>

    <p class="text-center">Fill in and press submit to enter competition panel.</p>
    <?php
    echo form_open('competitions/submit_login');
    echo '<div class="">';
    echo form_label('Username');
    $attr['placeholder'] = 'Enter your username here...';
    $attr['autocomplete'] = 'off';
    echo form_input('username', $username, $attr);
    echo '</div>';
    echo '<div class="">';
    echo form_label('Password');
    $attr['placeholder'] = 'Enter your password here...';
    echo form_password('password', '', $attr);
    echo '</div>';
    echo '<div class="">';
    echo form_checkbox('remember');
    echo 'remember me';
    echo '</div>';
    echo '<div class="d-grid">';
    echo form_submit('submit', 'Submit', ['class' => 'mb-1']);
    echo '</div>';
    echo form_close();
    ?>
</div>
