<style>
	.nav-tabs .nav-link {
		color: #000 !important;
	}

	.nav-tabs .nav-link.active {
		color: #000 !important;
		background-color: transparent !important;
		border-color: #dee2e6 #dee2e6 #fff !important;
	}
    * {
        user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }

    textarea,
    input,
    pre {
        user-select: text !important;
    }
</style>
<div id="top-nav" class="navbar navbar-expand-lg navbar-light bg-light fixed-top border-bottom">
    <div class="container-fluid">
        <a draggable="false" class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="/img/logo.png" alt="Logo" height="32" class="me-2" draggable="false">
            <strong>Obsidian</strong> Panel
        </a>

        <div class="d-flex align-items-center ms-auto">
			<ul class="navbar-nav align-items-center">
				<!-- User Dropdown -->
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="bi bi-person-circle me-1"></i>
						Signed in (<strong><?php echo htmlspecialchars($user['user']); ?></strong>)
					</a>
					<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
						<li><a class="dropdown-item" href="userProfile.php"><i class="bi bi-gear me-1"></i> Profile</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item text-danger" href="./?logout"><i class="bi bi-power me-1"></i> Sign Out</a></li>
					</ul>
				</li>
			</ul>
		</div>
    </div>
</div>

<ul class="nav nav-tabs mt-5 pt-3 d-flex justify-content-between" id="myTab" style="border-bottom: 1px solid #dee2e6;">
    <div class="d-flex">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER["SCRIPT_NAME"]) === "dashboard.php" ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER["SCRIPT_NAME"]) === "files.php" ? 'active' : ''; ?>" href="files.php">
                <i class="bi bi-folder2-open me-1"></i> Files
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER["SCRIPT_NAME"]) === "console.php" ? 'active' : ''; ?>" href="console.php">
                <i class="bi bi-terminal me-1"></i> Console
            </a>
        </li>
    </div>

    <?php if (!empty($_SESSION['is_admin']) || $user['role'] === 'admin'): ?>
        <div class="ms-auto d-flex">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER["SCRIPT_NAME"]) === "admin.php" ? 'active' : ''; ?>" href="admin.php">
                    <i class="bi bi-shield-lock me-1"></i> Admincenter
                </a>
            </li>
        </div>
    <?php endif; ?>
</ul>