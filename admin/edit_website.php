<?php
require_once __DIR__ . '/../config.php';

// 🔐 ADMIN SECURITY
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error   = "";
$success = "";

// ── Validate offer ID ──
if (!isset($_GET['id']) || !trim($_GET['id'])) {
    die("❌ Invalid Request. No ID Provided.");
}
$id = intval($_GET['id']);

// ── Fetch offer ──
$stmt = $conn->prepare("SELECT * FROM offers WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) die("❌ Offer not found.");
$offer = $result->fetch_assoc();

// ── Detect return page ──
$return = 'manage_websites.php';

// 🗒 Fetch gallery images — prepared statement
function fetchImages($conn, $id) {
    $s = $conn->prepare('SELECT * FROM offer_images WHERE offer_id=? ORDER BY sort_order ASC');
    $s->bind_param('i', $id);
    $s->execute();
    return $s->get_result()->fetch_all(MYSQLI_ASSOC);
}
$existingImages = fetchImages($conn, $id);

// ── DELETE a single gallery image — prepared statements ──
if (isset($_GET['del_img'])) {
    $img_id = intval($_GET['del_img']);
    $s = $conn->prepare('SELECT * FROM offer_images WHERE id=? AND offer_id=? LIMIT 1');
    $s->bind_param('ii', $img_id, $id); $s->execute();
    $row = $s->get_result()->fetch_assoc(); $s->close();
    if ($row) {
        $fp = '../' . $row['image_path'];
        if (file_exists($fp) && is_file($fp)) @unlink($fp);
        $d = $conn->prepare('DELETE FROM offer_images WHERE id=?');
        $d->bind_param('i', $img_id); $d->execute(); $d->close();
        $f = $conn->prepare('SELECT image_path FROM offer_images WHERE offer_id=? ORDER BY sort_order ASC LIMIT 1');
        $f->bind_param('i', $id); $f->execute();
        $firstImg = $f->get_result()->fetch_assoc(); $f->close();
        $newMain  = $firstImg ? $firstImg['image_path'] : '';
        $u = $conn->prepare('UPDATE offers SET image_path=? WHERE id=?');
        $u->bind_param('si', $newMain, $id); $u->execute(); $u->close();
    }
    header('Location: edit_website.php?id=' . $id . '&from=' . urlencode($_GET['from'] ?? '') . '&msg=img_deleted');
    exit();
}

// ── HANDLE UPDATE ──
if (isset($_POST['update'])) {
    if (!is_dir("../uploads/images")) mkdir("../uploads/images", 0777, true);

    $title        = htmlspecialchars(trim($_POST['title']));
    $desc         = htmlspecialchars(trim($_POST['description']));
    $price        = floatval($_POST['price']);
    $discount     = intval($_POST['discount'] ?? 0);
    $website_link = htmlspecialchars(trim($_POST['website_link']));
    $coupon       = htmlspecialchars(trim($_POST['coupon'] ?? ''));
    $newStatus    = in_array($_POST['status'] ?? '', ['pending','approved','rejected']) ? $_POST['status'] : $offer['status'];

    if (empty($title) || $price <= 0) {
        $error = "Title and a valid Price are required!";
    } else {
        // ── Upload new images ──
        $currentCount = count($existingImages);
        $newPaths = [];

        if (!empty($_FILES['new_images']['name'][0])) {
            $files     = $_FILES['new_images'];
            $remaining = 5 - $currentCount;
            $toUpload  = min(count($files['name']), max(0, $remaining));
            for ($i = 0; $i < $toUpload; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
                $newname = uniqid("img_") . "." . $ext;
                $dest    = "../uploads/images/" . $newname;
                if (move_uploaded_file($files['tmp_name'][$i], $dest)) $newPaths[] = "uploads/images/" . $newname;
            }
        }

        if (!empty($newPaths)) {
            $mq = $conn->prepare('SELECT MAX(sort_order) as m FROM offer_images WHERE offer_id=?');
            $mq->bind_param('i', $id); $mq->execute();
            $maxOrder = $mq->get_result()->fetch_assoc()['m'] ?? -1; $mq->close();
            $imgStmt  = $conn->prepare("INSERT INTO offer_images (offer_id, image_path, sort_order) VALUES (?,?,?)");
            foreach ($newPaths as $i => $path) {
                $ord = $maxOrder + 1 + $i;
                $imgStmt->bind_param("isi", $id, $path, $ord);
                $imgStmt->execute();
            }
        }

        $f2 = $conn->prepare('SELECT image_path FROM offer_images WHERE offer_id=? ORDER BY sort_order ASC LIMIT 1');
        $f2->bind_param('i', $id); $f2->execute();
        $firstImg = $f2->get_result()->fetch_assoc(); $f2->close();
        $imgPath  = $firstImg ? $firstImg['image_path'] : $offer['image_path'];

        $upStmt = $conn->prepare("UPDATE offers SET title=?, description=?, price=?, discount=?, website_link=?, coupon_code=?, image_path=?, status=? WHERE id=?");
        $upStmt->bind_param("ssdissssi", $title, $desc, $price, $discount, $website_link, $coupon, $imgPath, $newStatus, $id);

        if ($upStmt->execute()) {
            $success = "✅ Website updated successfully!";
            $offer = array_merge($offer, [
                'title' => $title, 'description' => $desc, 'price' => $price,
                'discount' => $discount, 'website_link' => $website_link,
                'coupon' => $coupon, 'image_path' => $imgPath, 'status' => $newStatus
            ]);
            $existingImages = fetchImages($conn, $id);
        } else {
            $error = "❌ Failed to update. " . $conn->error;
        }
    }
}

$slots = 5 - count($existingImages);
$flash = $_GET['msg'] ?? '';
include "../includes/header.php";
?>

<div class="d-flex" style="min-height: calc(100vh - 76px);">
    <!-- Sidebar -->
    <div class="sidebar-premium" style="width: 260px; flex-shrink: 0;">
        <div class="text-center mb-4 pt-3">
            <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 15px;">
                <i class="fas fa-shield-alt fa-2x text-white"></i>
            </div>
            <h5 class="fw-bold m-0">Admin Panel</h5>
            <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill mt-1">Super Admin</span>
        </div>
        <div class="d-flex flex-column gap-1">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-chart-pie w-20px text-center"></i> Dashboard</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-3 text-uppercase" style="letter-spacing:1px;">User Management</div>
            <a href="manage_users.php" class="sidebar-link"><i class="fas fa-users w-20px text-center"></i> All Users</a>
            <a href="manage_developers.php" class="sidebar-link"><i class="fas fa-code w-20px text-center"></i> Developers</a>
            <a href="developer_requests.php" class="sidebar-link"><i class="fas fa-user-clock w-20px text-center"></i> Dev Requests</a>
            <a href="password_requests.php" class="sidebar-link"><i class="fas fa-key w-20px text-center"></i> Password Requests</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-3 text-uppercase" style="letter-spacing:1px;">Content Management</div>
            <a href="manage_websites.php" class="sidebar-link <?= $return==='manage_websites.php'?'active':'' ?>"><i class="fas fa-globe w-20px text-center"></i> Websites</a>

            <div class="text-secondary small fw-bold mt-3 mb-2 px-3 text-uppercase" style="letter-spacing:1px;">System</div>
            <a href="../index.php" class="sidebar-link"><i class="fas fa-external-link-alt w-20px text-center"></i> View Site</a>
            <a href="../logout.php" class="sidebar-link text-danger mt-auto"><i class="fas fa-sign-out-alt w-20px text-center"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4 p-md-5" style="background:var(--bg-primary);overflow-y:auto;">

        <div class="d-flex justify-content-between align-items-center mb-5 animate-fade-up">
            <div>
                <h2 class="display-6 fw-bold m-0">Edit <span class="text-gradient">Website #<?= $id ?></span></h2>
                <p class="text-secondary mt-2">Update details, website link, and gallery images.</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <a href="<?= $return ?>" class="btn btn-premium-outline rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Back</a>
            </div>
        </div>

        <?php if ($flash === 'img_deleted'): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>{ showToast("🗑 Image removed successfully.","success"); });</script>
        <?php endif; ?>
        <?php if ($error): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>{ showToast("<?= addslashes($error) ?>","danger"); });</script>
        <?php endif; ?>
        <?php if ($success): ?>
            <script>document.addEventListener('DOMContentLoaded',()=>{ showToast("<?= addslashes($success) ?>","success"); });</script>
        <?php endif; ?>

        <div class="row g-4">
            <!-- ══ LEFT: Details Form ══ -->
            <div class="col-lg-6 animate-fade-up" style="animation-delay:0.1s;">
                <div class="glass-panel p-4 h-100">
                    <h5 class="fw-bold mb-4"><i class="fas fa-pen-to-square me-2 text-warning"></i> Website Details</h5>
                    <form method="POST" enctype="multipart/form-data" id="editForm">

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control-premium w-100" value="<?= htmlspecialchars($offer['title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Description</label>
                            <textarea name="description" class="form-control-premium w-100" rows="3"><?= htmlspecialchars($offer['description']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Live Website Link <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-primary border-secondary"><i class="fas fa-link"></i></span>
                                <input type="url" name="website_link" class="form-control-premium" value="<?= htmlspecialchars($offer['website_link']) ?>" placeholder="https://..." required>
                            </div>
                            <?php if(!empty($offer['website_link'])): ?>
                            <div class="mt-2">
                                <a href="<?= htmlspecialchars($offer['website_link']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                    <i class="fas fa-external-link-alt me-1"></i> Preview Link
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-secondary small">Price ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-success border-secondary"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" step="0.01" name="price" class="form-control-premium" value="<?= htmlspecialchars($offer['price']) ?>" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small">Discount (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-warning border-secondary"><i class="fas fa-percent"></i></span>
                                    <input type="number" step="1" name="discount" class="form-control-premium" value="<?= htmlspecialchars($offer['discount'] ?? 0) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Coupon Code</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-info border-secondary"><i class="fas fa-ticket"></i></span>
                                <input type="text" name="coupon" class="form-control-premium" value="<?= htmlspecialchars($offer['coupon'] ?? '') ?>" placeholder="Optional">
                            </div>
                        </div>

                        <!-- Admin can manually set status -->
                        <div class="mb-4">
                            <label class="form-label text-secondary small">Approval Status</label>
                            <select name="status" class="form-control-premium w-100" style="appearance:auto;">
                                <option value="pending"  <?= $offer['status']==='pending'  ? 'selected' : '' ?>>⏳ Pending</option>
                                <option value="approved" <?= $offer['status']==='approved' ? 'selected' : '' ?>>✅ Approved</option>
                                <option value="rejected" <?= $offer['status']==='rejected' ? 'selected' : '' ?>>❌ Rejected</option>
                            </select>
                        </div>

                        <button type="submit" name="update" class="btn btn-premium w-100 fs-6">
                            <i class="fas fa-save me-2"></i> Save All Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- ══ RIGHT: Image Gallery Manager ══ -->
            <div class="col-lg-6 animate-fade-up" style="animation-delay:0.2s;">
                <div class="glass-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0"><i class="fas fa-images me-2 text-info"></i> Image Gallery</h5>
                        <span class="badge" style="background:var(--gradient-primary);"><?= count($existingImages) ?> / 5</span>
                    </div>

                    <!-- Existing images grid -->
                    <?php if (!empty($existingImages)): ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:12px;margin-bottom:20px;">
                        <?php foreach ($existingImages as $idx => $img): ?>
                        <div style="position:relative;border-radius:10px;overflow:hidden;aspect-ratio:1;border:2px solid var(--glass-border);">
                            <img src="../<?= htmlspecialchars($img['image_path']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                            <?php if ($idx === 0): ?>
                            <span style="position:absolute;bottom:5px;left:5px;background:var(--accent-primary);color:#fff;font-size:9px;font-weight:700;border-radius:6px;padding:2px 7px;">Main</span>
                            <?php endif; ?>
                            <a href="edit_website.php?id=<?= $id ?>&del_img=<?= $img['id'] ?>&from=websites"
                               onclick="return confirm('Remove this image?')"
                               style="position:absolute;top:5px;right:5px;background:rgba(220,38,38,0.85);color:#fff;border:none;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;text-decoration:none;">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4 text-secondary mb-3" style="border:1px dashed var(--glass-border);border-radius:12px;">
                        <i class="fas fa-image fa-2x mb-2 opacity-50 d-block"></i>
                        <span class="small">No images uploaded yet.</span>
                    </div>
                    <?php endif; ?>

                    <!-- Upload Zone -->
                    <?php if ($slots > 0): ?>
                    <div>
                        <label class="form-label text-secondary small mb-2 d-block">
                            <i class="fas fa-plus-circle me-1 text-success"></i> Add Images
                            <span class="text-secondary">(<?= $slots ?> slot<?= $slots!=1?'s':'' ?> remaining)</span>
                        </label>
                        <div id="uploadZone"
                             onclick="document.getElementById('newImagesInput').click()"
                             style="border:2px dashed var(--accent-primary);border-radius:12px;padding:22px;text-align:center;cursor:pointer;background:rgba(99,102,241,0.05);transition:all 0.3s;">
                            <i class="fas fa-cloud-upload-alt fa-xl mb-2 d-block" style="color:var(--accent-primary);opacity:0.8;"></i>
                            <p class="mb-1 fw-semibold" style="font-size:13px;">Click or Drag &amp; Drop</p>
                            <p class="text-secondary mb-0" style="font-size:11px;">JPG, PNG, WEBP · Max <?= $slots ?></p>
                            <input type="file" name="new_images[]" id="newImagesInput" multiple accept="image/png,image/jpeg,image/jpg,image/webp" form="editForm" style="display:none;">
                        </div>
                        <div id="new-preview-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:10px;margin-top:12px;"></div>
                    </div>
                    <?php else: ?>
                    <div class="p-3 rounded text-center" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);">
                        <i class="fas fa-ban text-danger me-1"></i>
                        <span class="text-danger small fw-semibold">Maximum 5 images reached. Delete one first.</span>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4 p-3 rounded" style="background:rgba(255,255,255,0.04);border:1px solid var(--glass-border);font-size:12px;">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
                        <span class="text-secondary">The <b>Main</b> image is shown first. Images auto-slide on the public page every 2.8 seconds.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
const inp  = document.getElementById('newImagesInput');
const zone = document.getElementById('uploadZone');
const grid = document.getElementById('new-preview-grid');
const MAX  = <?= max(0,$slots) ?>;

if (zone && inp) {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor='var(--accent-secondary)'; });
    zone.addEventListener('dragleave', () => { zone.style.borderColor='var(--accent-primary)'; });
    zone.addEventListener('drop', e => { e.preventDefault(); zone.style.borderColor='var(--accent-primary)'; handleFiles(e.dataTransfer.files); });
    inp.addEventListener('change', () => handleFiles(inp.files));
}

function handleFiles(fileList) {
    const files = Array.from(fileList).slice(0, MAX);
    if (!grid) return;
    grid.innerHTML = '';
    const dt = new DataTransfer();
    files.forEach((file, i) => {
        if (!file.type.match(/image\/(jpeg|png|webp)/)) return;
        dt.items.add(file);
        const r = new FileReader();
        r.onload = e => {
            const th = document.createElement('div');
            th.style.cssText = 'border-radius:8px;overflow:hidden;aspect-ratio:1;border:2px solid var(--accent-primary);';
            th.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
            grid.appendChild(th);
        };
        r.readAsDataURL(file);
    });
    inp.files = dt.files;
}
</script>
</body>
</html>

