<?php
require_once 'inc/lib.php';

session_start();
if (!$user = user_info($_SESSION['user'])) {
	exit();
}

switch ($_POST['req']) {

	case 'dir':
		$dirs = [];
		$files = [];
		$sizes = [];

		// Sicheren Pfad berechnen
		$dir = sanitize_path($_POST['dir'] ?? '/');
		$path = rtrim($user['home'], '/') . '/' . ltrim($dir, '/');

		if (!is_dir($path)) {
			echo json_encode([
				'dirs' => [],
				'files' => [],
				'sizes' => [],
				'error' => 'Directory does not exist: ' . $path
			]);
			break;
		}

		if ($handle = opendir($path)) {
			while (false !== ($f = readdir($handle))) {
				if ($f == '.' || $f == '..') continue;

				$full = $path . '/' . $f;

				if (is_dir($full)) {
					$dirs[] = $f;
				} elseif (is_file($full)) {
					$files[] = $f;
					$sizes[] = filesize($full);
				}
			}
			closedir($handle);
		} else {
			echo json_encode([
				'dirs' => [],
				'files' => [],
				'sizes' => [],
				'error' => 'Unable to open directory: ' . $path
			]);
			break;
		}

		sort($dirs);
		sort($files);

		echo json_encode([
			'dirs' => $dirs,
			'files' => $files,
			'sizes' => $sizes
		]);
		break;

	case 'file_get':
		if (is_file($user['home'] . sanitize_path($_POST['file']))) {
			echo file_get_contents($user['home'] . sanitize_path($_POST['file']));
		}
		break;

	case 'file_put':
		if (is_file($user['home'] . sanitize_path($_POST['file']))) {
			file_put_contents($user['home'] . sanitize_path($_POST['file']), $_POST['data']);
		}
		break;

	case 'delete':
		foreach ($_POST['files'] as $f) {
			$target = $user['home'] . sanitize_path($f);
			if (is_file($target)) {
				unlink($target);
			}
		}
		break;

	case 'rename':
		file_rename($_POST['path'], $_POST['newname'], $user['home']);
		break;

	case 'cron_exists':
		header('Content-type: application/json');
		echo json_encode(check_cron_exists($_POST['user']));
		break;

	case 'get_cron':
		header('Content-type: application/json');
		echo json_encode(get_cron($_POST['user']));
		break;

	case 'server_start':
		echo server_start($user['user']);
		break;

	case 'server_cmd':
		server_cmd($user['user'], $_POST['cmd']);
		break;

	case 'server_stop':
		server_stop($user['user']);
		break;

	case 'server_kill':
		server_kill($user['user']);
		break;

	case 'server_running':
		echo json_encode(server_running($user['user']));
		break;

	case 'server_log':
		if (is_file($user['home'] . "/logs/latest.log")) {
			echo mclogparse2(file_backread($user['home'] . '/logs/latest.log', 64));
		} elseif (is_file($user['home'] . "/server.log")) {
			echo mclogparse2(file_backread($user['home'] . '/server.log', 64));
		} elseif (is_file($user['home'] . "/proxy.log.0")) {
			echo mclogparse2(file_backread($user['home'] . '/proxy.log.0', 64));
		} else {
			echo "No log file found.";
		}
		break;

	case 'server_log_bytes':
		header('Content-type: application/json');

		if (is_file($user['home'] . '/logs/latest.log')) {
			$file = $user['home'] . '/logs/latest.log';
		} elseif (is_file($user['home'] . '/server.log')) {
			$file = $user['home'] . '/server.log';
		} elseif (is_file($user['home'] . '/proxy.log.0')) {
			$file = $user['home'] . '/proxy.log.0';
		} else {
			exit(json_encode(['error' => 1, 'msg' => 'No log file found.']));
		}

		$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
		$end = isset($_REQUEST['end']) ? intval($_REQUEST['end']) : null;

		$data = @file_get_contents($file, false, null, $start, $end);

		if ($data === false) {
			$data = file_get_contents($file, false, null, 0, 30 * 1024);
			echo json_encode([
				'error' => 2,
				'msg' => 'Failed to read requested bytes from log file. Returned first 30 KB.',
				'start' => 0,
				'end' => strlen($data),
				'data' => $data,
			]);
		} else {
			echo json_encode([
				'start' => $start,
				'end' => $start + strlen($data),
				'data' => $data,
			]);
		}
		break;

	case 'players':
		require_once 'inc/MinecraftQuery.class.php';
		$mq = new MinecraftQuery();
		try {
			$mq->Connect(KT_LOCAL_IP, $user['port'], 2); // 2-second timeout
		} catch (MinecraftQueryException $ex) {
			echo json_encode(['error' => 1, 'msg' => $ex->getMessage()]);
			die();
		}

		echo json_encode([
			'info' => $mq->GetInfo(),
			'players' => $mq->GetPlayers()
		]);
		break;

	case 'set_jar':
		$result = user_modify($user['user'], $user['pass'], $user['role'], $user['home'], $user['ram'], $user['port'], $_POST['jar']);
		echo json_encode($result);
		break;
}