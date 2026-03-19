<section class="card pad span-12" aria-labelledby="profile-title">
    <h2 style="margin-top:1rem;color: light-dark(var(--primary), var(--opposite));">Forgot your password?</h2>
    <div id="response">    
        <p style="color: var(--text);">Enter your email<br>we'll send you a link to restore it</p>
        <?php
        echo validation_errors();
        $form_attr = [
            'mx-post' => 'users/submit_request',
            'mx-target' => '#response',
            'mx-swap' => 'inerHTML',
            'mx-indicator' => '.spinner',
            'style' =>'display:grid;'
        ];
        echo form_open('#', $form_attr);
        echo '<div class="reset-email">';
        $label_attr = [
            'style' =>'color: var(--text);margin-inline: 1rem;'
        ];
        echo form_label('Email', $label_attr);
        $email_attr = [
            'class' => 'text-center',
            'style' =>'width:65%;margin:auto;background: var(--bg);color: var(--text);font-size: 12px;'
        ];
        echo form_email('email', '', $email_attr);
        echo '</div><div class="spinner mx-indicator"></div>';
        echo form_submit('submit', 'Send Me Link', ['class'=>'button', 'style'=>'margin:1rem auto;']);
        echo form_close();
        ?>
    </div>
</section>
<style>
    button {
        width:45%;
        margin-inline:auto;
    }
    .reset-email {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70%;
        margin: 1.5em auto;
        background: var(--card);
    }
</style>


