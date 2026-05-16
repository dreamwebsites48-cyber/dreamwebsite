<?php
/**
 * includes/admin_sidebar.php
 * Reusable Admin Sidebar — include AFTER header.php, BEFORE your main content.
 * Opens the .dashboard-wrapper div — caller must CLOSE it with </div>.
 * Requires: $conn, $_SESSION['name'], $active_link (string)
 */

// Badge: pending developer requests
$_pd = $conn->query("SELECT COUNT(*) as t FROM users WHERE developer_status='pending'")->fetch_assoc();
$_pending_dev = (int)($_pd['t'] ?? 0);

// Current active page key
$_active = $active_link ?? basename($_SERVER['PHP_SELF'], '.php');

function _sb_link(string $href, string $icon, string $label, string $key, string $active, ?int $badge = null): void {
    $isActive = (strpos($active, $key) !== false) ? ' active' : '';
    echo "<a href=\"{$href}\" class=\"sidebar-link{$isActive}\"><i class=\"fas fa-{$icon}\"></i> {$label}";
    if ($badge !== null && $badge > 0)
        echo " <span class=\"badge bg-warning text-dark rounded-pill ms-auto\">{$badge}</span>";
    echo "</a>";
}
?>
<!-- Mobile Sidebar Toggle Button -->
<button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Dashboard Wrapper opens here — caller must close with </div> after main content -->
<div class="dashboard-wrapper">

<!-- Admin Sidebar -->
<div class="sidebar-premium" id="adminSidebar">
    <div class="text-center mb-4 pt-2">
        <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <i class="fas fa-shield-alt fa-2x text-white"></i>
        </div>
        <h5 class="fw-bold m-0">Admin Panel</h5>
        <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill mt-1">
            <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>
        </span>
    </div>
    <div class="d-flex flex-column gap-1">
        <?php _sb_link('dashboard.php',         'chart-pie',         'Dashboard',         'dashboard',    $_active); ?>
        <div class="text-secondary fw-bold mt-3 mb-2 px-2 text-uppercase" style="font-size:.7rem;letter-spacing:1px;">User Management</div>
        <?php _sb_link('manage_users.php',       'users',             'All Users',         'manage_users', $_active); ?>
        <?php _sb_link('manage_developers.php',  'code',              'Developers',        'manage_dev',   $_active); ?>
        <?php _sb_link('developer_requests.php', 'user-clock',        'Dev Requests',      'dev_req',      $_active, $_pending_dev); ?>
        <?php _sb_link('password_requests.php',  'key',               'Password Requests', 'password_req', $_active); ?>
        <div class="text-secondary fw-bold mt-3 mb-2 px-2 text-uppercase" style="font-size:.7rem;letter-spacing:1px;">Content</div>
        <?php _sb_link('manage_websites.php',    'globe',             'Websites',          'manage_web',   $_active); ?>
        <div class="text-secondary fw-bold mt-3 mb-2 px-2 text-uppercase" style="font-size:.7rem;letter-spacing:1px;">System</div>
        <a href="../index.php"  class="sidebar-link"><i class="fas fa-external-link-alt"></i> View Site</a>
        <a href="../logout.php" class="sidebar-link" style="color:var(--accent-danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- Sidebar toggle JS — runs immediately after markup -->
<script>
(function(){
    var btn = document.getElementById('sidebarToggle'),
        sb  = document.getElementById('adminSidebar'),
        ov  = document.getElementById('sidebarOverlay');
    if (!btn || !sb || !ov) return;
    function openSb()  { sb.classList.add('open');    ov.classList.add('active');    document.body.style.overflow = 'hidden'; }
    function closeSb() { sb.classList.remove('open'); ov.classList.remove('active'); document.body.style.overflow = ''; }
    btn.addEventListener('click', function() { sb.classList.contains('open') ? closeSb() : openSb(); });
    ov.addEventListener('click', closeSb);
    sb.querySelectorAll('.sidebar-link').forEach(function(l) {
        l.addEventListener('click', function() { if (window.innerWidth < 992) closeSb(); });
    });
    window.addEventListener('resize', function() { if (window.innerWidth >= 992) closeSb(); });
})();
</script>
