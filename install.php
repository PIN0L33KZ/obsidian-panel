<?php
require_once 'inc/lib.php';

$installed = is_file(__DIR__ . '/.installed');

if (!$installed && !empty($_POST['user'])) {
	session_start();
	user_add($_POST['user'], $_POST['pass'], 'admin', $_POST['dir'], $_POST['ram'], $_POST['port']);
	file_put_contents(".installed", "");
	$_SESSION['user'] = clean_alphanum($_POST['user']);
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Obsidian Panel</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/smooth.css" id="smooth-css">
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<style>
			body {
				background-color: #f8f9fa;
			}
			.modal-content {
				margin-top: 10vh;
			}
		</style>
	</head>
	<body>
	<div class="container">
		<div class="modal-dialog">
			<div class="modal-content">
				<?php if ($installed): ?>
					<div class="modal-header">
						<h5 class="modal-title">Install Obsidian Panel</h5>
					</div>
					<div class="modal-body">
						<p>Obsidian Panel has already been installed.</p>
						<div class="alert alert-info">
							If you are sure it is not installed, delete the <code>.installed</code> file and refresh this page.
						</div>
					</div>
					<div class="modal-footer">
						<a class="btn btn-primary" href="dashboard.php">Continue to Panel</a>
					</div>
				<?php elseif (!empty($_POST['user'])): ?>
					<div class="modal-header">
						<h5 class="modal-title">Install Obsidian Panel</h5>
					</div>
					<div class="modal-body">
						<p>Obsidian Panel has been installed, and you are now logged in.</p>
					</div>
					<div class="modal-footer">
						<a class="btn btn-primary" href="dashboard.php">Continue to Panel</a>
					</div>
				<?php else: ?>
					<form action="install.php" method="post">
						<div class="modal-header">
							<h5 class="modal-title">Install Obsidian Panel</h5>
						</div>
						<div class="modal-body">
							<h6 class="mb-3">Administrator User</h6>

							<div class="mb-3">
								<label for="user" class="form-label">Username</label>
								<div class="input-group">
									<span class="input-group-text"><i class="bi bi-person"></i></span>
									<input type="text" name="user" id="user" class="form-control">
								</div>
							</div>

							<div class="mb-3">
								<label for="pass" class="form-label">Password</label>
								<div class="input-group">
									<span class="input-group-text"><i class="bi bi-lock"></i></span>
									<input type="password" name="pass" id="pass" class="form-control">
								</div>
							</div>

							<div class="mb-3">
								<label for="dir" class="form-label">Home Directory</label>
								<div class="input-group">
									<span class="input-group-text"><i class="bi bi-folder"></i></span>
									<input type="text" name="dir" id="dir" class="form-control" value="<?php echo strtr(dirname(__FILE__), '\\', '/'); ?>">
								</div>
							</div>

							<div class="mb-3">
								<label for="ram" class="form-label">Server Memory (MB)</label>
								<div class="input-group">
									<input type="number" name="ram" id="ram" class="form-control" min="0" step="1" value="512">
									<span class="input-group-text">MB</span>
								</div>
								<div class="form-text">0 MB = No Server</div>
							</div>

							<div class="mb-3">
								<label for="port" class="form-label">Server Port</label>
								<input type="number" name="port" id="port" class="form-control" min="0" step="1" value="25565">
								<div class="form-text">0 = No Server</div>
							</div>
						</div>
						<div class="modal-footer">
							<button class="btn btn-primary" type="submit">Install and Sign in</button>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Optional: Bootstrap Icons -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
		<?php require 'inc/footer.php'; ?>
	</body>
</html>