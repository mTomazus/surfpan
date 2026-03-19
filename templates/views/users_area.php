<!DOCTYPE html>
<html lang="en">

	<head><?= Template::partial('partials/users/users_head') ?></head>

	<body>
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