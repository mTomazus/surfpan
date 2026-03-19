<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg1:#0e1a3a;
    --bg2:#291d4d;
    --panel:#ffffff;
    --muted:#8b91a5;
    --text:#1f2333;
    --accent:#a7f19b;
    --primary:darkorchid;
    --accent-strong:#8de07e;
    --ring:rgba(76, 130, 251, .35);
    --shadow: 0 10px 30px rgba(0,0,0,.25);
    --radius:18px;
  }
  *{box-sizing:border-box}
  body{
    margin:0;
    /* background: radial-gradient(1200px 700px at 20% 10%, var(--bg2), var(--bg1)); */
    color: var(--text);
  }
  main {
    min-height: 100vh;
  }

  h2 {
    text-transform: uppercase;
    font-family: inherit;
    text-align: center;
    margin-bottom: 1.5rem;
    & strong {
        background: darkorchid;
        color: white;
        padding-inline: 0.5rem;
    }
  }
  p {
    text-align: center;
    font-size: 0.8rem;
    color: color(srgb 0.6007 0.1983 0.7997);
  }
  .auth-wrapper{
    justify-content: center;
    display: grid;
    place-items: center;
    grid-template-columns: auto;
    padding: 32px 16px;
  }
  .auth-card{
    width:min(980px, 100%);
    display:grid;
    grid-template-columns: auto;
    overflow:hidden;
    background: transparent;
    border-radius: 20px;
    box-shadow:0 0 5px;
  }

  /* Right form panel */
  .panel{
    background: var(--panel);
    border-radius: calc(var(--radius) + 2px);
    box-shadow: var(--shadow);
    padding: 40px 46px 32px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    z-index: 1;
  }
  .fld{
    box-shadow: var(--shadow);
    margin-bottom: 1rem;
    }
  .lbl{
    display:block; font-size:11px; letter-spacing:.6px; color:#8d93a6;
    text-transform:uppercase; margin: 0;padding: 1em 1em 0;
  }
  .txt{
    width:100%; padding: 0 0.6em 0.6em!important; border:none!important;
    outline:none; font-size:15px; color:#1f2333; background:#fff;
    transition:border-color .2s, box-shadow .2s;
  }
  .txt:focus {
    border-color:transparent; box-shadow:none;;
  }

  .btn{
    display: inline-block;
    padding: 14px 20px;
    border: none;
    height: 57px;
    width: 100%;
    box-shadow: var(--shadow);
    font-weight: 700;
    letter-spacing: .6px;
    cursor: pointer;
    transition: transform .04s ease, filter .2s ease;
    text-align: center;
  }
  .main {
    background: rgb(29, 26, 73);
  }
  .btn:active{transform: translateY(1px)}
  .btn:hover{filter:brightness(0.98)}
  .mute{color:#7e8499}
  .link{color:#0f63ff; text-decoration:none}

  .foot{
    margin-top:12px; font-size:13px; color:#7c8298;
  }
  .err{color:#d33; font-size:13px; margin:-6px 0 10px}

  .rel{position:relative}

  /* Responsive */
  @media (max-width: 920px){
    .auth-card{grid-template-columns: 1fr}
    .panel{border-radius: var(--radius)}
  }
</style>

<div class="auth-wrapper">
  <div class="auth-card">
    <!-- RIGHT PANEL -->
    <section class="panel">
      <!-- ===== RESET PASSWORD ===== -->
      <div id="reset-pass" class="form-wrap">
        <h2 class='mt-0'><strong>reset</strong> your password</h2>
        <p>Make it at least 8 character long. </p>
        <?php
            echo validation_errors();
          // Example attributes – tweak as you like
          $attr = ['id'=>'resetForm','autocomplete'=>'off'];
          echo form_open('users/submit_reset', $attr);
        ?>
        <div>
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
        <?php
            echo form_hidden('token', $token);
            echo form_submit('submit', 'RESET PASSWORD', ['class' => 'main btn mt-1']);
            echo form_close();
        ?>
      </div>
    </section>
  </div>
</div> 
