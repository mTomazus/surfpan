<?php require_once APPPATH . 'modules/lang/controllers/Lang.php'; ?>
<!DOCTYPE html>
<html lang="<?= current_lang() ?>">

	<head><?= Template::partial('partials/users/users_head') ?></head>

	<body>
		<!-- Admin impersonation banner -->
		<?php if (!empty($_SESSION['admin_token_backup'])): ?>
		<div style="position:fixed;bottom:0;left:0;right:0;z-index:9999;background:#b00;color:#fff;text-align:center;padding:8px 16px;font-size:0.88em;display:flex;align-items:center;justify-content:center;gap:16px;">
			<i class="fa fa-eye" style="opacity:0.8;"></i>
			<span><?= t('admin_viewing_as') ?> <strong><?= out($_SESSION['impersonating_org_name'] ?? 'organization') ?></strong> — <?= t('admin_impersonation_active') ?></span>
			<a href="<?= BASE_URL ?>trongate_administrators/stop_impersonating"
			   style="background:#fff;color:#b00;border-radius:4px;padding:3px 10px;text-decoration:none;font-weight:bold;font-size:0.9em;">
				&larr; <?= t('admin_return') ?>
			</a>
		</div>
		<div style="height:38px;"></div>
		<?php endif; ?>
		<!-- End of admin impersonation banner -->

		<?php
		$judge_info = Modules::run("competitions/_get_judge_info");
		if ($judge_info) {
			$data['judge_info'] = $judge_info;
		} else {
			$user_info = Modules::run("users/_get_user_info");
			$data['user'] = $user_info[0];
		}

		?>

		<header><?= Template::partial('partials/users/users_header', $data) ?></header>
		<?= Template::partial('partials/users/users_slide', $data) ?>
		<aside id="side-nav"><?= Template::partial('partials/users/users_aside', $data) ?></aside>

		<main class="users_area"><?= Template::display($data) ?></main>

		<footer><?= Template::partial('partials/users/users_footer') ?></footer>

	</body>

	<script src="<?= BASE_URL ?>js/trongate-mx.js"></script>
	<script src="<?= BASE_URL ?>js/app.js"></script>

</html>