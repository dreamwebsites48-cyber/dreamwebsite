<?php
require_once __DIR__ . '/../config.php';

// 🔐 SECURITY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'developer') {
    header('Location: ../login.php');
    exit();
}

$dev_id = $_SESSION['user_id'];
$name   = $_SESSION['name'] ?? "Developer";

function getCount($conn, $query, $dev_id) {
    $stmt = $conn->prepare($query);
    if (!$stmt) return 0;
    $stmt->bind_param("i", $dev_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['t'] ?? 0;
}

$total    = getCount($conn, "SELECT COUNT(*) as t FROM offers WHERE developer_id=?", $dev_id);
$approved = getCount($conn, "SELECT COUNT(*) as t FROM offers WHERE developer_id=? AND status='approved'", $dev_id);
$pending  = getCount($conn, "SELECT COUNT(*) as t FROM offers WHERE developer_id=? AND status='pending'", $dev_id);
$rejected = getCount($conn, "SELECT COUNT(*) as t FROM offers WHERE developer_id=? AND status='rejected'", $dev_id);

$price_filter = $_GET['price'] ?? "";
$query = "SELECT * FROM offers WHERE developer_id=?";
if ($price_filter === "50-100")     $query .= " AND price BETWEEN 50 AND 100";
elseif ($price_filter === "100-1000")  $query .= " AND price BETWEEN 100 AND 1000";
elseif ($price_filter === "1000-5000") $query .= " AND price BETWEEN 1000 AND 5000";
$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $dev_id);
$stmt->execute();
$result = $stmt->get_result();

include "../includes/header.php";
?>

