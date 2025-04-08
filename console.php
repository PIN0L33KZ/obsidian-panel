<?php
require_once 'inc/lib.php';

session_start();
if (empty($_SESSION['user']) || !$user = user_info($_SESSION['user'])) {
	header('Location: .');
	exit('Not Authorized');
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Obsidian Panel</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/smooth.css" rel="stylesheet" id="smooth-css">
		<link href="css/style.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<style>
			form {
				margin: 0;
			}
			#cmd {
				width: 100%;
			}
			body {
				margin: 0;
				padding: 0;
				min-height: 100vh;
				display: flex;
				flex-direction: column;
			}
			main {
				flex: 1;
			}
			footer {
				margin-top: auto;
			}
			#log {
				min-height: 150px;
				max-height: 70vh;
				overflow-y: auto;
			}
		</style>
		<script src="js/jquery-1.7.2.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script>
			function refreshLog() {
				updateStatus();
				$.post('ajax.php', {
					req: 'server_log'
				}, function (data) {
					if ($('#log').scrollTop() == $('#log')[0].scrollHeight) {
						$('#log').html(data).scrollTop($('#log')[0].scrollHeight);
					} else {
						$('#log').html(data);
					}
					window.setTimeout(refreshLog, 1000);
				});
			}

			function refreshLogOnce() {
				$.post('ajax.php', {
					req: 'server_log'
				}, function (data) {
					$('#log').html(data).scrollTop($('#log')[0].scrollHeight);
				});
			}

			function updateStatus() {
				$.post('ajax.php', {
					req: 'server_running'
				}, function (data) {
					if (data) {
						$('#cmd').prop('disabled', false);
					} else {
						$('#cmd').prop('disabled', true);
					}
				}, 'json');
			}

			$(document).ready(function () {
				$('#frm-cmd').submit(function () {
					$.post('ajax.php', {
						req: 'server_cmd',
						cmd: $('#cmd').val()
					}, function () {
						$('#cmd').val('').prop('disabled', false).focus();
						refreshLogOnce();
					});
					$('#cmd').prop('disabled', true);
					return false;
				});

			function adjustLogHeight() {
					const footerHeight = $('footer').outerHeight(true) || 0;
					const cmdHeight = $('#frm-cmd').outerHeight(true) || 0;
					const topOffset = $('#log').offset().top || 0;
					const windowHeight = $(window).height();
					const newHeight = windowHeight - topOffset - cmdHeight - footerHeight - 30;
					$('#log').css('height', newHeight + 'px');
				}

				// Direkt nach dem Laden anpassen
				adjustLogHeight();

				// Auch bei Fenstergröße-Änderung
				$(window).on('resize', adjustLogHeight);

				// Starte Log-Updates
				$.post('ajax.php', {
					req: 'server_log'
				}, function (data) {
					$('#log').html(data).scrollTop($('#log')[0].scrollHeight);
					window.setTimeout(refreshLog, 1000);
				});
			});
		</script>
	</head>
<body style="margin-top: 0px;padding-top: 20px;">
	<?php require 'inc/top.php'; ?>
	<main class="container-fluid mt-3" style="padding-bottom: 20px;">
		<div class="tab-content">
			<div class="tab-pane active show">
				<?php if (!empty($user['ram'])): ?>
					<pre id="log" class="p-3 bg-light border rounded" style="overflow-y: auto;"></pre>
					<form id="frm-cmd" class="mt-2 mb-4">
						<input type="text" id="cmd" name="cmd" maxlength="250" class="form-control" placeholder="Enter a command, send with enter." autofocus>
					</form>
				<?php else: ?>
					<div class="alert alert-danger">You don't have permissions to own a server.</div>
				<?php endif; ?>
			</div>
		</div>
	</main>
	<?php require 'inc/footer.php'; ?>
</body>
</html>