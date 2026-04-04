<!DOCTYPE html>
<html lang="en">

	<head><?= Template::partial('partials/judges/judges_head') ?></head>

	<body>
		<div id="mx-loader" class="mx-indicator-hidden"></div>
		<!-- Admin impersonation banner -->
		<?php if (!empty($_SESSION['admin_token_backup'])): ?>
		<div style="position:fixed;bottom:0;left:0;right:0;z-index:9999;background:#b00;color:#fff;text-align:center;padding:8px 16px;font-size:0.88em;display:flex;align-items:center;justify-content:center;gap:16px;">
			<i class="fa fa-eye" style="opacity:0.8;"></i>
			<span>Viewing as <strong><?= out($_SESSION['impersonating_org_name'] ?? 'organization') ?></strong> — admin impersonation active</span>
			<a href="<?= BASE_URL ?>trongate_administrators/stop_impersonating"
			   style="background:#fff;color:#b00;border-radius:4px;padding:3px 10px;text-decoration:none;font-weight:bold;font-size:0.9em;">
				&larr; Return to Admin
			</a>
		</div>
		<div style="height:38px;"></div>
		<?php endif; ?>
		<!-- End of admin impersonation banner -->

		<header><?= Template::partial('partials/judges/judges_header', $data) ?></header>
		
		<?= Template::partial('partials/slide_nav', ['slide_type' => 'judges']) ?>
		
		<aside id="side-nav"><?= Template::partial('partials/judges/judges_aside', $data) ?></aside>
		
		<main class="judges_area"><?= Template::display($data) ?></main>
		
		<footer><?= Template::partial('partials/judges/judges_footer') ?></footer>
		
		<script src="<?= BASE_URL ?>js/trongate-mx.js"></script>
		<script src="<?= BASE_URL ?>js/app.js"></script>
		<script src="<?= BASE_URL ?>js/drag.js"></script>

	</body>

</html>