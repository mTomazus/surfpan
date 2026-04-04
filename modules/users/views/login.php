<style>
  :root{
    --bg1:#0e1a3a;
    --bg2:#291d4d;
    --panel:#ffffff;
    --muted:#8b91a5;
    --text:white;
    --primary:rgb(29, 26, 73);
    --accent-strong:#8de07e;
    --ring:rgba(76, 130, 251, .35);
    --shadow: 0 10px 30px rgba(0,0,0,.25);
    --radius:18px;
    --btn--primary:darkorchid;
  }

  h2 {
    text-transform: uppercase;
    font-family: inherit;
    text-align: center;
    margin-bottom: 1.5rem;
    color:black;
    & strong {
        background: var(--accent);
        color: white;
        padding-inline: 0.5rem;
    }
  }
  h2:not(strong){
        color: black;
  }
  hr {
    border: 0;
    height: 1px;
    background: var(--border);
    margin: 1rem 0 2em;
  }
  .auth-wrapper{
    min-height:100%;
    display:grid;
    place-items:center;
    padding:32px 16px;
  }
  .auth-card{
    width: min(868px, 100%);
    display: grid;
    grid-template-columns: 294px 1fr;
    overflow:hidden;
    background: transparent;
    -webkit-backdrop-filter: blur(5px);
    backdrop-filter: blur(5px);
    border: var(--card-border);
    border-radius: var(--radius);
    box-shadow: 0 0px 3px var(--primary-60), 0 0px 3px var(--primary-60) inset;
  }
  .auth-card .panel {
    border:none;
  }

  /* Left promo panel */
  .promo {
    position:relative;
    box-shadow: var(--shadow);
    border-radius: calc(var(--radius) + 2px);
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    padding:18px;
    color:#fff;
  }
  .promo-card {
    position:absolute; inset:0;
    background: rgba(255,255,255,.06);
    border-radius: var(--radius);
    margin:18px;
    backdrop-filter: blur(2px);
  }
  .promo-inner {
    position:relative;
    height:100%;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:1rem;
    text-align:center;
    z-index:2;
    & h1 {
      font-size: 2.1rem;
    }
    & h2 {
      margin:0;
      font-weight:400; 
      letter-spacing:.2px; 
      color:#e7ebff;
      font-size:14px;
      text-align: center;
      border-bottom: none;
      & b {
        display: block;
        font-size: 20px;
        color: #fff;
      }
    }
    & img {
      width:70%;
    }
    & .dot {
      width:40px; height:40px; border-radius:50%;
      display:grid; place-items:center; margin-top:8px;
      background:#0f1633; color:#a1ff8b; box-shadow: inset 0 0 0 2px rgba(255,255,255,.08);
    }
  }

  /* Right form panel */
  .auth-card .panel {
    padding: 32px 24px;
  }
  .form-wrap {
    & h2 {
      font-size: 1.4rem;
      border-bottom: none;
      color: white;
    }
    & .lbl {
    display: block;
    font-size: 11px;
    letter-spacing: 2px;
    color: var(--primary-light);
    text-transform: uppercase;
    margin: 0;
    padding: 1em 1em 0;
    }
    & .txt {
      font-size:16px;
      max-height: 39px;
    }
    & label span {
      font-size: 12px;
      text-transform: uppercase;
      color: var(--opposite);
    }
    & .checkbox-wrapper-30 .checkbox {
      width: calc(var(--size, 1) * 16px);
      margin-bottom: 4px;
    }
    & svg {
      color:var(--accent);
    }
  }
  .fld {
    margin-bottom: 1rem;
    }
  .fld .txt {
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

  .foot a:hover {
    transform:translate(1px);
  }

  .row2{display:flex; gap:10px}
  .row2 > .fld{flex:1}

  .agree, .remember{
    display:flex; align-items:center; gap:10px; font-size:12px; color:#8a90a3;
    margin:0;
  }
  .agree input, .remember input{width:16px; height:16px}

  #loginForm .login-btn, #signupForm .signup-btn {
    display: inline-block;
    position: relative;
    color: var(--bg);
    background: var(--opposite);
    margin-inline-start:2rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    min-width: 100px;
    box-shadow: 0 0 3px var(--primary-60), 0 0 3px var(--primary-60) inset;
    font-size:14px;
    min-height:fit-content;
    font-weight: 500;
    letter-spacing: 2px;
    cursor: pointer;
    transition: all 0.8s ease;
    text-align: center;
  }

  #loginForm .login-btn:active, #signupForm .signup-btn:active {
    transform: translateY(1px);
  }

  #loginForm .login-btn:hover, #signupForm .signup-btn:hover {
    color: var(--opposite);
    background: transparent;
  }
  .mute{color:#7e8499}
  .link{color:#0f63ff; text-decoration:none;cursor: default;}

  .foot{
    font-size: 13px;
    color: var(--primary-dark);
    text-align: right;
  }
  .err{color:#d33; font-size:13px; margin:-6px 0 10px}

  .change-btn {
    background: var(--accent);
    color: var(--primary-white);
    font-size: 0.7rem;
    font-weight: 100;
    letter-spacing: 2px;
    border: 1px solid;
    border-radius: 6px;
    text-decoration: none;
    margin:auto;
    padding: 0.4rem 0.8rem;
    box-shadow: 0 0 3px, 0 0 3px inset;
    transition: all 0.8s ease;
  }
  .change-btn:hover {
    color: var(--accent);
    background: transparent;
    box-shadow: 0 0 3px, 0 0 3px inset;
  }

  /* Success tick style (decorative) */
  .ok{
    position:absolute; right:0; top:34px; transform:translateY(-50%);
    font-size:16px; color:#7bd67a;
  }
  .rel{position:relative}

  /* Responsive */
  @media (max-width: 920px){
    .auth-card{grid-template-columns: 1fr}
    .promo{display:none}
    .row2{flex-direction: column;gap: 0;}
  }
</style>

<div class="auth-wrapper">
  <div class="auth-card">

    <!-- LEFT PROMO -->
    <aside class="promo">
      <div class="promo-card" aria-hidden="true"></div>
      <div class="promo-inner">
        <h1>SURFPAN</h1>
        <div>
          <img src="images/surfpan-hero-2.svg" alt="Logo">
        </div>
        <h2>Competition <b>management</b></h2>
        <div class="dot">➜</div>
      </div>
    </aside>
    
    <!-- RIGHT PANEL -->
    <section class="panel">
      <!-- ===== SIGNUP ===== -->
      <div id="signup" class="form-wrap" style="display:none;">
        <h2 class="mt-0 text-center"><strong>create</strong> your account</h2>
        <div class="foot">We’ll <strong>never</strong> share your email with no one.</div>
        <?php
          // Example attributes – tweak as you like
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
                // build country options array
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

          <label class="mb-1 ml-2 agree">
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

        <div style="display: grid;grid-template-columns: 1fr auto;gap: 1rem;margin-inline: 1rem;">
          <button class="signup-btn" type="submit">CREATE</button>
          <a class="change-btn" href="#" data-target="login">LOGIN</a>
        </div>
        <?= form_close(); ?>
      </div>

      <!-- ===== LOGIN ===== -->
      <div id="login" class="form-wrap">
        <h2 class="mb-1 mt-0 text-center"><strong>login</strong> your account</h2>
        <?php
          flashdata('<p style="color: firebrick;text-align: center;">', '</p>'); // from set_flashdata()
          $attr = ['id'=>'loginForm','autocomplete'=>'off'];
          echo form_open('users/submit_login', $attr); // e.g. modules/members/controllers/Members::submit_login()
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
            <label class="mb-1 ml-2 remember">
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
            <button class="login-btn mt-1 mb-1" type="submit">LOGIN</button>
            <div class="foot">
                <a class="link" mx-get="users/request_modal" mx-build-modal="request-modal">Forgot password?</a>
            </div>
            <hr>
            <a class="change-btn" href="#" data-target="signup" style="float: right;">CREATE ACCOUNT</a>
        <?= form_close(); ?>
      </div>
    </section>
  </div>
</div>

<script>
  // tiny tab switcher
  (function(){
    const switchTo = (name)=>{
      document.querySelectorAll('.tabs a').forEach(a=>{
        a.classList.toggle('active', a.dataset.target===name);
      });
      document.querySelectorAll('.form-wrap').forEach(w=>{
        w.style.display = (w.id===name)?'block':'none';
      });
    };
    document.querySelectorAll('[data-target]').forEach(el=>{
      el.addEventListener('click', e=>{
        e.preventDefault();
        switchTo(el.dataset.target);
      });
    });
  })();
</script>
