<?php
require_once __DIR__ . '/../config.php';

// 🔐 ADMIN SECURITY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 🔍 FILTER INPUT
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$status = isset($_GET['status']) ? $_GET['status'] : "";

// 📄 PAGINATION
$limit = 10;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

// ✅ APPROVE
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE offers SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $_SESSION['msg'] = "Website Approved!";
    header("Location: manage_websites.php"); exit();
}

// ❌ REJECT
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $stmt = $conn->prepare("UPDATE offers SET status='rejected' WHERE id=?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $_SESSION['msg'] = "Website Rejected!";
    header("Location: manage_websites.php"); exit();
}

// ➕ ADD OFFER
if (isset($_POST['add_offer'])) {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $website_link = trim($_POST['website_link']);
    $image_path  = trim($_POST['image_path']);
    $dev_id      = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO offers (developer_id, title, description, price, image_path, website_link, status) VALUES (?, ?, ?, ?, ?, ?, 'approved')");
    $stmt->bind_param("issdss", $dev_id, $title, $description, $price, $image_path, $website_link);
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Website Added Successfully!";
    } else {
        $_SESSION['msg_error'] = "Failed to add website!";
    }
    header("Location: manage_websites.php"); exit();
}

// ✏️ EDIT
if (isset($_POST['edit_website'])) {
    $offer_id    = intval($_POST['offer_id']);
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $website_link = trim($_POST['website_link']);

    $stmt = $conn->prepare("UPDATE offers SET title=?, description=?, price=?, website_link=? WHERE id=?");
    $stmt->bind_param("ssdsi", $title, $description, $price, $website_link, $offer_id);
    $_SESSION['msg'] = $stmt->execute() ? "Website Updated!" : "Update failed!";
    header("Location: manage_websites.php"); exit();
}

// 🗑 DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM offers WHERE id=?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $_SESSION['msg'] = "Website Deleted!";
    header("Location: manage_websites.php"); exit();
}

// 📊 QUERY
$query  = "SELECT * FROM offers WHERE 1";
$params = []; $types = "";

if (!empty($search)) { $query .= " AND title LIKE ?"; $params[] = "%$search%"; $types .= "s"; }
if (!empty($status))  { $query .= " AND status = ?";    $params[] = $status;        $types .= "s"; }
$query .= " ORDER BY id DESC LIMIT ?, ?";
$params[] = $start; $params[] = $limit; $types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// COUNT
$cq = 'SELECT COUNT(*) as total FROM offers WHERE 1'
    . (!empty($search) ? ' AND title LIKE ?' : '')
    . (!empty($status) ? ' AND status = ?'   : '');
$cstmt = $conn->prepare($cq);
$count_params = array_slice($params, 0, count($params) - 2);
$count_types  = substr($types, 0, strlen($types) - 2);
if (!empty($count_params)) $cstmt->bind_param($count_types, ...$count_params);
$cstmt->execute();
$total = $cstmt->get_result()->fetch_assoc()['total'];
$pages = ceil($total / $limit);

// Pending dev requests for badge
$pending_dev = $conn->query("SELECT COUNT(*) as total FROM users WHERE developer_status='pending'")->fetch_assoc();

include "../includes/header.php";
?>

