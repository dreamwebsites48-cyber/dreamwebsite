<?php
require_once __DIR__ . '/../config.php';

// 🔐 SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'developer') {
    header('Location: ../login.php');
    exit();
}

$dev_id = $_SESSION['user_id'];
$error   = "";
$success = "";

// ── Validate offer ID ──
if (!isset($_GET['id']) || !trim($_GET['id'])) {
    die("❌ Invalid Request. No ID Provided.");
}
$id = intval($_GET['id']);

// ── Fetch offer (owned by this developer) ──
$stmt = $conn->prepare("SELECT * FROM offers WHERE id=? AND developer_id=?");
$stmt->bind_param("ii", $id, $dev_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("❌ You do not have permission to edit this item or it does not exist.");
}
$offer = $result->fetch_assoc();

// 🗒 Fetch gallery images — prepared statement
$imgStmt2 = $conn->prepare('SELECT * FROM offer_images WHERE offer_id=? ORDER BY sort_order ASC');
$imgStmt2->bind_param('i', $id); $imgStmt2->execute();
$existingImages = $imgStmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$imgStmt2->close();

// 🗑 DELETE a single gallery image — prepared statements
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
    header('Location: edit.php?id=' . $id . '&msg=img_deleted');
    exit();
}

// ── HANDLE UPDATE ──
if (isset($_POST['update'])) {
    if (!is_dir("../uploads/images")) mkdir("../uploads/images", 0777, true);

    $title        = htmlspecialchars(trim($_POST['title']));
    $desc         = htmlspecialchars(trim($_POST['description']));
    $price        = floatval($_POST['price']);
    $discount     = intval($_POST['discount']);
    $website_link = htmlspecialchars(trim($_POST['website_link']));
    $coupon       = htmlspecialchars(trim($_POST['coupon']));

    if (empty($title) || empty($price)) {
        $error = "Title and Price are required!";
    } else {
        // ── Upload new images (up to fill 5 slots) ──
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
                if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                    $newPaths[] = "uploads/images/" . $newname;
                }
            }
        }

        // Insert new images into offer_images
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

        // Determine legacy image_path (first in gallery)
        $f2 = $conn->prepare('SELECT image_path FROM offer_images WHERE offer_id=? ORDER BY sort_order ASC LIMIT 1');
        $f2->bind_param('i', $id); $f2->execute();
        $firstImg2 = $f2->get_result()->fetch_assoc(); $f2->close();
        $imgPath  = $firstImg2 ? $firstImg2['image_path'] : $offer['image_path'];

        // Update offer row — reset to pending for re-approval
        $upStmt = $conn->prepare("UPDATE offers SET title=?, description=?, price=?, discount=?, website_link=?, coupon_code=?, image_path=?, status='pending' WHERE id=? AND developer_id=?");
        $upStmt->bind_param("ssdisssii", $title, $desc, $price, $discount, $website_link, $coupon, $imgPath, $id, $dev_id);

        if ($upStmt->execute()) {
            $success = '✅ Website updated! Pending admin re-approval.';
            $offer['title'] = $title; $offer['description'] = $desc;
            $offer['price'] = $price; $offer['discount'] = $discount;
            $offer['website_link'] = $website_link; $offer['coupon'] = $coupon;
            $offer['image_path']   = $imgPath;
            $ir = $conn->prepare('SELECT * FROM offer_images WHERE offer_id=? ORDER BY sort_order ASC');
            $ir->bind_param('i', $id); $ir->execute();
            $existingImages = $ir->get_result()->fetch_all(MYSQLI_ASSOC); $ir->close();
        } else {
            $error = "❌ Failed to update. " . $conn->error;
        }
    }
}

