<?php
require_once 'inc/lib.php';

session_start();
if ((!$user = user_info($_SESSION['user'])) && !$_SESSION['user']) {
	header('Location: .');
	exit('Not Authorized');
} elseif (empty($_SESSION['is_admin']) && $user['role'] != 'admin') {
	header('Location: .');
	exit('Not Authorized');
}

// Switch users
if (isset($_POST['action']) && $_POST['action'] == 'user-switch' && $_POST['user']) {
	$_SESSION['is_admin'] = true;
	$_SESSION['user'] = $_POST['user'];
	header('Location: .');
	exit('Switching Users');
}

//Manage a backup cron job
if (isset($_POST['action']) && $_POST['action'] == 'backup-manage' && $_POST['user']) {
	$action = (isset($_POST['create']) ? "create" : (isset($_POST['delete']) ? "delete" : exit("Action error")));
	if ($action == 'create') {
		server_manage_backup($_POST['user'], $action, intval($_POST["hrFreq"]), intval($_POST["hrDeleteAfter"]));
	} else {
		server_manage_backup($_POST['user'], $action, 1, 0);
	}
}

// Add new user
if (isset($_POST['action']) && $_POST['action'] == 'user-add')
	user_add($_POST['user'], $_POST['pass'], $_POST['role'], $_POST['dir'], $_POST['ram'], $_POST['port']);

// Start a server
if (isset($_POST['action']) && $_POST['action'] == 'server-start') {
	$stu = user_info($_POST['user']);
	if (!server_running($stu['user']))
		server_start($stu['user']);
}

// Kill a server
if (isset($_POST['action']) && $_POST['action'] == 'server-stop')
	if ($_POST['user'] == 'ALL')
		server_kill_all();
	else
		server_kill($_POST['user']);
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
	<script src="js/jquery-1.7.2.min.js"></script>
	<script src="js/bootstrap.bundle.min.js"></script>
	<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
	<script>
		$(document).ready(function () {
			check_cron();
			window.setTimeout(() => $('.alert').fadeOut(), 3000);
			$('#frm-killall').submit(() => confirm('Are you sure you want to KILL EVERY SERVER?\nServers will not save any new data, and all connected players will be disconnected!'));
			$("#backup-user").change(check_cron);

			function check_cron() {
				$.post('ajax.php', {
					req: 'get_cron',
					user: $('#backup-user').val()
				}, function (data) {
					let enabled = !!data.hrFreq;
					$("#backup-create").prop("disabled", enabled);
					$("#backup-delete").prop("disabled", !enabled);
					$("#hrFreq, #hrDeleteAfter").prop("disabled", enabled);
					$("#hrFreq").val(enabled ? data.hrFreq : 1);
					$("#hrDeleteAfter").val(enabled ? data.hrDeleteAfter : 0);
				});
			}
		});
	</script>
