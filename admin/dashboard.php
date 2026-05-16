<?php
require_once __DIR__ . '/../config.php';

// 🔒 ADMIN SECURITY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 📊 FETCH DATA
$users           = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc();
$developers      = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='developer'")->fetch_assoc();
$pending_dev     = $conn->query("SELECT COUNT(*) as total FROM users WHERE developer_status='pending'")->fetch_assoc();
$uploads         = $conn->query("SELECT COUNT(*) as total FROM offers")->fetch_assoc();
$approved        = $conn->query("SELECT COUNT(*) as total FROM offers WHERE status='approved'")->fetch_assoc();
$rejected        = $conn->query("SELECT COUNT(*) as total FROM offers WHERE status='rejected'")->fetch_assoc();
$pending_uploads = $conn->query("SELECT COUNT(*) as total FROM offers WHERE status='pending'")->fetch_assoc();
$offers          = $conn->query("SELECT COUNT(*) as total FROM offers")->fetch_assoc();

include "../includes/header.php";
?>

<!-- Mobile Sidebar Toggle -->
<button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Admin Sidebar & Main Content Layout -->
<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar-premium" id="adminSidebar">
        <div class="text-center mb-4 pt-2">
            <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <i class="fas fa-shield-alt fa-2x text-white"></i>
            </div>
            <h5 class="fw-bold m-0">Admin Panel</h5>
            <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill mt-1">
                <?= htmlspecialchars($_SESSION['name']) ?>
            </span>
        </div>

        <div class="d-flex flex-column gap-1">
            <a href="dashboard.php" class="sidebar-link active"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">User Management</div>
            <a href="manage_users.php" class="sidebar-link"><i class="fas fa-users"></i> All Users</a>
            <a href="manage_developers.php" class="sidebar-link"><i class="fas fa-code"></i> Developers</a>
            <a href="developer_requests.php" class="sidebar-link d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-clock"></i> Dev Requests</span>
                <?php if ($pending_dev['total'] > 0): ?>
                    <span class="badge bg-warning text-dark rounded-pill"><?= $pending_dev['total'] ?></span>
                <?php endif; ?>
            </a>
            <a href="password_requests.php" class="sidebar-link"><i class="fas fa-key"></i> Password Requests</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">Content</div>
            <a href="manage_websites.php" class="sidebar-link"><i class="fas fa-globe"></i> Websites</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">System</div>
            <a href="../index.php" class="sidebar-link"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="../logout.php" class="sidebar-link" style="color:var(--accent-danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up flex-wrap gap-3">
            <div>
                <h2 class="display-6 fw-bold m-0">Dashboard <span class="text-gradient">Overview</span></h2>
                <p class="text-secondary mt-1 mb-0">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?> 👑</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="d-flex align-items-center gap-2 glass-panel px-3 py-2 rounded-pill">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=6366f1&color=fff" class="rounded-circle" width="30" height="30" alt="Avatar">
                    <span class="fw-bold d-none d-sm-inline"><?= htmlspecialchars($_SESSION['name']) ?></span>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>showToast("<?= addslashes($_SESSION['msg']) ?>","success"));</script>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <!-- User Statistics -->
        <h6 class="fw-bold mb-3 animate-fade-up"><i class="fas fa-users text-primary me-2"></i>User Statistics</h6>
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-primary border-opacity-25">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Total Users</div>
                        <h2 class="m-0 fw-bold text-primary"><?= $users['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(59,130,246,.1);color:var(--accent-primary);">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-info border-opacity-25">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Developers</div>
                        <h2 class="m-0 fw-bold text-info"><?= $developers['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(6,182,212,.1);color:#06b6d4;">
                        <i class="fas fa-code"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-warning border-opacity-25">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Dev Requests</div>
                        <h2 class="m-0 fw-bold text-warning"><?= $pending_dev['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(245,158,11,.1);color:var(--accent-warning);">
                        <i class="fas fa-user-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-success border-opacity-25">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Total Websites</div>
                        <h2 class="m-0 fw-bold text-success"><?= $offers['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(16,185,129,.1);color:var(--accent-tertiary);">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Statistics -->
        <h6 class="fw-bold mb-3 animate-fade-up"><i class="fas fa-globe text-secondary me-2"></i>Content Statistics</h6>
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Total Uploads</div>
                        <h2 class="m-0 fw-bold"><?= $uploads['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(255,255,255,.05);color:var(--text-primary);">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-success border-opacity-25">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Approved</div>
                        <h2 class="m-0 fw-bold text-success"><?= $approved['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(16,185,129,.1);color:var(--accent-tertiary);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-danger border-opacity-25">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Rejected</div>
                        <h2 class="m-0 fw-bold text-danger"><?= $rejected['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(239,68,68,.1);color:var(--accent-danger);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-warning border-opacity-25">
                    <div>
                        <div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Pending</div>
                        <h2 class="m-0 fw-bold text-warning"><?= $pending_uploads['total'] ?></h2>
                    </div>
                    <div class="stat-card-icon" style="background:rgba(245,158,11,.1);color:var(--accent-warning);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h6 class="fw-bold mb-3 animate-fade-up"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h6>
        <div class="row g-3 animate-fade-up">
            <div class="col-sm-6 col-lg-3">
                <a href="manage_websites.php" class="glass-panel p-3 d-flex align-items-center gap-3 text-decoration-none" style="color:var(--text-primary);">
                    <i class="fas fa-globe fa-lg" style="color:var(--accent-primary);"></i>
                    <div><div class="fw-bold small">Manage Websites</div><div class="text-secondary" style="font-size:.75rem;">Approve / Reject</div></div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="manage_users.php" class="glass-panel p-3 d-flex align-items-center gap-3 text-decoration-none" style="color:var(--text-primary);">
                    <i class="fas fa-users fa-lg" style="color:var(--accent-secondary);"></i>
                    <div><div class="fw-bold small">Manage Users</div><div class="text-secondary" style="font-size:.75rem;">View all accounts</div></div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="developer_requests.php" class="glass-panel p-3 d-flex align-items-center gap-3 text-decoration-none" style="color:var(--text-primary);">
                    <i class="fas fa-user-clock fa-lg" style="color:var(--accent-warning);"></i>
                    <div><div class="fw-bold small">Dev Requests</div><div class="text-secondary" style="font-size:.75rem;"><?= $pending_dev['total'] ?> pending</div></div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="../index.php" class="glass-panel p-3 d-flex align-items-center gap-3 text-decoration-none" style="color:var(--text-primary);">
                    <i class="fas fa-eye fa-lg" style="color:var(--accent-tertiary);"></i>
                    <div><div class="fw-bold small">View Site</div><div class="text-secondary" style="font-size:.75rem;">Public front-end</div></div>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
// Wire sidebar toggle button to theme.js sidebar logic
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    } else {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
});
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.remove('open');
    this.classList.remove('active');
    document.body.style.overflow = '';
});
</script>
</body>
</html>