// ── Handle ?msg= flash ──
$flash = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?> - Edit Website</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <style>
        /* ── Gallery Grid ── */
        .img-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }
        .img-gallery-thumb {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 1;
            border: 2px solid var(--glass-border);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .img-gallery-thumb img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .img-gallery-thumb .del-btn {
            position: absolute; top: 5px; right: 5px;
            background: rgba(220,38,38,0.85);
            color: #fff; border: none;
            width: 26px; height: 26px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; cursor: pointer;
            transition: background 0.2s;
        }
        .img-gallery-thumb .del-btn:hover { background: #dc2626; }
        .img-gallery-thumb .primary-badge {
            position: absolute; bottom: 5px; left: 5px;
            background: var(--accent-primary);
            color: #fff; font-size: 9px; font-weight: 700;
            border-radius: 6px; padding: 2px 7px;
        }
        /* ── Upload Zone ── */
        .upload-zone {
            border: 2px dashed var(--accent-primary);
            border-radius: 12px;
            padding: 22px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(99,102,241,0.05);
        }
        .upload-zone:hover { background: rgba(99,102,241,0.12); }
        #newImagesInput { display: none; }
        /* ── New preview grid ── */
        #new-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .preview-thumb {
            border-radius: 8px; overflow: hidden; aspect-ratio: 1;
            border: 2px solid var(--accent-primary);
        }
        .preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
        /* ── Slot counter ── */
        .slots-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .slots-ok   { background: rgba(16,185,129,0.15); border: 1px solid #10b981; color: #10b981; }
        .slots-full { background: rgba(239,68,68,0.15);  border: 1px solid #ef4444; color: #ef4444; }
    </style>
</head>
<body data-theme="dark">

<div class="d-flex" style="min-height: 100vh;">
    <!-- Sidebar -->
    <div class="sidebar-premium" style="width: 260px; flex-shrink: 0;">
        <div class="text-center mb-4 pt-3">
            <div style="width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fas fa-code fa-2x text-white"></i>
            </div>
            <h5 class="fw-bold m-0">Dev Panel</h5>
            <span class="badge bg-info bg-opacity-25 text-info rounded-pill mt-1">Pro Developer</span>
        </div>
        <div class="d-flex flex-column gap-1">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-chart-pie w-20px text-center"></i> Dashboard</a>
            <div class="text-secondary small fw-bold mt-3 mb-2 px-3 text-uppercase" style="letter-spacing: 1px;">Content</div>
            <a href="add_website.php" class="sidebar-link active"><i class="fas fa-globe w-20px text-center"></i> Add Website</a>

            <div class="text-secondary small fw-bold mt-3 mb-2 px-3 text-uppercase" style="letter-spacing: 1px;">System</div>
            <a href="../index.php" class="sidebar-link"><i class="fas fa-external-link-alt w-20px text-center"></i> View Site</a>
            <a href="../logout.php" class="sidebar-link text-danger mt-auto"><i class="fas fa-sign-out-alt w-20px text-center"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4 p-md-5" style="background: var(--bg-primary); overflow-y: auto;">

        <div class="d-flex justify-content-between align-items-center mb-5 animate-fade-up">
            <div>
                <h2 class="display-6 fw-bold m-0">Edit <span class="text-gradient">Website</span></h2>
                <p class="text-secondary mt-2">Update details, link, and gallery images.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="add_website.php" class="btn btn-premium-outline rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Back</a>
                <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width:45px;height:45px;"><i class="fas fa-moon"></i></button>
            </div>
        </div>

        <?php if ($flash === 'img_deleted'): ?>
            <script>document.addEventListener('DOMContentLoaded', () => { showToast("🗑 Image removed successfully.", "success"); });</script>
        <?php endif; ?>
        <?php if ($error): ?>
            <script>document.addEventListener('DOMContentLoaded', () => { showToast("<?= addslashes($error) ?>", "danger"); });</script>
        <?php endif; ?>
        <?php if ($success): ?>
            <script>document.addEventListener('DOMContentLoaded', () => { showToast("<?= addslashes($success) ?>", "success"); });</script>
        <?php endif; ?>

        <div class="row g-4">
            <!-- ══ LEFT: Details Form ══ -->
            <div class="col-lg-6 animate-fade-up" style="animation-delay:0.1s;">
                <div class="glass-panel p-4 h-100">
                    <h5 class="fw-bold mb-4"><i class="fas fa-pen-to-square me-2 text-warning"></i> Website Details</h5>
                    <form method="POST" enctype="multipart/form-data" id="editForm">

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Website Title <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-secondary border-secondary"><i class="fas fa-heading"></i></span>
                                <input type="text" name="title" class="form-control-premium" value="<?= htmlspecialchars($offer['title']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Description</label>
                            <textarea name="description" class="form-control-premium" rows="3"><?= htmlspecialchars($offer['description']) ?></textarea>
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
                                    <i class="fas fa-external-link-alt me-1"></i> Preview Current Link
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

                        <div class="mb-4">
                            <label class="form-label text-secondary small">Coupon Code</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-info border-secondary"><i class="fas fa-ticket"></i></span>
                                <input type="text" name="coupon" class="form-control-premium" value="<?= htmlspecialchars($offer['coupon'] ?? '') ?>" placeholder="Optional">
                            </div>
                        </div>

                        <!-- ── Status notice ── -->
                        <div class="p-3 rounded mb-4" style="background:rgba(255,255,255,0.05); border:1px solid var(--glass-border);">
                            <label class="form-label text-secondary small mb-2 d-block">Current Status</label>
                            <?php
                            $badges = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
                            $s = $offer['status'];
                            $bc = $badges[$s] ?? 'secondary';
                            echo "<span class='badge bg-{$bc} bg-opacity-25 text-{$bc} border border-{$bc} px-3 py-2'>".ucfirst($s)."</span>";
                            ?>
                            <small class="d-block mt-2 text-warning"><i class="fas fa-info-circle me-1"></i> Saving changes will reset status to <b>Pending</b> for admin review.</small>
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
                        <?php $slots = 5 - count($existingImages); ?>
                        <span class="slots-badge <?= $slots > 0 ? 'slots-ok' : 'slots-full' ?>">
                            <i class="fas fa-<?= $slots > 0 ? 'check-circle' : 'ban' ?>"></i>
                            <?= count($existingImages) ?>/5 used · <?= max(0,$slots) ?> slot<?= $slots != 1 ? 's' : '' ?> free
                        </span>
                    </div>

                    <!-- Existing images -->
                    <?php if (!empty($existingImages)): ?>
                    <div class="img-gallery-grid">
                        <?php foreach ($existingImages as $idx => $img): ?>
                        <div class="img-gallery-thumb">
                            <img src="../<?= htmlspecialchars($img['image_path']) ?>" alt="Image <?= $idx+1 ?>">
                            <?php if ($idx === 0): ?>
                                <span class="primary-badge">Main</span>
                            <?php endif; ?>
                            <a href="edit.php?id=<?= $id ?>&del_img=<?= $img['id'] ?>"
                               class="del-btn"
                               onclick="return confirm('Remove this image?')"
                               title="Remove">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4 text-secondary" style="border: 1px dashed var(--glass-border); border-radius:12px;">
                        <i class="fas fa-image fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0 small">No images yet. Upload some below.</p>
                    </div>
                    <?php endif; ?>

                    <!-- Add more images (part of the same form) -->
                    <?php if ($slots > 0): ?>
                    <div class="mt-4">
                        <label class="form-label text-secondary small mb-2 d-block">
                            <i class="fas fa-plus-circle me-1 text-success"></i> Add More Images
                            <span class="text-secondary ms-1">(max <?= $slots ?> more)</span>
                        </label>
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('newImagesInput').click()">
                            <i class="fas fa-cloud-upload-alt fa-xl mb-2" style="color:var(--accent-primary); opacity:0.8;"></i>
                            <p class="mb-1 fw-semibold" style="font-size:13px;">Click or Drag &amp; Drop</p>
                            <p class="text-secondary mb-0" style="font-size:11px;">JPG, PNG, WEBP</p>
                            <input type="file" name="new_images[]" id="newImagesInput" multiple accept="image/png,image/jpeg,image/jpg,image/webp" form="editForm">
                        </div>
                        <div id="new-preview-grid"></div>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 p-3 rounded text-center" style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.3);">
                        <i class="fas fa-ban text-danger me-1"></i>
                        <span class="text-danger small fw-semibold">Maximum 5 images reached. Delete one to add more.</span>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4 p-3 rounded" style="background:rgba(255,255,255,0.04); border:1px solid var(--glass-border); font-size:12px;">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
                        <span class="text-secondary">The <b>first (Main)</b> image appears as the card thumbnail. Images play automatically in a slideshow on the public listing.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
const inp   = document.getElementById('newImagesInput');
const zone  = document.getElementById('uploadZone');
const grid  = document.getElementById('new-preview-grid');
const MAX   = <?= max(0, $slots) ?>;

if (zone) {
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
        const reader = new FileReader();
        reader.onload = e => {
            const th = document.createElement('div');
            th.className = 'preview-thumb';
            th.innerHTML = `<img src="${e.target.result}" alt="new ${i+1}">`;
            grid.appendChild(th);
        };
        reader.readAsDataURL(file);
    });
    inp.files = dt.files;
}
</script>
</body>
</html>

