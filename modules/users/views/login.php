<style>
  /* ============ Night Swell — auth ============ */
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

  .auth-card {
    position: relative;
    z-index: 1;
    width: min(900px, 100%);
    display: grid;
    grid-template-columns: 320px 1fr;
    overflow: hidden;
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
  .auth-card .auth-panel { border: none; background: transparent; }

  /* ---------- left promo ---------- */
  .promo {
    position: relative;
    padding: 2rem 1.5rem;
    border-right: 1px solid rgba(23,225,239,.14);
    background:
      linear-gradient(rgba(23,225,239,.045) 1px, transparent 1px),
      linear-gradient(90deg, rgba(23,225,239,.045) 1px, transparent 1px),
      linear-gradient(200deg, rgba(23,225,239,.08), transparent 60%);
    background-size: 44px 44px, 44px 44px, auto;
  }
  .promo-inner {
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.1rem;
    text-align: center;
  }
  .promo-kicker {
    font-size: .62rem;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: var(--primary);
  }
  .promo-inner h1 {
    font-family: 'Krungthep', 'Lato', sans-serif;
    font-size: 2.3rem;
    margin: 0;
    letter-spacing: .04em;
    color: var(--text-light);
    text-shadow: 0 0 24px var(--primary-shadow);
  }
  .promo-inner img {
    width: 64%;
    filter: drop-shadow(0 12px 28px rgba(23,225,239,.25));
  }
  .promo-tag {
    margin: 0;
    font-size: .8rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--text-dark);
  }
  .promo-tag b {
    display: block;
    font-size: 1.05rem;
    color: var(--primary);
    letter-spacing: 4px;
  }
  .promo-line {
    width: 4rem;
    height: 2px;
    background: var(--primary);
    box-shadow: 0 0 12px var(--primary-shadow);
  }

  /* ---------- right form panel ---------- */
  .auth-card .auth-panel { padding: 2.2rem 2rem; }

  .form-head { text-align: center; margin-bottom: 1.4rem; }
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
    font-size: 1.6rem;
    text-transform: uppercase;
    margin: 0;
    color: var(--text-light);
  }

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

  .row2 { display: flex; gap: 12px; }
  .row2 > .fld { flex: 1; }
  .rel { position: relative; }

  .agree, .remember {
    display: flex; align-items: center; gap: 10px;
    font-size: 12px; color: var(--text-dark); margin: 0 0 1rem;
  }
  .agree input, .remember input { width: 16px; height: 16px; }
  .agree span, .remember span {
    font-size: 12px; text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-dark);
  }
  .checkbox-wrapper-30 .checkbox { width: 16px; margin-bottom: 4px; }
  .form-wrap svg { color: var(--primary); }

  .auth-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: .5rem;
  }
  .auth-submit {
    display: inline-block;
    padding: .65rem 1.6rem;
    min-width: 130px;
    width: auto;
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
    transition: transform .25s ease, box-shadow .25s ease, background .25s ease, color .25s ease;
  }
  .auth-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px -10px var(--primary-shadow);
  }
  .auth-submit:active { transform: translateY(0); }

  .change-btn {
    display: inline-block;
    padding: .55rem 1rem;
    font-size: 11px;
    letter-spacing: 2px;
    color: var(--primary);
    border: 1px solid var(--primary-60);
    border-radius: 8px;
    text-decoration: none;
    background: transparent;
    transition: background .25s ease, color .25s ease, border-color .25s ease;
  }
  .change-btn:hover { background: var(--primary-20); border-color: var(--primary); }

  .foot { font-size: 13px; color: var(--text-dark); text-align: center; margin-top: 1rem; }
  .foot .link { color: var(--primary); text-decoration: none; cursor: pointer; }
  .foot .link:hover { text-decoration: underline; }
  .link { color: var(--primary); text-decoration: none; }
  .err { color: #ff7a7a; font-size: 13px; margin: -4px 0 10px; }

  hr { border: 0; height: 1px; background: rgba(23,225,239,.15); margin: 1.2rem 0 1rem; }

  @media (max-width: 920px) {
    .auth-card { grid-template-columns: 1fr; }
    .promo { display: none; }
    .row2 { flex-direction: column; gap: 0; }
    .auth-card .auth-panel { padding: 1.6rem 1.2rem; }
    .auth-actions { flex-direction: column; align-items: stretch; }
    .change-btn { text-align: center; }
  }
</style>

<div class="auth-stage">
  <canvas id="ocean-canvas" aria-hidden="true"></canvas>

  <div class="auth-card fx-card">

    <!-- LEFT PROMO -->
    <aside class="promo">
      <div class="promo-inner">
        <span class="promo-kicker">Ride the data</span>
        <h1>SURFPAN</h1>
        <img src="images/surfpan-hero-2.svg" alt="SurfPan logo">
        <span class="promo-line" aria-hidden="true"></span>
        <p class="promo-tag">Competition <b>management</b></p>
      </div>
    </aside>

    <!-- RIGHT PANEL -->
    <section class="auth-panel">
      <!-- ===== SIGNUP ===== -->
      <div id="signup" class="form-wrap" style="display:none;">
        <div class="form-head">
          <span class="kicker">Join the lineup</span>
          <h2>Create account</h2>
        </div>
        <?php
          $form_attr = [
            'id'=>'signupForm',
            'autocomplete'=>'off',
            'mx-post' => 'users/submit_create_account',
            'mx-redirect-on-success' => 'true'
          ];
          echo form_open('#', $form_attr);
        ?>
          <div class="row2">
            <div class="fld">
              <label class="lbl" for="name">Full Name</label>
              <div class="rel">
                <?php
                $name_attr = [
                  'class' => 'txt',
                  'id'    => 'name',
                  'maxlength' => '60',
                  'placeholder' => 'Billy Jones',
                  'required' => 'required'
                ];
                echo form_input('name', '', $name_attr); ?>
              </div>
            </div>
            <div class="fld">
              <label class="lbl" for="email">Email</label>
              <?php $email_attr = [
                'class' => 'txt',
                'id'    => 'email',
                'type'  => 'email',
                'maxlength' => '120',
                'placeholder' => 'anyone@gmail.com',
                'required' => 'required'
                ];
                echo form_input('email', '', $email_attr); ?>
            </div>
          </div>

          <div class="row2">
            <div class="fld">
              <label class="lbl" for="phone">Phone number</label>
              <div class="rel">
                <?php
                $phone_attr = [
                  'class' => 'phone txt',
                  'id'    => 'phone',
                  'maxlength' => '60',
                  'placeholder' => '+306123456789',
                  'type' => 'tel',
                  'required' => 'required'
                ];
                echo form_input('phone', '', $phone_attr); ?>
              </div>
            </div>
            <div class="fld">
              <label class="lbl" for="birth">Birthday</label>
              <?php $birth_attr = [
                'class' => 'txt',
                'id'    => 'birthday',
                'maxlength' => '10',
                'placeholder' => '2010-12-31',
                'required' => 'required'
              ];
              echo form_date('birthday', '', $birth_attr); ?>
            </div>
          </div>

          <div class="row2">
            <div class="fld">
              <label class="lbl" for="gender">Gender</label>
              <div class="rel">
                <?php
                $options = array(
                  'male'  => 'Male',
                  'female'    => 'Female',
                );
                echo form_dropdown('gender', $options, 'male', array('id' => 'gender', 'class'=>'txt', 'style'=>'appearance:none;'));
                ?>
              </div>
            </div>
            <div class="fld">
              <label class="lbl" for="country">Country</label>
              <?php
                $country_attr = [
                  'class' => 'txt',
                  'id'    => 'country',
                  'style' => 'appearance:none;',
                ];
                $country_options = ['-- Select your country --'];
                if (!empty($countries) && is_array($countries)) {
                  foreach ($countries as $c) {
                    $country_options[$c->code] = $c->name;
                  }
                }
                echo form_dropdown('country', $country_options, '-select country-', $country_attr);
              ?>
            </div>
          </div>

          <div class="row2">
            <div class="fld">
              <label class="lbl" for="password">Password</label>
              <div class="rel">
                <input class="txt" type="password" id="password" name="password" minlength="8" required>
              </div>
            </div>
            <div class="fld">
              <label class="lbl" for="repeat_password">Repeat Password</label>
              <div class="rel">
                <input class="txt" type="password" id="repeat_password" name="repeat_password" minlength="8" required>
              </div>
            </div>
          </div>

          <label class="agree">
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
            <span>By signing up I agree with <a class="link" href="welcome/terms">terms and conditions</a></span>
        </label>

        <div class="auth-actions">
          <button class="auth-submit" type="submit">CREATE</button>
          <a class="change-btn" href="#" data-target="login">LOG IN INSTEAD</a>
        </div>
        <div class="foot">We&rsquo;ll <strong>never</strong> share your email with anyone.</div>
        <?= form_close(); ?>
      </div>

      <!-- ===== LOGIN ===== -->
      <div id="login" class="form-wrap">
        <div class="form-head">
          <span class="kicker">Welcome back</span>
          <h2>Log in</h2>
        </div>
        <?php
          flashdata('<p style="color: #ff7a7a;text-align: center;">', '</p>');
          $attr = ['id'=>'loginForm','autocomplete'=>'off'];
          echo form_open('users/submit_login', $attr);
          echo validation_errors('<div class="text-center err"><strong>', '</strong></div>');
        ?>
            <div class="fld">
                <label class="lbl" for="identity">Email</label>
                <input class="txt" type="email" id="identity" name="email" value="<?= out($email) ?>" required>
            </div>

            <div class="fld">
                <label class="lbl" for="login_password">Password</label>
                <input class="txt" type="password" id="login_password" name="password" required>
            </div>
            <label class="remember">
                <div class="checkbox-wrapper-30">
                  <span class="checkbox">
                    <input type="checkbox" name="remember" value="1"/>
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
                <span>remember me</span>
            </label>
            <div class="auth-actions">
              <button class="auth-submit" type="submit">LOGIN</button>
              <a class="change-btn" href="#" data-target="signup">CREATE ACCOUNT</a>
            </div>
            <div class="foot">
                <a class="link" mx-get="users/request_modal" mx-build-modal="request-modal">Forgot password?</a>
            </div>
        <?= form_close(); ?>
      </div>
    </section>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.149.0/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="js/auth-fx.js"></script>
<script>
  // login/signup switcher — animated via authSwitch (auth-fx.js), static fallback otherwise
  document.querySelectorAll('[data-target]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      if (typeof window.authSwitch === 'function') {
        window.authSwitch(el.dataset.target);
      } else {
        document.querySelectorAll('.form-wrap').forEach(w => {
          w.style.display = (w.id === el.dataset.target) ? 'block' : 'none';
        });
      }
    });
  });
</script>
