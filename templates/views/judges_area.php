<!DOCTYPE html>
<html lang="en">

	<head><?= Template::partial('partials/judges/judges_head') ?></head>

	<body>

		<header><?= Template::partial('partials/judges/judges_header', $data) ?></header>
		
		<?= Template::partial('partials/judges/judges_slide', $data) ?>
		
		<aside id="side-nav"><?= Template::partial('partials/judges/judges_aside', $data) ?></aside>
		
		<main class="judges_area"><?= Template::display($data) ?></main>
		
		<footer><?= Template::partial('partials/judges/judges_footer') ?></footer>
		
		<script src="<?= BASE_URL ?>js/trongate-mx.js"></script>
		<script src="<?= BASE_URL ?>js/app.js"></script>
		<script src="<?= BASE_URL ?>js/drag.js"></script>

	</body>

</html>