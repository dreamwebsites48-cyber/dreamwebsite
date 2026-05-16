<?php
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($base_url)) {
    $base_url = "http://localhost/dreamwebsitegpt/"; 
}
?>
<!-- Reusable Navbar for All User Pages -->
<nav class="navbar navbar-expand-lg navbar-premium px-4 sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= $base_url ?>index.php">
            <i class="fas fa-globe me-2" style="color: var(--accent-primary);"></i>
            <span class="text-gradient"><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?></span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="mainNavbar">
            <ul class="navbar-nav mb-2 mb-lg-0 gap-2">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="<?= $base_url ?>index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'websites.php' ? 'active' : '' ?>" href="<?= $base_url ?>websites.php">
                        <i class="fas fa-laptop-code me-1"></i> Websites
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'about.php' ? 'active' : '' ?>" href="<?= $base_url ?>about.php">
                        <i class="fas fa-info-circle me-1"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'help.php' ? 'active' : '' ?>" href="<?= $base_url ?>help.php">
                        <i class="fas fa-question-circle me-1"></i> Help / Support
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'contact.php' ? 'active' : '' ?>" href="<?= $base_url ?>contact.php">
                        <i class="fas fa-envelope me-1"></i> Contact
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="d-flex align-items-center">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a class="text-decoration-none dropdown-toggle fw-bold d-flex align-items-center me-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-primary);">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random" alt="Avatar" class="rounded-circle me-2" width="35" height="35">
                        <?= htmlspecialchars($_SESSION['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end glass-panel border-0" style="background: var(--bg-secondary);">
                        <li><a class="dropdown-item text-secondary hover-primary" href="<?= $base_url ?>user/dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                        <li><a class="dropdown-item text-secondary hover-primary" href="<?= $base_url ?>user/profile.php"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                        <li><a class="dropdown-item text-danger" href="<?= $base_url ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= $base_url ?>login.php" class="btn btn-premium-outline rounded-pill px-4 me-2">Login</a>
                <a href="<?= $base_url ?>register.php" class="btn btn-premium rounded-pill px-4 fw-bold">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
