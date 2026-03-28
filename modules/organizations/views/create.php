<style>
    .create-wrapper {
        width: min(868px, 100%);
        padding: 32px 16px;
        margin: auto;
        & h2 {
            font-size: 1.4rem;
            border-bottom: none;
            text-transform: uppercase;
            color: white;
            margin-top:0;
        }
        & .panel { 
            padding: 32px 24px; 
        }
    }
    h2 strong { background: darkorchid;color: white;padding-inline: 0.5rem; }
    label { display:block; margin:14px 0 6px; font-size:14px; color:var(--text); }
    input { width:100%; padding:10px 12px; border-radius:none; border:none; background:transparent; color:var(--text); }
    .fld:first-child { 
        grid-column: 1/-1; 
        margin-inline: 3rem;
    }
    .fld { margin-bottom: 1rem; }
    .lbl { 
        display: block;
        font-size: 11px;
        letter-spacing: 2px;
        color: var(--primary-light);
        text-transform: uppercase;
        margin: 0;
        padding: 1em 1em 0;
    }
    .fld .txt {
        font-size:16px;
        max-height: 39px;
        color:var(--text); 
        background: var(--bg);
        border: 1px solid var(--primary-60);
        box-shadow: 0 0 3px var(--primary-20), 0 0 3px var(--primary-20) inset;
        transition: all 600ms ease;
    }
    .fld .txt:focus {
        border: 1px solid var(--primary-light);
        box-shadow: 0 0 3px var(--primary-60), 0 0 3px var(--primary-60) inset;
    }
    .fld .txt:hover {
        border: 1px solid var(--primary-light);
        box-shadow: 0 0 3px var(--primary-60), 0 0 3px var(--primary-60) inset;
    }
    form { display: grid; grid-template-columns: 1fr 1fr; column-gap: 1rem;}
    button.create-btn { font-size: 14px; grid-column: 1/-1;margin: 1rem auto; width: 50%; min-height:40px;color: var(--opposite);}
    .create-btn {
        display: inline-block;
        position: relative;
        color: var(--opposite);
        padding: 10px 14px;
        border-radius: 6px;
        height: 40px;
        width: 100%;
        box-shadow: 0 0 3px var(--primary-60), 0 0 3px var(--primary-60) inset;
        font-size:14px;
        min-height:fit-content;
        font-weight: 500;
        letter-spacing: 2px;
        cursor: pointer;
        transition: all 0.8s ease;
        text-align: center;
    }

    .create-btn:active {
        transform: translateY(1px);
    }

    .create-btn:hover {
        color: black;
        background: var(--primary-light);
    }
    .agree span {
        font-size: 12px;
        color: var(--opposite);
        text-transform:uppercase;
        & a {
            color: #0f63ff;;
            text-decoration: none;
        }
    }
    .checkbox {
      color:var(--accent);
    }

    @media screen and (max-width: 768px) {
        form { grid-template-columns: 1fr; }
        .txt { margin-left:0; }
    }
</style>
<div class="create-wrapper">
    <div class="panel">
        <h2 class="text-center"><strong><?= t('org_create_title') ?></strong></h2>
        <div id="response"></div>
        <?php
        validation_errors(); // show validation errors
        flashdata(); // display system messages
        $form_attr = [
            'mx-post' => 'organizations/submit_create',
            'mx-redirect-on-success' => 'true',
            'mx-target' => '#response'
        ];
        echo form_open('#', $form_attr);
        echo '<div class="fld" style="grid-column: 1/-1;">';
        echo form_label(t('org_create_name'), ['class' => 'lbl']);
        echo form_input('organization', '', ['class'=>'txt','maxlength'=>'255', 'required' => 'required', 'placeholder'=>'e.g., Surf Club']);
        echo '</div><div class="fld">';
        echo form_label(t('org_create_email'), ['class' => 'lbl']);
        echo form_email('email', '', ['class'=>'txt','maxlength'=>'255', 'required' => 'required', 'placeholder'=>'e.g., email@example.com']);
        echo '</div><div class="fld">';
        echo form_label(t('org_create_phone'), ['class' => 'lbl']);
        echo form_input('phone', '',['maxlength'=>'50', 'type'=>'tel', 'class'=>'txt', 'placeholder'=>'e.g., +1234567890']);
        echo '</div><div class="fld">';
        echo form_label(t('org_create_address'), ['class' => 'lbl']);
        echo form_input('address', '', ['maxlength'=>'255', 'class'=>'txt', 'placeholder'=>'e.g., 123 Main St, City, State']);
        echo '</div><div class="fld">';
        echo form_label(t('org_create_country'), ['class' => 'lbl']);
        $country_attr = [
            'class' => 'txt',
            'id'    => 'country',
            'style' => 'appearance:none;',
        ];
        // build country options array
        $country_options = [t('org_create_country_select')];
        if (!empty($countries) && is_array($countries)) {
            foreach ($countries as $country) {
                $country_options[$country->code] = $country->name;
            }
        }
        echo form_dropdown('country', $country_options, '-select country-', $country_attr);
        echo '</div><div class="fld">';
        echo form_label(t('org_create_password'), ['class' => 'lbl']);
        echo form_password('password', '', ['required' => 'required', 'class'=>'txt']);
        echo '</div><div class="fld">';
        echo form_label(t('org_create_repeat'), ['class' => 'lbl']);
        echo form_password('repeat_password', '', ['required' => 'required', 'class'=>'txt']);
        echo '</div>
        <label class="mb-1 ml-2 agree" style="grid-column: 1/-1;">
            <div class="checkbox-wrapper-30">
              <span class="checkbox">
                <input type="checkbox" name="agree" value="1" required/>
                <svg>
                  <use xlink:href="#checkbox-30" class="checkbox"></use>
                </svg>
              </span>
              <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
                <symbol id="checkbox-30" viewBox="0 0 22 22">
                  <path fill="none" stroke="currentColor" d="M5.5,11.3L9,14.8L20.2,3.3l0,0c-0.5-1-1.5-1.8-2.7-1.8h-13c-1.7,0-3,1.3-3,3v13c0,1.7,1.3,3,3,3h13 c1.7,0,3-1.3,3-3v-13c0-0.4-0.1-0.8-0.3-1.2"/>
                </symbol>
              </svg>
            </div>
            <span>' . t('org_create_agree') . ' <a class="link" href="welcome/terms">' . t('org_create_terms') . '</a></span>
        </label>';
        echo form_submit('submit', t('org_create_btn'), ['class' => 'create-btn']);
        echo form_close();
        ?>
    </div>
</div>