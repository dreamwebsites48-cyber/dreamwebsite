<?php
require_once __DIR__ . '/../config.php';

// 🔒 ADMIN SECURITY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// ✅ APPROVE — prepared statement
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE users SET developer_status='approved', role='developer' WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['msg'] = 'Developer Approved!';
    header('Location: developer_requests.php');
    exit();
}

// ❌ REJECT — prepared statement
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $stmt = $conn->prepare("UPDATE users SET developer_status='rejected' WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['msg'] = 'Developer Rejected!';
    header('Location: developer_requests.php');
    exit();
}

// 📊 FETCH REQUESTS — prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE developer_status='pending' ORDER BY created_at DESC");
$stmt->execute();
$res   = $stmt->get_result();
$count = $res->num_rows;

$active_link = 'dev_req';
include "../includes/header.php";
include "../includes/admin_sidebar.php";
?>

    <!-- Main Content -->
    <div class="dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up flex-wrap gap-3">
            <div>
                <h2 class="display-6 fw-bold m-0">Developer <span class="text-gradient">Requests</span></h2>
                <p class="text-secondary mt-1 mb-0">Approve or reject new developer applications.</p>
            </div>
            <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;"><i class="fas fa-moon"></i></button>
        </div>

        <?php if(isset($_SESSION['msg'])): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>showToast("<?= addslashes($_SESSION['msg']) ?>","success"));</script>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <!-- 📋 TABLE -->
        <div class="glass-panel p-0 overflow-hidden animate-fade-up">
            <div class="p-4 border-bottom" style="border-color:var(--glass-border)!important;">
                <h5 class="fw-bold m-0">Pending Requests <span class="badge bg-warning text-dark ms-2"><?= $count ?></span></h5>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle m-0" style="background: transparent;">
                    <thead style="border-bottom:1px solid var(--glass-border);">
                        <tr>
                            <th class="py-3 px-3 text-secondary fw-normal">Applicant</th>
                            <th class="py-3 px-3 text-secondary fw-normal d-none d-sm-table-cell">Applied</th>
                            <th class="py-3 px-3 text-secondary fw-normal text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($res->num_rows > 0): while($row = $res->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td class="py-3 px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=6366f1&color=fff" class="rounded-circle flex-shrink-0" width="38" height="38" alt="">
                                    <div class="min-w-0">
                                        <div class="fw-bold text-truncate" style="max-width:150px;"><?= htmlspecialchars($row['name']) ?></div>
                                        <div class="text-secondary small text-truncate" style="max-width:180px;"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-secondary small d-none d-sm-table-cell"><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                            <td class="py-3 px-3 text-end">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success rounded-pill" onclick="return confirm('Approve developer: <?= htmlspecialchars(addslashes($row['name'])) ?>?')"><i class="fas fa-check me-1"></i>Approve</a>
                                    <a href="?reject=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Reject developer: <?= htmlspecialchars(addslashes($row['name'])) ?>?')"><i class="fas fa-times me-1"></i>Reject</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" class="text-center py-5">
                            <i class="fas fa-user-clock fa-3x text-secondary mb-3 opacity-50 d-block"></i>
                            <p class="text-secondary m-0">No pending developer requests. 🎉</p>
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
</body>
</html>

