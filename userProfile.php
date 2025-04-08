<?php
require_once 'inc/lib.php';
session_start();
if (empty($_SESSION['user']) || !$user = user_info($_SESSION['user'])) {
	header('Location: dashboard.php');
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
		<link rel="stylesheet" href="css/style.css">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<script src="js/jquery-1.7.2.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
	</head>
	<body style="margin-top: 0px;padding-top: 20px;">
		<?php require 'inc/top.php'; ?>

		<div class="container mt-5">
			<h2 class="mb-4">User Profile</h2>
			<form action="userProfile.php" method="post">
				<input type="hidden" name="action" value="user-update">

				<div class="mb-3">
					<label class="form-label">Username:</label>
					<input type="hidden" name="user" value="<?php echo htmlspecialchars($user['user']); ?>">
					<p class="form-control-plaintext fw-semibold"><?php echo htmlspecialchars($user['user']); ?></p>
				</div>

				<div class="mb-3">
					<label class="form-label">Role:</label>
					<input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
					<p class="form-control-plaintext fw-semibold"><?php echo htmlspecialchars($user['role']); ?></p>
				</div>

				<div class="mb-3">
					<label class="form-label">Home Directory:</label>
					<input type="hidden" name="dir" id="dir" value="<?php echo htmlspecialchars($user['home']); ?>">
					<p class="form-control-plaintext fw-semibold"><?php echo htmlspecialchars($user['home']); ?></p>
				</div>

				<div class="mb-3">
					<label class="form-label">RAM Allocated:</label>
					<input type="hidden" name="ram" id="ram" value="<?php echo htmlspecialchars($user['ram']); ?>">
					<p class="form-control-plaintext fw-semibold"><?php echo htmlspecialchars($user['ram']); ?> MB</p>
				</div>

				<div class="mb-3">
					<label class="form-label">Port:</label>
					<input type="hidden" name="port" id="port" value="<?php echo htmlspecialchars($user['port']); ?>">
					<p class="form-control-plaintext fw-semibold"><?php echo htmlspecialchars($user['port']); ?></p>
				</div>
			</form>
		</div>
		<?php require 'inc/footer.php'; ?>
	</body>
</html>