<?php
require_once __DIR__ . '/../config.php';

// 🔐 ADMIN SECURITY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// ✅ APPROVE & EMAIL
if(isset($_POST['approve'])){
    $request_id = intval($_POST['request_id']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    
    // Hash password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update user password
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss", $hashed, $email);
    
    if ($stmt->execute()) {
        // Mark request as completed — prepared statement
        $upd = $conn->prepare("UPDATE password_requests SET status='completed' WHERE id=?");
        $upd->bind_param('i', $request_id);
        $upd->execute(); $upd->close();
        
        // Send email
        $subject = "Your Password Has Been Reset - DreamWebsites";
        $message = "Hello,\n\nYour password reset request has been approved by the Admin.\n\nYour new password is: $new_password\n\nYou can now log in using this password. Please change it after your first login for security purposes.\n\nBest Regards,\nDreamWebsites Admin";
        $headers = "From: admin@dreamwebsites.com\r\n";
        
        @mail($email, $subject, $message, $headers);
        
        $_SESSION['msg'] = "Password updated and email sent!";
    } else {
        $_SESSION['msg_error'] = "Failed to update password.";
    }
    header("Location: password_requests.php");
    exit();
}

// ❌ DELETE
if (isset($_GET['delete'])) {
    $id  = intval($_GET['delete']);
    $del = $conn->prepare('DELETE FROM password_requests WHERE id=?');
    $del->bind_param('i', $id);
    $del->execute(); $del->close();
    $_SESSION['msg'] = 'Request deleted!';
    header('Location: password_requests.php');
    exit();
}

// 📊 FETCH — prepared statement
$pstmt = $conn->prepare("SELECT * FROM password_requests WHERE status='pending' ORDER BY id DESC");
$pstmt->execute();
$res   = $pstmt->get_result();
$count = $res->num_rows;

$active_link = 'password_req';
include "../includes/header.php";
include "../includes/admin_sidebar.php";
?>

    <div class="dashboard-main">
        
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up flex-wrap gap-3">
            <div>
                <h2 class="display-6 fw-bold m-0">Password <span class="text-gradient">Requests</span></h2>
                <p class="text-secondary mt-1 mb-0">Manage forgotten password reset requests.</p>
            </div>
            <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;"><i class="fas fa-moon"></i></button>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>showToast("<?= addslashes($_SESSION['msg']) ?>","success"));</script>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['msg_error'])): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>showToast("<?= addslashes($_SESSION['msg_error']) ?>","error"));</script>
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>

        <!-- 📋 TABLE -->
        <div class="glass-panel p-0 overflow-hidden animate-fade-up" style="animation-delay: 0.1s;">
            <div class="p-4 border-bottom" style="border-color: var(--glass-border) !important;">
                <h5 class="fw-bold m-0">Pending Requests <span class="badge bg-warning text-dark ms-2"><?= $count ?></span></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle m-0" style="background: transparent;">
                    <thead style="border-bottom:1px solid var(--glass-border);">
                        <tr>
                            <th class="py-3 px-3 text-secondary fw-normal">Email</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-md-table-cell">Requested Password</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-sm-table-cell">Date</th>
                            <th class="py-3 px-3 text-secondary fw-normal text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($res->num_rows > 0): while($row = $res->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td class="py-3 px-3 fw-bold text-info small text-truncate" style="max-width:160px;"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="py-3 px-3 font-monospace text-warning small d-none d-md-table-cell"><?= htmlspecialchars($row['requested_password']) ?></td>
                            <td class="py-3 px-3 text-secondary small d-none d-sm-table-cell"><?= date("d M Y H:i", strtotime($row['created_at'])) ?></td>
                            <td class="py-3 px-3 text-end">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <button class="btn btn-sm btn-outline-success rounded-pill"
                                        onclick="openApproveModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['email'])) ?>', '<?= htmlspecialchars(addslashes($row['requested_password'])) ?>')"
                                    ><i class="fas fa-check me-1"></i>Approve</button>
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill"
                                       onclick="return confirm('Delete without action?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center py-5">
                            <i class="fas fa-key fa-3x text-secondary mb-3 opacity-50 d-block"></i>
                            <p class="text-secondary m-0">No pending password requests.</p>
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-panel" style="background: var(--bg-secondary); border: 1px solid var(--glass-border);">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Approve Password Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <p class="text-secondary small mb-4">You are about to change the password for <span id="modalEmailDisplay" class="text-primary fw-bold"></span></p>
            <input type="hidden" name="request_id" id="modalRequestId" value="">
            <input type="hidden" name="email" id="modalEmail" value="">
            <div class="mb-3">
                <label class="form-label text-secondary small">New Password (Edit if needed)</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-secondary border-secondary"><i class="fas fa-lock"></i></span>
                    <input type="text" name="new_password" id="modalNewPassword" class="form-control-premium" required>
                </div>
            </div>
            <div class="alert alert-info py-2 small mb-0">
                <i class="fas fa-info-circle me-1"></i> Submitting will automatically email this password to the user.
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="approve" class="btn btn-premium">Save & Email User</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
function openApproveModal(id, email, password) {
    document.getElementById('modalRequestId').value = id;
    document.getElementById('modalEmail').value = email;
    document.getElementById('modalEmailDisplay').innerText = email;
    document.getElementById('modalNewPassword').value = password;
    var myModal = new bootstrap.Modal(document.getElementById('approveModal'));
    myModal.show();
}
</script>
</body>
</html>

