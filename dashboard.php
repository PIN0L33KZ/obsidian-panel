<?php
require_once 'inc/lib.php';

session_start();

if (!empty($_SESSION['user'])) {
	if (!$user = user_info($_SESSION['user'])) {
		header('Location: .');
		exit('Not Authorized');
	}
} elseif (!empty($_POST['user']) && !empty($_POST['pass'])) {
	$user = user_info($_POST['user']);
	$_SESSION['is_admin'] = $user['role'] == 'admin';

	if (!$user || !bcrypt_verify($_POST['pass'], $user['pass'])) {
		header('Location: ./?error=badlogin');
		exit('Not Authorized');
	}

	$_SESSION['user'] = $user['user'];
} else {
	header('Location: .');
	exit('Not Authorized');
}
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
	<head>
		<meta charset="utf-8">
		<title>Obsidian Panel</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/smooth.css" rel="stylesheet" id="smooth-css">
		<link href="css/style.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<style>
			#cmd {
				height: 30px;
			}
		</style>
		<script src="js/jquery-1.7.2.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
	</head>
	<body style="margin-top: 0px;padding-top: 20px;">
	<?php require 'inc/top.php'; ?>
	<div class="container-fluid mt-3" style="padding-bottom: 20px;">
		<?php if (!empty($user['ram'])): ?>
		<div class="row">
			<div class="col-lg-5 mb-3">
				<div class="p-3 border rounded bg-light mb-4">
					<h5>Server Controls</h5>
					<div class="btn-toolbar mb-3">
						<div class="btn-group me-2">
							<button class="btn btn-success btn-lg ht" id="btn-srv-start" title="Start" disabled><i class="bi bi-play-fill"></i></button>
							<button class="btn btn-danger btn-lg ht" id="btn-srv-stop" title="Stop" disabled><i class="bi bi-stop-fill"></i></button>
						</div>
						<div class="btn-group">
							<button class="btn btn-warning btn-lg ht" id="btn-srv-restart" title="Restart" disabled><i class="bi bi-arrow-repeat"></i></button>
						</div>
					</div>

					<label for="server-jar" class="form-label">Server JAR</label>
					<select id="server-jar" class="form-select">
						<?php
						$jars = scandir($user['home']);
						foreach($jars as $file) {
							if (str_ends_with($file, '.jar')) {
								$selected = ((!empty($user['jar']) && $user['jar'] == $file) || (empty($user['jar']) && $file == 'craftbukkit.jar')) ? 'selected' : '';
								echo "<option value=\"$file\" $selected>$file</option>";
							}
						}
						?>
					</select>
				</div>

				<div class="p-3 border rounded bg-light">
					<h5>Server Information</h5>
					<p>
						<strong>Status:</strong>
						<i id="status-icon" class="bi bi-question-circle text-secondary me-1"></i><span id="lbl-status" class="badge bg-secondary">Checking…</span><br>
						<strong>IP:</strong> <?php echo KT_LOCAL_IP . ':' . $user['port']; ?><br>
						<strong>RAM:</strong> <?php echo $user['ram'] . 'MB'; ?><br>
						<strong>Players:</strong> <span id="lbl-players">Checking…</span>
					</p>
					<div class="player-list"></div>
				</div>
			</div>

			<div class="col-lg-7">
				<pre id="log" class="p-3 border rounded bg-light" style="height: 400px; overflow-y: auto;"></pre>
				<form id="frm-cmd" class="mt-2">
					<input type="text" id="cmd" name="cmd" maxlength="250" placeholder="Enter a command, send with enter." class="form-control">
				</form>
			</div>
		</div>
		<?php else: ?>
			<div class="alert alert-danger">You don't have permissions to own a server.</div>
		<?php endif; ?>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			function updateStatus(once = false) {
				fetch('ajax.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'req=server_running'
				})
				.then(res => res.json())
				.then(data => {
					const lbl = document.getElementById('lbl-status');
					const icon = document.getElementById('status-icon');
					const startBtn = document.getElementById('btn-srv-start');
					const stopBtn = document.getElementById('btn-srv-stop');
					const restartBtn = document.getElementById('btn-srv-restart');
					const cmdInput = document.getElementById('cmd');

					if (data) {
						lbl.innerText = 'Running';
						lbl.className = 'badge bg-success';
						icon.className = 'bi bi-play-fill text-success me-1';
						startBtn.disabled = true;
						stopBtn.disabled = false;
						restartBtn.disabled = false;
						cmdInput.disabled = false;
					} else {
						lbl.innerText = 'Stopped';
						lbl.className = 'badge bg-danger';
						icon.className = 'bi bi-stop-fill text-danger me-1';
						startBtn.disabled = false;
						stopBtn.disabled = true;
						restartBtn.disabled = true;
						cmdInput.disabled = true;
					}
					if (!once) setTimeout(updateStatus, 5000);
				})
				.catch(() => {
					const lbl = document.getElementById('lbl-status');
					const icon = document.getElementById('status-icon');
					lbl.innerText = 'Unknown';
					lbl.className = 'badge bg-secondary';
					icon.className = 'bi bi-question-circle text-secondary me-1';
				});

			}

			function updatePlayers() {
				fetch('ajax.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'req=players'
				})
				.then(res => res.json())
				.then(data => {
					const label = document.getElementById('lbl-players');
					const list = document.querySelector('.player-list');
					list.innerHTML = '';
					if (data.error) {
						label.textContent = 'Unknown';
					} else {
						const players = data.players || [];
						label.textContent = `${players.length}/${data.info.MaxPlayers}`;
						if (players.length > 0) {
							const title = document.createElement('strong');
							title.textContent = 'Player List:';
							list.appendChild(title);
							list.appendChild(document.createElement('br'));
							players.forEach(name => {
								const img = document.createElement('img');
								img.src = `inc/getFace.php?username=${name}&size=24`;
								img.style.marginRight = '8px';
								img.alt = name;
								list.appendChild(img);
								list.append(name);
								list.appendChild(document.createElement('br'));
							});
						}
					}
				}).catch(() => {
					document.getElementById('lbl-players').textContent = 'Error';
				});
			}

			function refreshLog() {
				updateStatus();
				fetch('ajax.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'req=server_log'
				})
				.then(res => res.text())
				.then(log => {
					const logArea = document.getElementById('log');
					const isAtBottom = logArea.scrollTop + logArea.clientHeight >= logArea.scrollHeight - 10;
					logArea.innerHTML = log;
					if (isAtBottom) {
						logArea.scrollTop = logArea.scrollHeight;
					}
					setTimeout(refreshLog, 3000);
				});
			}

			function sendCommand(cmd) {
				fetch('ajax.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'req=server_cmd&cmd=' + encodeURIComponent(cmd)
				}).then(() => refreshLog());
			}

			document.getElementById('frm-cmd').addEventListener('submit', function (e) {
				e.preventDefault();
				const cmdInput = document.getElementById('cmd');
				if (cmdInput.value.trim()) {
					sendCommand(cmdInput.value);
					cmdInput.value = '';
				}
			});

			document.getElementById('btn-srv-start').addEventListener('click', function () {
				this.disabled = true;
				fetch('ajax.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'req=server_start'
				}).then(() => updateStatus(true));
			});

			document.getElementById('btn-srv-stop').addEventListener('click', function () {
				this.disabled = true;
				fetch('ajax.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'req=server_stop'
				}).then(() => updateStatus(true));
			});

			document.getElementById('btn-srv-restart').addEventListener('click', function () {
				document.getElementById('btn-srv-stop').click();
				setTimeout(() => {
					document.getElementById('btn-srv-start').click();
				}, 2000);
			});

			document.getElementById('server-jar').addEventListener('change', function () {
				const jar = this.value;
				fetch('ajax.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'req=set_jar&jar=' + encodeURIComponent(jar)
				});
			});

			// Initialisierung
			updateStatus();
			updatePlayers();
			refreshLog();
		});
	</script>
		<?php require 'inc/footer.php'; ?>
	</body>
</html>