<!-- Mobile Sidebar Toggle -->
<button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar-premium" id="adminSidebar">
        <div class="text-center mb-4 pt-2">
            <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <i class="fas fa-shield-alt fa-2x text-white"></i>
            </div>
            <h5 class="fw-bold m-0">Admin Panel</h5>
            <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill mt-1"><?= htmlspecialchars($_SESSION['name']) ?></span>
        </div>
        <div class="d-flex flex-column gap-1">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">User Management</div>
            <a href="manage_users.php" class="sidebar-link"><i class="fas fa-users"></i> All Users</a>
            <a href="manage_developers.php" class="sidebar-link"><i class="fas fa-code"></i> Developers</a>
            <a href="developer_requests.php" class="sidebar-link d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-clock"></i> Dev Requests</span>
                <?php if ($pending_dev['total'] > 0): ?><span class="badge bg-warning text-dark rounded-pill"><?= $pending_dev['total'] ?></span><?php endif; ?>
            </a>
            <a href="password_requests.php" class="sidebar-link"><i class="fas fa-key"></i> Password Requests</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">Content</div>
            <a href="manage_websites.php" class="sidebar-link active"><i class="fas fa-globe"></i> Websites</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-2 text-uppercase" style="letter-spacing:1px;font-size:.7rem;">System</div>
            <a href="../index.php" class="sidebar-link"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="../logout.php" class="sidebar-link" style="color:var(--accent-danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up flex-wrap gap-3">
            <div>
                <h2 class="display-6 fw-bold m-0">Manage <span class="text-gradient">Websites</span></h2>
                <p class="text-secondary mt-1 mb-0">Review, approve, or remove listings.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-premium rounded-pill" data-bs-toggle="modal" data-bs-target="#addWebsiteModal">
                    <i class="fas fa-plus me-1"></i> Add Website
                </button>
                <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>showToast("<?= addslashes($_SESSION['msg']) ?>","success"));</script>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['msg_error'])): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>showToast("<?= addslashes($_SESSION['msg_error']) ?>","error"));</script>
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>

        <!-- Search -->
        <div class="glass-panel p-4 mb-4 animate-fade-up">
            <form method="GET" class="row g-3">
                <div class="col-12 col-md-5">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute text-secondary" style="top:14px;left:14px;"></i>
                        <input type="text" name="search" class="form-control-premium" style="padding-left:40px;" placeholder="Search title..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="position-relative">
                        <i class="fas fa-filter position-absolute text-secondary" style="top:14px;left:14px;"></i>
                        <select name="status" class="form-control-premium" style="padding-left:40px;appearance:auto;">
                            <option value="">All Status</option>
                            <option value="pending"   <?= $status==="pending"   ? "selected" : "" ?>>Pending</option>
                            <option value="approved"  <?= $status==="approved"  ? "selected" : "" ?>>Approved</option>
                            <option value="rejected"  <?= $status==="rejected"  ? "selected" : "" ?>>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-3 col-md-2">
                    <button class="btn btn-premium w-100" style="height:46px;">Search</button>
                </div>
                <div class="col-3 col-md-2">
                    <a href="manage_websites.php" class="btn btn-premium-outline w-100" style="height:46px;">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="glass-panel p-0 overflow-hidden animate-fade-up">
            <div class="p-4 border-bottom" style="border-color:var(--glass-border)!important;">
                <h5 class="fw-bold m-0">All Websites <span class="badge bg-primary ms-2"><?= $total ?></span></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle m-0" style="background:transparent;">
                    <thead style="border-bottom:1px solid var(--glass-border);">
                        <tr>
                            <th class="py-3 px-3 text-secondary fw-normal">Website</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-sm-table-cell">Price</th>
                            <th class="py-3 px-3 text-secondary fw-normal">Status</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-md-table-cell">Link</th>
                            <th class="py-3 px-3 text-secondary fw-normal text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res->num_rows > 0): while ($row = $res->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td class="py-3 px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <?php
                                    $thumb = $row['image_path'] ?? '';
                                    // Check if it's a URL (starts with http) or local path
                                    $imgSrc = (str_starts_with($thumb, 'http') || str_starts_with($thumb, '//'))
                                        ? htmlspecialchars($thumb)
                                        : (!empty($thumb) ? '../' . htmlspecialchars($thumb) : '');
                                    ?>
                                    <?php if ($imgSrc): ?>
                                        <img src="<?= $imgSrc ?>" style="width:48px;height:48px;object-fit:cover;border-radius:8px;flex-shrink:0;" alt="">
                                    <?php else: ?>
                                        <div style="width:48px;height:48px;border-radius:8px;background:rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-image text-secondary"></i></div>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-truncate" style="max-width:160px;"><?= htmlspecialchars($row['title']) ?></div>
                                        <div class="text-secondary small"><?= date("d M Y", strtotime($row['created_at'])) ?></div>
                                        <div class="text-success fw-bold small d-sm-none">$<?= number_format($row['price'], 2) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-success fw-bold d-none d-sm-table-cell">$<?= number_format($row['price'], 2) ?></td>
                            <td class="py-3 px-3">
                                <?php if ($row['status'] === 'approved'): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success">Approved</span>
                                <?php elseif ($row['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 d-none d-md-table-cell">
                                <?php if (!empty($row['website_link'])): ?>
                                    <a href="<?= htmlspecialchars($row['website_link']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill"><i class="fas fa-external-link-alt"></i></a>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-end">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <a href="edit_website.php?id=<?= $row['id'] ?>&from=websites" class="btn btn-sm btn-outline-warning rounded-pill" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success rounded-pill" onclick="return confirm('Approve?')" title="Approve"><i class="fas fa-check"></i></a>
                                        <a href="?reject=<?= $row['id'] ?>"  class="btn btn-sm btn-outline-danger rounded-pill"  onclick="return confirm('Reject?')"  title="Reject"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Delete permanently?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-secondary mb-3 opacity-50 d-block"></i>
                            <p class="text-secondary m-0">No websites found.</p>
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?>
            <div class="p-3 border-top" style="border-color:var(--glass-border)!important;">
                <div class="d-flex justify-content-center flex-wrap gap-2">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
                           class="btn <?= $i == $page ? 'btn-premium' : 'btn-premium-outline' ?> btn-sm px-3"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Website Modal -->
<div class="modal fade" id="addWebsiteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="background:var(--bg-secondary);border:1px solid var(--glass-border);border-radius:16px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Add New Website</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label text-secondary small">Title</label>
                <input type="text" name="title" class="form-control-premium" required placeholder="Website Title">
            </div>
            <div class="col-md-4">
                <label class="form-label text-secondary small">Price ($)</label>
                <input type="number" step="0.01" name="price" class="form-control-premium" required placeholder="0.00">
            </div>
            <div class="col-md-6">
                <label class="form-label text-secondary small">Image URL</label>
                <input type="url" name="image_path" class="form-control-premium" placeholder="https://images.unsplash.com/..." required>
            </div>
            <div class="col-md-6">
                <label class="form-label text-secondary small">Website Link</label>
                <input type="url" name="website_link" class="form-control-premium" placeholder="https://example.com" required>
            </div>
            <div class="col-12">
                <label class="form-label text-secondary small">Description</label>
                <textarea name="description" class="form-control-premium" rows="4" placeholder="Describe the website..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_offer" class="btn btn-premium">Add Website</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const s = document.getElementById('adminSidebar'), o = document.getElementById('sidebarOverlay');
    const open = s.classList.toggle('open'); o.classList.toggle('active', open);
    document.body.style.overflow = open ? 'hidden' : '';
});
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.remove('open');
    this.classList.remove('active'); document.body.style.overflow = '';
});
</script>
</body>
</html>