</head>
<body style="margin-top: 0px;padding-top: 20px;">
<?php require 'inc/top.php'; ?>
<div class="container-fluid mt-4">
	<?php if (isset($_POST['action']) && $_POST['action'] == 'user-add'): ?>
		<div class="alert alert-success float-end">User added successfully.</div>
	<?php elseif (isset($_POST['action']) && $_POST['action'] == 'server-start'): ?>
		<div class="alert alert-success float-end">Server started.</div>
	<?php elseif (isset($_POST['action']) && $_POST['action'] == 'server-stop'): ?>
		<div class="alert alert-success float-end">Server killed.</div>
	<?php endif; ?>
	<div class="clearfix mb-4"></div>

	<div class="row">
		<div class="col-lg-9">
			<div class="p-3 border rounded bg-light mb-4">
				<h5>Running Servers (User: <?php echo `whoami`; ?>)</h5>
				<pre><?php echo `screen -ls`; ?></pre>
				<div class="row gx-3">
					<div class="col-md-6">
						<form action="admin.php" method="post">
							<input type="hidden" name="action" value="server-start">
							<label class="form-label">Start Server</label>
							<div class="input-group">
								<select name="user" class="form-select">
									<optgroup label="Users">
										<?php foreach (user_list() as $u) if ($u != "empty") echo "<option value=\"$u\">$u</option>"; ?>
									</optgroup>
								</select>
								<button class="btn btn-success" type="submit">
									<i class="bi bi-play-fill me-1"></i> Start
								</button>
							</div>
						</form>
					</div>

					<div class="col-md-6">
						<form action="admin.php" method="post">
							<input type="hidden" name="action" value="server-stop">
							<label class="form-label">Kill Server</label>
							<div class="input-group">
								<select name="user" class="form-select">
									<option value="ALL">All Servers</option>
									<optgroup label="Users">
										<?php foreach (user_list() as $u) if ($u != "empty") echo "<option value=\"$u\">$u</option>"; ?>
									</optgroup>
								</select>
								<button class="btn btn-danger" type="submit">
									<i class="bi bi-x-octagon-fill me-1"></i> Kill
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="p-3 border rounded bg-light mb-4">
				<form action="admin.php" method="post" class="mb-0">
					<input type="hidden" name="action" value="backup-manage">
					<h5>Scheduled Backups</h5>
					<pre><?php echo shell_exec('crontab -l'); ?></pre>

					<div class="mb-3">
						<label for="backup-user" class="form-label">Server</label>
						<select name="user" id="backup-user" class="form-select w-25">
							<?php foreach (user_list() as $u) if ($u != "empty") echo "<option value=\"$u\">$u</option>"; ?>
						</select>
					</div>

					<div class="mb-3">
						<label for="hrFreq" class="form-label">Backup frequency</label>
						<div class="input-group w-25">
							<input type="number" name="hrFreq" id="hrFreq" class="form-control" min="0" step="1" value="1">
							<span class="input-group-text">Hours</span>
						</div>
						<div class="form-text">4 = Every 4 Hours</div>
					</div>

					<div class="mb-3">
						<label for="hrDeleteAfter" class="form-label">Delete backups older than</label>
						<div class="input-group w-25">
							<input type="number" name="hrDeleteAfter" id="hrDeleteAfter" class="form-control" min="0" step="1" value="0">
							<span class="input-group-text">Hours</span>
						</div>
						<div class="form-text">0 = Never delete</div>
					</div>

					<button type="submit" name="create" id="backup-create" class="btn btn-success">
						<i class="bi bi-check-circle-fill me-1"></i> Enable
					</button>
					<button type="submit" name="delete" id="backup-delete" class="btn btn-danger">
						<i class="bi bi-slash-circle-fill me-1"></i> Disable
					</button>
				</form>
			</div>
		</div>

		<div class="col-lg-3">
			<div class="p-3 border rounded bg-light mb-4">
				<form action="admin.php" method="post" class="mb-0">
					<input type="hidden" name="action" value="user-switch">
					<h5>Switch to a User</h5>
					<div class="input-group w-100">
						<select name="user" class="form-select">
							<?php foreach (user_list() as $u) if ($u != "empty") echo "<option value=\"$u\">$u</option>"; ?>
						</select>
						<button type="submit" class="btn btn-primary">
							<i class="bi bi-arrow-left-right me-1"></i> Switch
						</button>
					</div>
				</form>
			</div>

			<div class="p-3 border rounded bg-light mb-4">
				<form action="admin.php" method="post" autocomplete="off" class="mb-0">
					<input type="hidden" name="action" value="user-add">
					<h5>Add New User</h5>

					<div class="mb-3">
						<label for="user" class="form-label">Username</label>
						<input type="text" name="user" id="user" class="form-control w-50">
					</div>

					<div class="mb-3">
						<label for="pass" class="form-label">Password</label>
						<input type="password" name="pass" id="pass" class="form-control w-50">
					</div>

					<div class="mb-3">
						<label for="dir" class="form-label">Home Directory</label>
						<input type="text" name="dir" id="dir" class="form-control w-75" value="<?php echo strtr(dirname(__FILE__), '\\', '/'); ?>">
					</div>

					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="ram" class="form-label">Server Memory (MB)</label>
							<input type="number" name="ram" id="ram" class="form-control" value="512" min="0" step="1">
							<div class="form-text">0 MB = No Server</div>
						</div>

						<div class="col-md-6 mb-3">
							<label for="port" class="form-label">Server Port</label>
							<input type="number" name="port" id="port" class="form-control" value="25565" min="0" step="1">
							<div class="form-text">0 = No Server</div>
						</div>
					</div>

					<div class="mb-3">
						<label for="role" class="form-label">User Role</label>
						<select name="role" id="role" class="form-select w-50">
							<option value="user" selected>User</option>
							<option value="admin">Administrator</option>
						</select>
					</div>

					<button type="submit" class="btn btn-primary">
						<i class="bi bi-person-plus-fill me-1"></i> Add
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php require 'inc/footer.php'; ?>
</body>
</html>