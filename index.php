<?php
require_once 'inc/lib.php';

session_start();

if (isset($_GET['logout'])) {
	$_SESSION = array();
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	session_destroy();
}

if (!empty($_SESSION['user']) && $user = user_info($_SESSION['user'])) {
	header('Location: dashboard.php');
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Obsidian Panel</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/smooth.css" rel="stylesheet" id="smooth-css">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<style>
			body {
					margin: 0;
					min-height: 100vh;
					background: linear-gradient(-45deg, #1e1e2f, #2d2d3d, #3c3c4f, #1e1e2f);
					background-size: 400% 400%;
					animation: gradientBG 15s ease infinite;
					display: flex;
					align-items: center;
					justify-content: center;
				}

				@keyframes gradientBG {
					0% { background-position: 0% 50%; }
					50% { background-position: 100% 50%; }
					100% { background-position: 0% 50%; }
				}

				/* Glassmorphism-Card */
				.card {
					background: rgba(255, 255, 255, 0.05);
					backdrop-filter: blur(12px);
					border: 1px solid rgba(255, 255, 255, 0.1);
					border-radius: 1rem;
					color: white;
					box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
				}

				.input-group-text, .form-control, .form-label {
					background-color: rgba(255, 255, 255, 0.1);
					color: white;
					border: none;
				}

				.form-control::placeholder {
					color: #ccc;
				}

				.card-header h3 {
					color: #fff;
				}

				.alert {
					background-color: rgba(255, 0, 0, 0.2);
					color: white;
					border: 1px solid rgba(255, 0, 0, 0.4);
				}
				
				.form-label {
					background-color: transparent !important;
					color: white !important;
				}
				
				.btn-glass {
					background-color: rgba(255, 255, 255, 0.1);
					border: 1px solid rgba(255, 255, 255, 0.2);
					color: white;
					backdrop-filter: blur(10px);
					-webkit-backdrop-filter: blur(10px);
					box-shadow: 0 4px 20px rgba(0,0,0,0.3);
					transition: all 0.3s ease;
				}

				.btn-glass:hover {
					background-color: rgba(255, 255, 255, 0.2);
					color: #fff;
					border-color: rgba(255, 255, 255, 0.4);
				}
				
				.btn-glass, .form-control {
					border-radius: 0.75rem;
				}
				
				footer.text-end.text-muted {
					color: #ffffff !important;
				}
		</style>
	</head>
	<body>
	<noscript>
		<div class="alert alert-warning text-center m-3">
			<strong>Enable Javascript:</strong> Javascript is required to use the Obsidian Panel.
		</div>
	</noscript>

	<div class="container d-flex justify-content-center align-items-center vh-100">
		<div class="card shadow-lg" style="min-width: 350px;">
			<form action="dashboard.php" method="post">
				<div class="card-header text-center">
					<div class="d-flex justify-content-center align-items-center">
						<img src="/img/logo.png" alt="Logo" height="40" class="me-2">
						<h3 class="mb-0"><strong>Obsidian Panel</strong></h3>
					</div>
				</div>
				<div class="card-body">
					<?php if (!empty($_GET['error']) && $_GET['error'] == 'badlogin'): ?>
						<div class="alert alert-danger">Invalid login details.</div>
					<?php endif; ?>

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
				</div>
				<div class="card-footer text-end">
					<button class="btn btn-glass w-100" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i> Sign in</button>
				</div>
			</form>
		</div>
	</div>
		<?php require 'inc/footer.php'; ?>
	</body>
</html>