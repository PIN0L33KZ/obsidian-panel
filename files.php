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
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/smooth.css" id="smooth-css">
		<link rel="stylesheet" href="css/style.css">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
		<script src="js/jquery-1.7.2.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<meta name="author" content="PIN0L33KZ contact@pinoleekz.de">
		<style>
			.list-group-item.active span {
				color: white !important;
			}

			.list-group-item span {
				color: #212529 !important;
			}
			
			#filelist li, #dirtree li {
				cursor: pointer;
				user-select: none;
				-webkit-user-select: none;
			}
		</style>
	</head>
	<body style="margin-top: 0px;padding-top: 20px;">
	<?php require 'inc/top.php'; ?>
	<div class="container-fluid mt-3">
		<div class="row">
			<div class="col-md-3">
				<div class="p-3 border rounded bg-light">
					<h5>Directories</h5>
					<ul class="list-group" id="dirtree">
						<li class="list-group-item active" id="home" data-path="/">
							<span class="text-white text-decoration-none"><i class="bi bi-house-door-fill me-2"></i>Home (/)</span>
						</li>
					</ul>
				</div>
			</div>
			<div class="col-md-9" style="margin-bottom: 20px;">
				<div class="p-3 border rounded bg-light">
					<div class="row align-items-center mb-3">
						<div class="col-md-6" id="path" style="display: flex; flex-wrap: wrap; gap: 0.5rem;"></div>
						<div class="col-md-6 text-end">
							<div class="btn-group me-2">
								<button id="btn-delete" type="button" class="btn btn-outline-danger btn-sm ht" title="Delete" disabled><i class="bi bi-trash"></i></button>
								<button id="btn-edit" type="button" class="btn btn-outline-secondary btn-sm ht" title="Edit" disabled><i class="bi bi-pencil-square"></i></button>
								<button id="btn-rename" type="button" class="btn btn-outline-secondary btn-sm ht" title="Rename" disabled><i class="bi bi-input-cursor-text"></i></button>
							</div>
							<div class="btn-group me-2">
								<button id="btn-view" type="button" class="btn btn-outline-secondary btn-sm ht" title="View" disabled><i class="bi bi-eye"></i></button>
								<button id="btn-dl" type="button" class="btn btn-outline-primary btn-sm ht" title="Download" disabled><i class="bi bi-download"></i></button>
							</div>
							<button id="btn-upload" type="button" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i> Upload</button>
						</div>
					</div>
					<ul class="list-group" id="filelist"></ul>
				</div>
			</div>
		</div>
	</div>

	<!-- Upload Modal -->
	<div class="modal fade" id="modal-upload" tabindex="-1" aria-labelledby="lbl-upload" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="lbl-upload">Upload Files</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<iframe src="uploader.php" id="iframe-upload" style="width:100%; height:150px; border:none;" allowtransparency="true"></iframe>
				</div>
			</div>
		</div>
	</div>

	<script>
	$(document).ready(function () {
		$('#dirtree').on('click', 'li', function () {
			$('#dirtree li').removeClass('active');
			$(this).addClass('active');

			const path = $(this).data('path');
			if (path) {
				loaddir(path);
			}
		});

		$('#filelist').on('click', 'li', function (e) {
			if (!e.ctrlKey && !e.shiftKey)
				$('#filelist li').removeClass('active');

			$(this).toggleClass('active');

			let active = $('#filelist li.active').length;
			$('#btn-delete').prop('disabled', active === 0);
			$('#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', active !== 1);
		});

		$('#filelist').on('dblclick', 'li', function () {
			const type = $(this).data('type');
			const path = $(this).data('path');

			if (type === 'dir') {
				loaddir(path);
			} else if (type === 'file') {
				window.location = 'edit.php?file=' + encodeURIComponent(path);
			}
		});

		$(document).on('keyup', function (e) {
			if (e.which == 27) {
				$('#filelist li.active').removeClass('active');
				$('#btn-delete,#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', true);
			}
		});

		$('#btn-delete').click(function () {
			window.selectedfiles = [];
			$('#filelist li.active').each(function () {
				window.selectedfiles.push($(this).data('path'));
			});
			if (confirm('Are you sure you want to delete the selected files?')) {
				$.post('ajax.php', {
					req: 'delete',
					files: window.selectedfiles
				}, function () {
					loaddir(window.lastdir);
				}).fail(function () {
					alert('There was an error deleting your files.');
				});
			}
		});

		$('#btn-edit').click(function () {
			window.location = 'edit.php?file=' + encodeURIComponent($('#filelist li.active').data('path'));
		});

		$('#btn-rename').click(function () {
			const activeItem = $('#filelist li.active');
			const oldPath = activeItem.data('path');
			let newname = prompt('Enter a new name for the file:', basename(oldPath));

			if (newname) {
				$.post('ajax.php', {
					req: 'rename',
					path: oldPath,
					newname: newname
				}, function () {
					loaddir(window.lastdir);
				}).fail(function () {
					alert('There was an error renaming your file.');
				});
			}
		});

		$('#btn-view').click(function () {
			window.open('download.php?dl=0&file=' + encodeURIComponent($('#filelist li.active').data('path')));
		});

		$('#btn-dl').click(function () {
			window.open('download.php?dl=1&file=' + encodeURIComponent($('#filelist li.active').data('path')));
		});

		$('#btn-upload').click(function () {
			$('#modal-upload').modal('show');
		});

		$('button.ht').tooltip();

		loaddir('<?php echo !empty($_GET["dir"]) ? addslashes(htmlspecialchars($_GET["dir"])) : '/'; ?>');
	});

	function loaddir(dir) {
		window.lastdir = dir;
		$('#filelist').empty().addClass('loading');
		$('#btn-delete,#btn-edit,#btn-rename,#btn-dl,#btn-view').prop('disabled', true);
		$('#dirtree li:not(#home)').remove();

		$.post('ajax.php', {
			req: 'dir',
			dir: dir
		}, function (data) {
			const lvl_array = window.lastdir.replace(/\/$/, '').split('/');
			$('#path').empty();
			let lvl_current = '/';

			for (let i = 0; i < lvl_array.length; i++) {
				if (i) {
					lvl_current += lvl_array[i] + '/';
					$('#path').append(`<button type="button" class="btn btn-outline-secondary btn-sm" onclick="loaddir('${lvl_current}')">${lvl_array[i]}</button>`);
				} else {
					$('#path').append(`<button type="button" class="btn btn-outline-secondary btn-sm" onclick="activateHome()"><i class="bi bi-house"></i></button>`);
				}
			}

			let dirtree = '';
			for (let d in data.dirs) {
				let dirName = data.dirs[d];
				dirtree += `<li class="list-group-item" data-type="dir" data-path="${window.lastdir.replace(/\/$/, '')}/${dirName}"><span class="text-muted text-decoration-none"><i class="bi bi-folder me-2"></i>${dirName}</span></li>`;
			}

			let filelist = '';
			for (let f in data.files) {
				let fname = data.files[f];
				let size = size_format(data.sizes[f]);
				filelist += `<li class="list-group-item" data-type="file" data-path="${window.lastdir.replace(/\/$/, '')}/${fname}"><span class="text-muted text-decoration-none"><i class="bi bi-file-earmark me-2"></i>${fname} <span class="float-end">${size}</span></span></li>`;
				}

			$('#dirtree').append(dirtree);
			$('#filelist').removeClass('loading').html(filelist);
			$('#home').toggleClass('active', window.lastdir === '/');
			$('#iframe-upload').attr('src', 'uploader.php?dir=' + encodeURIComponent(window.lastdir));
		}, 'json').fail(function () {
			console.error('Error loading directory "' + window.lastdir + '"');
		});
	}

	function size_format(s) {
		if (s >= 1073741824)
			s = Math.round(s / 1073741824 * 100) / 100 + ' GB';
		else if (s >= 1048576)
			s = Math.round(s / 1048576 * 100) / 100 + ' MB';
		else if (s >= 1024)
			s = Math.round(s / 1024 * 100) / 100 + ' KB';
		else
			s = s + ' bytes';
		return s;
	}

	function basename(path, suffix) {
		let b = path.replace(/^.*[\/\\]/g, '');
		if (typeof(suffix) == 'string' && b.endsWith(suffix))
			b = b.slice(0, -suffix.length);
		return b;
	}
	
	function activateHome() {
		$('#dirtree li').removeClass('active');
		$('#home').addClass('active');
		loaddir('/');
	}
	</script>
		<?php require 'inc/footer.php'; ?>
	</body>
</html>