<!-- Mobile Sidebar Toggle -->
<button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar-premium" id="devSidebar">
        <div class="text-center mb-4 pt-2">
            <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <i class="fas fa-code fa-2x text-white"></i>
            </div>
            <h5 class="fw-bold m-0">Dev Panel</h5>
            <span class="badge bg-info bg-opacity-25 text-info rounded-pill mt-1"><?= htmlspecialchars($name) ?></span>
        </div>
        <div class="d-flex flex-column gap-1">
            <a href="dashboard.php" class="sidebar-link active"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">Content</div>
            <a href="add_website.php" class="sidebar-link"><i class="fas fa-plus-circle"></i> Add Website</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">System</div>
            <a href="../index.php" class="sidebar-link"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="../logout.php" class="sidebar-link" style="color:var(--accent-danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up flex-wrap gap-3">
            <div>
                <h2 class="display-6 fw-bold m-0">Developer <span class="text-gradient">Dashboard</span></h2>
                <p class="text-secondary mt-1 mb-0">Welcome back, <?= htmlspecialchars($name) ?> 👨‍💻</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="d-flex align-items-center gap-2 glass-panel px-3 py-2 rounded-pill">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($name) ?>&background=6366f1&color=fff" class="rounded-circle" width="30" height="30" alt="">
                    <span class="fw-bold d-none d-sm-inline"><?= htmlspecialchars($name) ?></span>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>showToast("<?= addslashes($_SESSION['msg']) ?>","success"));</script>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <!-- Stats -->
        <h6 class="fw-bold mb-3 animate-fade-up"><i class="fas fa-chart-line text-primary me-2"></i>Your Statistics</h6>
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-primary border-opacity-25">
                    <div><div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Total Offers</div><h2 class="m-0 fw-bold text-primary"><?= $total ?></h2></div>
                    <div class="stat-card-icon" style="background:rgba(59,130,246,.1);color:var(--accent-primary);"><i class="fas fa-box"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-success border-opacity-25">
                    <div><div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Approved</div><h2 class="m-0 fw-bold text-success"><?= $approved ?></h2></div>
                    <div class="stat-card-icon" style="background:rgba(16,185,129,.1);color:var(--accent-tertiary);"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-warning border-opacity-25">
                    <div><div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Pending</div><h2 class="m-0 fw-bold text-warning"><?= $pending ?></h2></div>
                    <div class="stat-card-icon" style="background:rgba(245,158,11,.1);color:var(--accent-warning);"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3 animate-fade-up">
                <div class="glass-panel stat-card h-100 border-danger border-opacity-25">
                    <div><div class="text-secondary text-uppercase mb-1" style="font-size:.75rem;letter-spacing:1px;">Rejected</div><h2 class="m-0 fw-bold text-danger"><?= $rejected ?></h2></div>
                    <div class="stat-card-icon" style="background:rgba(239,68,68,.1);color:var(--accent-danger);"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
        </div>

        <!-- Offers Table -->
        <div class="glass-panel p-0 overflow-hidden animate-fade-up">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-color:var(--glass-border)!important;">
                <h5 class="fw-bold m-0"><i class="fas fa-list me-2 text-gradient"></i>Your Listings</h5>
                <form method="GET" class="d-flex gap-2">
                    <select name="price" class="form-control-premium" style="appearance:auto;min-width:140px;" onchange="this.form.submit()">
                        <option value="">All Prices</option>
                        <option value="50-100"    <?= $price_filter == '50-100'    ? 'selected' : '' ?>>$50 – $100</option>
                        <option value="100-1000"  <?= $price_filter == '100-1000'  ? 'selected' : '' ?>>$100 – $1,000</option>
                        <option value="1000-5000" <?= $price_filter == '1000-5000' ? 'selected' : '' ?>>$1,000 – $5,000</option>
                    </select>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle m-0" style="background:transparent;">
                    <thead style="border-bottom:1px solid var(--glass-border);">
                        <tr>
                            <th class="py-3 px-3 text-secondary fw-normal">Website</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-sm-table-cell">Price</th>
                            <th class="py-3 px-3 text-secondary fw-normal">Status</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-md-table-cell">Live Link</th>
                            <th class="py-3 px-3 text-secondary fw-normal text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td class="py-3 px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <?php
                                    $thumb = $row['image_path'] ?? '';
                                    $imgSrc = (str_starts_with($thumb, 'http') || str_starts_with($thumb, '//'))
                                        ? htmlspecialchars($thumb)
                                        : (!empty($thumb) ? '../' . htmlspecialchars($thumb) : '');
                                    ?>
                                    <?php if ($imgSrc): ?>
                                        <img src="<?= $imgSrc ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                                    <?php else: ?>
                                        <div style="width:44px;height:44px;border-radius:8px;background:rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-image text-secondary"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-truncate" style="max-width:150px;"><?= htmlspecialchars($row['title']) ?></div>
                                        <div class="text-success fw-bold small d-sm-none">$<?= number_format($row['price'], 2) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-success fw-bold d-none d-sm-table-cell">$<?= number_format($row['price'], 2) ?></td>
                            <td class="py-3 px-3">
                                <?php
                                if ($row['status'] === 'approved')
                                    echo "<span class='badge bg-success bg-opacity-25 text-success border border-success'>Approved</span>";
                                elseif ($row['status'] === 'rejected')
                                    echo "<span class='badge bg-danger bg-opacity-25 text-danger border border-danger'>Rejected</span>";
                                else
                                    echo "<span class='badge bg-warning bg-opacity-25 text-warning border border-warning'>Pending</span>";
                                ?>
                            </td>
                            <td class="py-3 px-3 d-none d-md-table-cell">
                                <?php if (!empty($row['website_link'])): ?>
                                    <a href="<?= htmlspecialchars($row['website_link']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3"><i class="fas fa-external-link-alt me-1"></i>View</a>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-end">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill ms-1" onclick="return confirm('Delete this listing?')" title="Delete"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-secondary mb-3 opacity-50 d-block"></i>
                            <p class="text-secondary m-0">No listings yet. <a href="add_website.php" class="fw-bold">Add your first one!</a></p>
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const s = document.getElementById('devSidebar'), o = document.getElementById('sidebarOverlay');
    const open = s.classList.toggle('open'); o.classList.toggle('active', open);
    document.body.style.overflow = open ? 'hidden' : '';
});
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.getElementById('devSidebar').classList.remove('open');
    this.classList.remove('active'); document.body.style.overflow = '';
});
</script>
</body>
</html>
