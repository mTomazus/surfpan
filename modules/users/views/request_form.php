<section class="card pad span-12" aria-labelledby="profile-title">
    <h2 style="margin-top:1rem;color: light-dark(var(--primary), var(--opposite));"><?= t('reset_forgot_title') ?></h2>
    <div id="response">
        <p style="color: var(--text);"><?= t('reset_enter_email') ?><br><?= t('reset_send_link') ?></p>
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
        echo form_label(t('reset_email_label'), $label_attr);
        $email_attr = [
            'class' => 'text-center',
            'style' =>'width:65%;margin:auto;background: var(--bg);color: var(--text);font-size: 12px;'
        ];
        echo form_email('email', '', $email_attr);
        echo '</div><div class="spinner mx-indicator"></div>';
        echo form_submit('submit', t('reset_send_btn'), ['class'=>'button', 'style'=>'margin:1rem auto;']);
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


