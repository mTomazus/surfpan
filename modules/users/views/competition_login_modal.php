<?php
/**
 * FILE: modules/users/views/competition_login_modal.php
 * PURPOSE: Shown inside the competitionModal when a logged-OUT visitor presses
 *          "Register"/"Action" on a competition. Replaces the old behaviour of
 *          redirecting to (and dumping) the full login page inside the modal.
 *
 * Expects: $competition (object, optional) — for contextual heading.
 * Visual language mirrors modules/users/views/login.php (Night Swell auth).
 */
$cl_comp = isset($competition->name)
    ? trim(out($competition->name) . ' ' . out($competition->year ?? ''))
    : '';
?>
<div class="cl-modal">

    <div class="cl-head">
        <span class="cl-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <span class="cl-kicker">Members only</span>
        <h2 class="cl-title">Log in to register</h2>
        <?php if ($cl_comp): ?>
            <p class="cl-sub">for <strong><?= $cl_comp ?></strong></p>
        <?php else: ?>
            <p class="cl-sub">Sign in to enter this competition.</p>
        <?php endif; ?>
    </div>

    <?php
        flashdata('<p class="cl-err" role="alert">', '</p>');
        echo validation_errors('<p class="cl-err" role="alert">', '</p>');
        echo form_open('users/submit_login', ['id' => 'clLoginForm', 'autocomplete' => 'off']);
    ?>
        <div class="cl-fld">
            <label class="cl-lbl" for="cl_email">Email</label>
            <input class="cl-txt" type="email" id="cl_email" name="email" placeholder="you@email.com" required>
        </div>

        <div class="cl-fld">
            <label class="cl-lbl" for="cl_pass">Password</label>
            <input class="cl-txt" type="password" id="cl_pass" name="password" required>
        </div>

        <label class="cl-remember">
            <input type="checkbox" name="remember" value="1">
            <span>Remember me</span>
        </label>

        <button class="cl-submit" type="submit">Log in</button>
    <?= form_close(); ?>

    <div class="cl-foot">
        <a class="cl-link" href="<?= BASE_URL ?>login">Create account</a>
        <span class="cl-dot" aria-hidden="true"></span>
        <a class="cl-link" href="<?= BASE_URL ?>login">Forgot password?</a>
    </div>

    <button type="button" class="cl-cancel" onclick="closeModal()">Cancel</button>
</div>

<style>
/* =========================================================================
   COMPETITION LOGIN MODAL — Night Swell (mirrors users/login.php)
   ========================================================================= */
.cl-modal {
    --cl-line: rgba(23, 225, 239, .22);
    width: min(360px, 100%);
    margin: 0 auto;
    text-align: center;
}

.cl-head { display: grid; justify-items: center; gap: .35rem; margin-bottom: 1.25rem; }
.cl-icon {
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    color: var(--primary);
    background: var(--primary-20);
    border: 1px solid var(--cl-line);
    box-shadow: 0 0 24px -10px var(--primary-shadow);
    margin-bottom: .35rem;
}
.cl-kicker {
    font-size: .6rem;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: var(--primary);
}
.cl-title {
    font-family: 'Krungthep', 'Lato', sans-serif;
    font-size: 1.5rem;
    text-transform: uppercase;
    margin: 0;
    color: var(--text-light);
    text-shadow: 0 0 24px var(--primary-shadow);
}
/* .cl-head specificity beats global `.modal-body h2 + p { margin-bottom:3rem; text-align:right }` */
.cl-head .cl-sub { margin: 0; font-size: .82rem; color: var(--text-dark); text-align: center; }
.cl-sub strong { color: var(--primary-light); font-weight: 700; }

#clLoginForm { display: grid; gap: .85rem; text-align: left; }

.cl-fld { display: grid; gap: .35rem; }
.cl-lbl {
    font-size: 11px;
    letter-spacing: 2px;
    color: var(--primary-light);
    text-transform: uppercase;
    padding: 0 .25rem;
}
.cl-txt {
    width: 100%;
    font-size: 16px;
    padding: 11px 12px;
    color: var(--primary-white); /* field is always dark — keep text light in both themes */
    background: rgba(0, 2, 8, .55);
    border: 1px solid var(--cl-line);
    border-radius: 8px;
    box-shadow: inset 0 0 12px -8px var(--primary-shadow);
    transition: border-color .25s ease, box-shadow .25s ease, background .25s ease;
}
.cl-txt:hover { border-color: var(--primary-60); }
.cl-txt:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(0, 2, 8, .75);
    box-shadow: 0 0 0 3px var(--primary-20), inset 0 0 12px -8px var(--primary-shadow);
}

.cl-remember {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-dark);
    cursor: pointer;
    margin: -.15rem 0 .25rem;
}
.cl-remember input { width: 16px; height: 16px; accent-color: var(--primary); }
/* override global `label span { color: green }` from trongate.css */
.cl-remember span { color: var(--text-dark); }

.cl-submit {
    width: 100%;
    min-width: 0;
    max-height: none;
    margin: 0;
    padding: .8rem 1rem;
    border: 1px solid var(--primary);
    border-radius: 8px;
    background: var(--primary);
    color: #00161a;
    font-weight: 900;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    box-shadow: 0 0 4px var(--primary-shadow);
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
}
.cl-submit:hover {
    transform: translateY(-2px);
    background: var(--primary-light);
    color: #00161a;
    box-shadow: 0 10px 28px -10px var(--primary-shadow);
}
.cl-submit:active { transform: translateY(0); }

.cl-foot {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .75rem;
    margin-top: 1.1rem;
    font-size: 12px;
}
.cl-link { color: var(--primary); text-decoration: none; }
.cl-link:hover { color: var(--primary-light); text-decoration: underline; }
.cl-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--primary-60); }

.cl-cancel {
    min-width: 0;
    max-height: none;
    margin: 1rem auto 0;
    padding: .5rem 1.4rem;
    border: 1px solid var(--primary-60);
    border-radius: 8px;
    background: transparent;
    color: var(--primary);
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background .2s ease, border-color .2s ease;
}
.cl-cancel:hover { background: var(--primary-20); border-color: var(--primary); color: var(--primary); }

.cl-err {
    color: #ff7a7a;
    font-size: 13px;
    text-align: center;
    margin: 0 0 .9rem;
}
</style>
