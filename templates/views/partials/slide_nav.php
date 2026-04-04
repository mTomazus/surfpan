<?php $slide_type = $slide_type ?? 'users'; ?>
<div id="slide-nav" class="slide justify-content-around"<?php
    if ($slide_type === 'judges'):
        ?> mx-get="competitions" mx-select=".side-nav" mx-trigger="load" mx-target="#result"<?php
    elseif ($slide_type === 'users'):
        ?> mx-get="users" mx-select=".side-nav" mx-trigger="load"<?php
    endif; ?>>
    <?php if ($slide_type === 'judges' || $slide_type === 'public'): ?>
        <div id="result"></div>
    <?php endif; ?>
    <?php if ($slide_type === 'public'): ?>
        <div class="slide-header"></div>
        <?= anchor('welcome', 'HOME', ['class' => 'side-button']) ?>
        <?= anchor('users/athletes', 'ATHLETES', ['class' => 'side-button']) ?>
        <?= anchor('organizations', 'ORGANIZERS', ['class' => 'side-button']) ?>
        <?= anchor('heats', 'EVENTS', ['class' => 'side-button']) ?>
        <?= anchor('users', 'LOGIN', ['class' => 'login-link']) ?>
        <div class="slide-footer"></div>
    <?php endif; ?>
</div>
