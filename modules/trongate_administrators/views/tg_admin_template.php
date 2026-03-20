<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<base href="<?= BASE_URL ?>">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="<?= BASE_URL ?>css/trongate.css">
		<title>Trongate Administrators</title>
	</head>
	<style>
		:root {
			--primary: hsl(0, 0%, 90%);
			--primary-60: hsla(0, 0%, 93%, 0.6);
			--ok: hsl(122, 39%, 49%);
			--chip-pending: rgb(255, 152, 0);
			--chip-scheduled: hsl(200, 100%, 50%);
			--chip-running: hsl(122, 39%, 49%);
			--chip-finished: hsl(0, 0%, 62%);
			--secondary-60: hsla(165, 100%, 50%, 0.6);
			--secondary-20: hsla(165, 100%, 50%, 0.2);
			--secondary-th: hsla(165, 100%, 35%, 1);
			--secondary: hsla(165, 100%, 50%, 1);

		}
		body {
			background: rgb(0, 0, 0);
			background: -webkit-linear-gradient(rgba(0, 0, 0, 1) 0%, rgba(51, 51, 51, 1) 35%, rgba(111, 111, 111, 1) 100%);
			background: -o-linear-gradient(rgba(0, 0, 0, 1) 0%, rgba(51, 51, 51, 1) 35%, rgba(111, 111, 111, 1) 100%);
			background: linear-gradient(rgba(0, 0, 0, 1) 0%, rgba(51, 51, 51, 1) 35%, rgba(111, 111, 111, 1) 100%);
			background-repeat: no-repeat;
			background-size: cover;
			min-height: 100vh;
			color: #eee;
			font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
		}

		h1 {
			font-size: 2.4em;
		}

		h2 {
			font-size: 2.0em;
		}

		h3 {
			font-size: 1.7em;
		}

		h4 {
			font-size: 1.5em;
		}

		h5 {
			font-size: 1.2em;
		}

		#top-gutter {
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			line-height: 2.4em;
			padding: 0 12px;
			text-align: center;
		}

		#top-gutter>div:nth-child(1) {
			text-align: left !important;
		}
		#top-gutter a {
			text-decoration: none;
			color: #eee;
			padding: 0 8px;
		}

		#top-gutter a:hover {
			color: cyan;
		}
		header {
			position: fixed;
			width: -webkit-fill-available;
			background: transparent;
			-webkit-backdrop-filter: blur(5px);
			height: 45px;
			text-align: center;
			top:0;
		}
		main {
			
		}
		.container {
			background-color: transparent;
			margin-top: 40px;
		}

		.hide-sm {
			display: none;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			background: rgba(255,255,255,0.05);
			border-radius: 8px;
			overflow: hidden;
			color: #000;
			& thead th {
				background: var(--secondary-th);
				color: #fff;
				font-size: 0.78em;
				text-transform: uppercase;
				letter-spacing: 0.06em;
				padding: 9px 12px;
				text-align: left;
				font-weight: 600;
			}
			& td {
				background: hsl(200, 35%, 20%);
				padding: 9px 12px;
				border-top: 1px solid rgba(255,255,255,0.06);
				font-size: 0.9em;
				color: #ddd;
			}
			& tr:nth-child(odd) td {
				background: hsl(200, 35%, 30%);
			}
			& tbody tr:hover td {
				background: hsl(200, 35%, 10%);
			}
			& button {
				margin: 0;
			}
			& .empty-row td {
				color: #666;
				font-style: italic;
				text-align: center;
				padding: 18px;
			}
		}

		.stat-row {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
			gap: 1rem;
			margin-bottom: 2rem;
		}
		.stat-card {
			position: relative;
			border: 1px solid var(--secondary-60);
			box-shadow: 0 0 3px var(--secondary-60), 0 0 3px var(--secondary-60) inset;
			border-radius: 10px;
			padding: 18px 16px;
			text-align: center;
			& .badge {
				position: absolute;
				background: var(--secondary-20);
				top: 5px;
				right: 5px;
				border: 1px solid;
				box-shadow: 0 0 2px #0c6, 0 0 2px #0c6 inset;
				color: #0c6;
				border-radius: 20px;
				font-size: 0.7em;
				padding: 2px 7px;
				font-weight: bold;
			}
		}	

		.stat-card .val { font-size: 2.2em; font-weight: bold; line-height: 1; margin-bottom: 5px; }
		.stat-card .lbl { font-size: 0.75em; color: #aaa; text-transform: uppercase; letter-spacing: 0.06em; }
		.stat-card.live  .val { color: #0cf; }
		.stat-card.done  .val { color: #aaa; }
		.stat-card.next  .val { color: #fc0; }
		.stat-card.money .val { color: #0f9; }
		.stat-card.warn .val { color: #fc0; }
		.stat-card.draft .val { color: #888; }

		.status-pill {
			display: inline-block;
			padding: 2px 9px;
			border-radius: 20px;
			font-size: 0.76em;
			font-weight: bold;
			text-transform: capitalize;
			margin-left: 10px;
			vertical-align: middle;
		}
		.status-paid    { background: rgba(0,200,100,0.18); color: #0c6; }
		.status-failed  { background: rgba(255,80,80,0.18); color: #f55; }
		.status-default { background: rgba(255,255,255,0.08); color: #888; }
		.status-active   { background: rgba(0,200,100,0.2); color: #0c6; }
		.status-inactive { background: rgba(200,100,0,0.2); color: #f80; }
		.status-created   { background: rgba(255,255,255,0.1); color: #888; }
		.status-scheduled { background: rgba(255,200,0,0.2); color: #fc0; }
		.status-open     { background: rgba(100,200,0,0.2); color: #8d0; }
		.status-closed   { background: rgba(150,150,150,0.2); color: #aaa; }
		.status-pending { background: rgba(255,200,0,0.18); color: #fc0; }
		.status-generated { background: rgba(180,100,255,0.2); color: #b7f; }
		.status-running  { background: rgba(0,180,255,0.2); color: #0cf; }
		.status-finished { background: rgba(150,150,150,0.15); color: #f55; }

		.alt {
			background-color: #fff;
			border: 1px #555 solid;
		}

		.alt:hover {
			background-color: #fff;
		}

		.danger {
			background-color: #ff0000;
			border: 1px #dd0000 solid;
		}

		.danger:hover {
			background-color: #dd0000;
			border: 1px #dd0000 solid;
		}

		.error {
			background-color: red;
			color: #fff;
			padding: 4px;
			border-radius: 6px;
			font-size: 0.9em;
			margin-bottom: 4px;
		}

		@media (max-width: 840px) {
			h1 {
				font-size: 1.6em;
				text-align: center;
			}

			h2 {
				font-size: 1.4em;
				text-align: center;
			}

			h3 {
				font-size: 1.3em;
				text-align: center;
			}

			h4 {
				font-size: 1.2em;
				text-align: center;
			}

			h5 {
				font-size: 1.1em;
				text-align: center;
			}

			p {
				display: block;

				text-align: center;
			}

			.button {
				width: 100%;

			}
		}

		@media (max-width: 550px) {
			.float-right-lg {
				width: 100% !important;
			}
		}

		@media (min-width: 551px) {
			.float-right-lg {
				float: right;
				position: relative;
			}
		}

		@media (max-width: 839px) {

			#top-gutter {
				font-size: 1.4em;
			}

			.fa-home {
				left: -12px;
				position: relative;
			}
		}

		@media (min-width: 840px) {
			.hide-sm {
				display: inline-block;
			}
		}
	</style>
	<body>
	<?php
	if ((segment(2) !== '') && (segment(2) !== 'login') && (segment(2) !== 'submit_login')) { ?>
		<header id="top-gutter">
			<a href="<?= BASE_URL ?>">
				<img src="<?= BASE_URL ?>/images/surfpan-hero-2.svg" height="30px" width="30px" alt="logo surf panel" style="padding: 8px;">
			</a>
			<div>
				<a href="<?= BASE_URL ?>trongate_administrators/dashboard">
					<span class="hide-sm">Admin Dashboard</span>
					<i class="fa fa-tachometer"></i>
				</a>
			</div>
			<nav>
				<a href="<?= BASE_URL ?>trongate_administrators/organizations_list">
					<span class="hide-sm">Organizations</span>
					<i class="fa fa-building"></i>
				</a>
				<a href="<?= BASE_URL ?>trongate_administrators/competitions_overview">
					<span class="hide-sm">Competitions</span>
					<i class="fa fa-trophy"></i>
				</a>
				<a href="<?= BASE_URL ?>trongate_administrators/billing_overview">
					<span class="hide-sm">Billing</span>
					<i class="fa fa-credit-card"></i>
				</a>
				<a href="<?= BASE_URL ?>trongate_administrators/user_search">
					<span class="hide-sm">Users</span>
					<i class="fa fa-search"></i>
				</a>
				<a href="<?= BASE_URL ?>trongate_administrators/manage">
					<span class="hide-sm">Administrators</span>
					<i class="fa fa-gears"></i>
				</a>
				<a href="<?= BASE_URL ?>trongate_administrators/create/<?= $data['my_admin_id'] ?? '' ?>">
					<span class="hide-sm">Your Account</span>
					<i class="fa fa-user"></i>
				</a>
				<a href="<?= BASE_URL ?>trongate_administrators/logout">
					<span class="hide-sm">Logout</span>
					<i class="fa fa-sign-out"></i>
				</a>
			</nav>
		</header>
		<?php
	}
	?>
	<main class="container">
		<?= Template::display($data) ?>
	</main>
</body>

</html>
