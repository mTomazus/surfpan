<style>
    /* ============ Night Swell — create organization ============ */
    main.public_area {
        padding: 0;
        width: 100%;
        max-width: 100%;
    }
    .auth-stage {
        position: relative;
        min-height: 100dvh;
        display: grid;
        place-items: center;
        padding: calc(65px + 2rem) 16px 3rem;
        overflow: hidden;
        background: #000208;
    }
    #ocean-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        display: block;
    }
    .auth-stage::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background:
            radial-gradient(55% 40% at 50% 18%, rgba(23,225,239,.08), transparent 70%),
            linear-gradient(to bottom, rgba(0,2,8,.6), transparent 40%, rgba(0,2,8,.7) 100%);
    }

    .create-card {
        position: relative;
        z-index: 1;
        width: min(820px, 100%);
        padding: 2.4rem 2.2rem;
        border-radius: 18px;
        border: 1px solid rgba(23,225,239,.22);
        background: linear-gradient(160deg, rgba(23,225,239,.06), rgba(0,2,8,.72));
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        box-shadow:
            0 30px 80px -30px rgba(0,0,0,.8),
            0 0 40px -18px var(--primary-shadow),
            inset 0 0 30px -22px var(--primary-shadow);
    }

    .form-head { text-align: center; margin-bottom: 1.6rem; }
    .form-head .kicker {
        display: block;
        font-size: .62rem;
        letter-spacing: 6px;
        text-transform: uppercase;
        color: var(--primary);
        margin-bottom: .5rem;
    }
    .form-head h2 {
        font-family: 'Krungthep', 'Lato', sans-serif;
        font-size: 1.7rem;
        text-transform: uppercase;
        margin: 0;
        color: var(--text-light);
    }
    .form-head .sub {
        margin: .6rem auto 0;
        max-width: 64ch;
        font-size: .8rem;
        letter-spacing: 1px;
        color: var(--text-dark);
    }

    form { display: grid; grid-template-columns: 1fr 1fr; column-gap: 1rem; }
    .fld { margin-bottom: 1rem; }
    .lbl {
        display: block;
        font-size: 11px;
        letter-spacing: 2px;
        color: var(--primary-light);
        text-transform: uppercase;
        margin: 0;
        padding: 0 .25rem .35rem;
    }
    .fld .txt {
        width: 100%;
        font-size: 16px;
        max-height: 41px;
        padding: 10px 12px;
        color: var(--text-light);
        background: rgba(0,2,8,.55);
        border: 1px solid rgba(23,225,239,.22);
        border-radius: 8px;
        box-shadow: inset 0 0 12px -8px var(--primary-shadow);
        transition: border-color .3s ease, box-shadow .3s ease, background .3s ease;
    }
    .fld .txt:hover { border-color: var(--primary-60); }
    .fld .txt:focus {
        outline: none;
        border-color: var(--primary);
        background: rgba(0,2,8,.75);
        box-shadow: 0 0 0 3px var(--primary-20), inset 0 0 12px -8px var(--primary-shadow);
    }

    .agree {
        display: flex; align-items: center; gap: 10px;
        font-size: 12px; margin: 0 0 1rem;
    }
    .agree span {
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--text-dark);
        text-transform: uppercase;
    }
    .agree span a { color: var(--primary); text-decoration: none; }
    .agree span a:hover { text-decoration: underline; }
    .checkbox-wrapper-30 .checkbox { width: 16px; margin-bottom: 4px; }
    .create-card svg { color: var(--primary); }

    .create-btn {
        grid-column: 1 / -1;
        display: inline-block;
        margin: .4rem auto 0;
        width: min(320px, 100%);
        padding: .7rem 1.6rem;
        border: 1px solid var(--primary);
        border-radius: 8px;
        background: var(--primary);
        color: #00161a;
        font-weight: 900;
        font-size: 13px;
        letter-spacing: 3px;
        text-transform: uppercase;
        cursor: pointer;
        text-align: center;
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .create-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px -10px var(--primary-shadow);
    }
    .create-btn:active { transform: translateY(0); }

    .foot {
        grid-column: 1 / -1;
        text-align: center;
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--text-dark);
        margin-top: 1rem;
    }
    .foot a { color: var(--primary); text-decoration: none; }
    .foot a:hover { text-decoration: underline; }

    @media screen and (max-width: 768px) {
        form { grid-template-columns: 1fr; }
        .create-card { padding: 1.6rem 1.2rem; }
    }
</style>

<div class="auth-stage">
    <canvas id="ocean-canvas" aria-hidden="true"></canvas>

    <div class="create-card fx-card">
        <div class="form-head">
            <span class="kicker">Run your own events</span>
            <h2>Create organization</h2>
            <p class="sub">Set up your club or federation account — schedule competitions, invite judges, and run live scoring from one panel.</p>
        </div>
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
        echo form_label('Organization name', ['class' => 'lbl']);
        echo form_input('organization', '', ['class'=>'txt','maxlength'=>'255', 'required' => 'required', 'placeholder'=>'e.g., Surf Club']);
        echo '</div><div class="fld">';
        echo form_label('Email (owner)', ['class' => 'lbl']);
        echo form_email('email', '', ['class'=>'txt','maxlength'=>'255', 'required' => 'required', 'placeholder'=>'e.g., email@example.com']);
        echo '</div><div class="fld">';
        echo form_label('Phone', ['class' => 'lbl']);
        echo form_input('phone', '',['maxlength'=>'50', 'type'=>'tel', 'class'=>'txt', 'placeholder'=>'e.g., +1234567890', 'required'=>'required']);
        echo '</div><div class="fld">';
        echo form_label('Address', ['class' => 'lbl']);
        echo form_input('address', '', ['maxlength'=>'255', 'class'=>'txt', 'placeholder'=>'e.g., 123 Main St, City, State']);
        echo '</div><div class="fld">';
        echo form_label('Country', ['class' => 'lbl']);
        $country_attr = [
            'class' => 'txt',
            'id'    => 'country',
            'style' => 'appearance:none;',
        ];
        // build country options array
        $country_options = ['-- Select Country --'];
        if (!empty($countries) && is_array($countries)) {
            foreach ($countries as $country) {
                $country_options[$country->code] = $country->name;
            }
        }
        echo form_dropdown('country', $country_options, '-select country-', $country_attr);
        echo '</div><div class="fld">';
        echo form_label('Password', ['class' => 'lbl']);
        echo form_password('password', '', ['required' => 'required', 'minlength' => '8', 'class'=>'txt']);
        echo '</div><div class="fld">';
        echo form_label('Repeat Password', ['class' => 'lbl']);
        echo form_password('repeat_password', '', ['required' => 'required', 'minlength' => '8', 'class'=>'txt']);
        echo '</div>
        <label class="agree" style="grid-column: 1/-1;">
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
            <span>By creating an account I agree with <a class="link" href="welcome/terms">terms and conditions</a></span>
        </label>';
        echo form_submit('submit', 'Create organization', ['class' => 'create-btn']);
        echo '<div class="foot">Competing, not organising? <a href="users/login">Create an athlete account</a></div>';
        echo form_close();
        ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.149.0/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="js/auth-fx.js"></script>
