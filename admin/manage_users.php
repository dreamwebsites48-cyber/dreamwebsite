<?php
require_once __DIR__ . '/../config.php';

// 🔐 ADMIN SECURITY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$admin_id = $_SESSION['user_id'];
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_f   = isset($_GET['role'])   ? $_GET['role']         : '';

// ❌ DELETE USER
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $admin_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id); $stmt->execute();
        $_SESSION['msg'] = "User deleted successfully.";
    } else {
        $_SESSION['msg_error'] = "You cannot delete your own account!";
    }
    header("Location: manage_users.php"); exit();
}

// 🔑 CHANGE PASSWORD
if (isset($_POST['change_password'])) {
    $uid  = intval($_POST['user_id']);
    $pass = $_POST['new_password'] ?? '';
    if (strlen($pass) < 6) {
        $_SESSION['msg_error'] = "Password must be at least 6 characters!";
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $uid);
        $_SESSION['msg'] = $stmt->execute() ? "Password updated!" : "Update failed.";
    }
    header("Location: manage_users.php"); exit();
}

// 📊 QUERY
$params = []; $types = ''; $where = '';
if (!empty($search)) { $where .= ' AND (name LIKE ? OR email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }
if (!empty($role_f)) { $where .= ' AND role=?'; $params[] = $role_f; $types .= 's'; }

$stmt = $conn->prepare("SELECT * FROM users WHERE 1$where ORDER BY id DESC");
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res   = $stmt->get_result();
$count = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];

$active_link = 'manage_users';
include "../includes/header.php";
include "../includes/admin_sidebar.php";
?>

    <!-- Main Content -->
    <div class="dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up flex-wrap gap-3">
            <div>
                <h2 class="display-6 fw-bold m-0">Manage <span class="text-gradient">Users</span></h2>
                <p class="text-secondary mt-1 mb-0">View, search, and manage all platform accounts.</p>
            </div>
            <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
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
                        <input type="text" name="search" class="form-control-premium" style="padding-left:40px;" placeholder="Search name or email…" value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="position-relative">
                        <i class="fas fa-filter position-absolute text-secondary" style="top:14px;left:14px;"></i>
                        <select name="role" class="form-control-premium" style="padding-left:40px;appearance:auto;">
                            <option value="">All Roles</option>
                            <option value="user"      <?= $role_f==='user'      ?'selected':'' ?>>User</option>
                            <option value="developer" <?= $role_f==='developer' ?'selected':'' ?>>Developer</option>
                            <option value="admin"     <?= $role_f==='admin'     ?'selected':'' ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="col-3 col-md-2">
                    <button class="btn btn-premium w-100" style="height:46px;">Search</button>
                </div>
                <div class="col-3 col-md-2">
                    <a href="manage_users.php" class="btn btn-premium-outline w-100" style="height:46px;">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="glass-panel p-0 overflow-hidden animate-fade-up">
            <div class="p-4 border-bottom" style="border-color:var(--glass-border)!important;">
                <h5 class="fw-bold m-0">All Users <span class="badge bg-primary ms-2"><?= $count ?></span></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle m-0" style="background:transparent;">
                    <thead style="border-bottom:1px solid var(--glass-border);">
                        <tr>
                            <th class="py-3 px-3 text-secondary fw-normal">User</th>
                            <th class="py-3 px-3 text-secondary fw-normal">Role</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-sm-table-cell">Joined</th>
                            <th class="py-3 px-3 text-secondary fw-normal text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res->num_rows > 0): while ($row = $res->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td class="py-3 px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=6366f1&color=fff" class="rounded-circle flex-shrink-0" width="38" height="38" alt="">
                                    <div class="min-w-0">
                                        <div class="fw-bold text-truncate" style="max-width:160px;"><?= htmlspecialchars($row['name']) ?></div>
                                        <div class="text-secondary small text-truncate" style="max-width:180px;"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <?php if ($row['role'] === 'admin'): ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Admin</span>
                                <?php elseif ($row['role'] === 'developer'): ?>
                                    <span class="badge bg-info bg-opacity-25 text-info border border-info">Developer</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-secondary small d-none d-sm-table-cell"><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                            <td class="py-3 px-3 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill"
                                        onclick="openPasswordModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>')"
                                        title="Change password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <?php if ($row['id'] != $admin_id): ?>
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill"
                                       onclick="return confirm('Permanently delete <?= htmlspecialchars(addslashes($row['name'])) ?>?')"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center py-5">
                            <i class="fas fa-users-slash fa-3x text-secondary mb-3 opacity-50 d-block"></i>
                            <p class="text-secondary m-0">No users found.</p>
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--bg-secondary);border:1px solid var(--glass-border);border-radius:16px;">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Change Password</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <p class="text-secondary small mb-3">New password for <span id="modalUserName" class="text-primary fw-bold"></span></p>
          <input type="hidden" name="user_id" id="modalUserId">
          <div class="mb-2">
            <label class="form-label text-secondary small">New Password (min 6 chars)</label>
            <input type="password" name="new_password" class="form-control-premium" required minlength="6" placeholder="Enter new password">
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="change_password" class="btn btn-premium">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
function openPasswordModal(id, name) {
    document.getElementById('modalUserId').value = id;
    document.getElementById('modalUserName').innerText = name;
    new bootstrap.Modal(document.getElementById('passwordModal')).show();
}
</script>
</body>
</html>
