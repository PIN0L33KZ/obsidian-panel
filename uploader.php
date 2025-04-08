<?php
require_once 'inc/lib.php';

session_start();
if (empty($_SESSION['user']) || !$user = user_info($_SESSION['user'])) {
	header('Location: .');
	exit('Not Authorized');
}

// Upload files
if (isset($_FILES['files']) && isset($_POST['dir'])) {
	$uploads = $_FILES['files'];
	$numfiles = count($uploads['name']);
	for ($i = 0; $i < $numfiles; $i++) {
		move_uploaded_file($uploads['tmp_name'][$i], $user['home'] . $_POST['dir'] . '/' . $uploads['name'][$i]);
	}
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Obsidian Panel</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/smooth.css" id="smooth-ui">
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<script src="js/jquery-1.7.2.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<style>
			body {
				background: none;
				background-color: transparent;
				overflow: hidden;
			}
		</style>
	</head>
	<body class="p-3">
	<?php if (!empty($numfiles)) { ?>
		<div class="alert alert-success" role="alert">
			<?php echo $numfiles; ?> file<?php echo $numfiles > 1 ? 's were' : ' was'; ?> uploaded successfully.
		</div>
		<div class="d-flex justify-content-end mt-3">
			<button type="button" class="btn btn-secondary" onclick="top.$('#modal-upload').modal('hide');top.loaddir('<?php echo $_POST['dir']; ?>')">Close</button>
		</div>
	<?php } else { ?>
		<form action="uploader.php" method="post" enctype="multipart/form-data">
			<?php $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : ''; ?>
			<input type="hidden" name="dir" value="<?php echo htmlspecialchars($dir); ?>">


			<div class="mb-3">
				<label for="files" class="form-label">Select files to upload</label>
				<input class="form-control" type="file" name="files[]" id="files" multiple>
			</div>

			<div class="d-flex justify-content-end gap-2">
				<input type="reset" class="btn btn-secondary" value="Cancel" onclick="top.$('#modal-upload').modal('hide');">
				<button type="submit" class="btn btn-primary">Upload</button>
			</div>
		</form>
	<?php } ?>
</body>
</html>