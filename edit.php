<?php
require_once 'inc/lib.php';

session_start();
if (empty($_SESSION['user']) || !$user = user_info($_SESSION['user'])) {
	header('Location: .');
	exit('Not Authorized');
}

if (empty($_REQUEST['file'])) {
	header('Location: files.php');
	exit('No file specified');
}

if (strpos($_REQUEST['file'], '..') !== false) {
	exit('Invalid file path.');
}

if (isset($_POST['text']) && !empty($_POST['file'])) {
	$file = $user['home'] . $_POST['file'];
	$text = $_POST['text'];
	if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
		$text = stripslashes($text);
	$saved = file_put_contents($file, $text);
}

$dir = rtrim(dirname($_REQUEST['file']), '/');
$file = $user['home'] . sanitize_path($_REQUEST['file']);
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Obsidian Panel</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/smooth.css" rel="stylesheet" id="smooth-css">
		<link href="css/style.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
		<script src="js/jquery-1.7.2.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script>
			function adjustTextareaHeight() {
				const navHeight = $('#top-nav')?.outerHeight() || 56;
				const titleHeight = $('h3').outerHeight() || 48;
				const buttonRowHeight = $('.d-flex.mt-3').outerHeight() || 48;
				const spacing = 48; 

				const reservedSpace = navHeight + titleHeight + buttonRowHeight + spacing + 200;
				const textareaHeight = $(window).height() - reservedSpace;

				$('textarea').css('height', textareaHeight + 'px');
			}

			$(document).ready(function () {
				adjustTextareaHeight();

				$(window).on('resize', adjustTextareaHeight);

				$('textarea').on('input change', function () {
					window.edited = true;
				});

				$('#cancel, #reload').click(function () {
					if (window.edited)
						return confirm('Are you sure? Unsaved changes will be lost.');
					return true;
				});

				setTimeout(() => $('.alert').fadeOut(), 4000);
			});
		</script>
		<style>
			html, body {
				height: 100%;
				margin: 0;
				padding: 0;
				overflow-y: auto;
			}
			textarea {
				resize: none;
				margin-bottom: 1rem;
			}
		</style>
	</head>
	<body style="margin-top: 0px;padding-top: 20px;">
	<?php require 'inc/top.php'; ?>
	<div class="container-fluid mt-3">
		<form action="edit.php" method="post">
			<div class="row">
				<div class="col-12">
					<h3 class="float-start fw-normal">"<?php echo htmlspecialchars($_REQUEST['file']); ?>"</h3>
					<?php if (isset($_POST['text']) && $saved !== false): ?>
						<div class="alert alert-success float-end">Saved</div>
					<?php elseif (isset($_POST['text'])): ?>
						<div class="alert alert-danger float-end">File couldn't saved!</div>
					<?php elseif (isset($_GET['action']) && $_GET['action'] == 'reload'): ?>
						<div class="alert alert-info float-end">Reloaded</div>
					<?php endif; ?>
					<div class="clearfix"></div>

					<input type="hidden" name="file" value="<?php echo htmlspecialchars($_REQUEST['file']); ?>">

					<textarea name="text" class="form-control mt-3" style="width:100%; box-sizing:border-box; font-family:monospace;"><?php echo htmlspecialchars(file_get_contents($file)); ?></textarea>

					<div class="d-flex justify-content-end mt-3 gap-2">
						<a href="files.php?dir=<?php echo htmlspecialchars(urlencode($dir)); ?>" id="cancel" class="btn btn-secondary">
							<i class="bi bi-x-lg me-1"></i> Close
						</a>
						<a href="edit.php?file=<?php echo htmlspecialchars(urlencode($_REQUEST['file'])); ?>&action=reload" id="reload" class="btn btn-secondary">
							<i class="bi bi-arrow-clockwise me-1"></i> Refresh
						</a>
						<button type="submit" class="btn btn-primary">
							<i class="bi bi-save me-1"></i> Save
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
		<?php require 'inc/footer.php'; ?>
	</body>
</html>