<?php
require_once __DIR__ . '/../config.php';

// 🔐 DEVELOPER SECURITY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'developer') {
    header('Location: ../login.php');
    exit();
}

$dev_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? "Developer";

$success = "";
$error = "";

if(isset($_POST['submit'])){

    $title    = htmlspecialchars(trim($_POST['title']));
    $desc     = htmlspecialchars(trim($_POST['description']));
    $price    = floatval($_POST['price']);
    $link     = htmlspecialchars(trim($_POST['website_link']));
    $coupon   = htmlspecialchars(trim($_POST['coupon']));
    $discount = intval($_POST['discount']);

    // 📁 Folder create
    if(!is_dir("../uploads/images")) mkdir("../uploads/images", 0777, true);

    // ── Collect uploaded images (up to 5) ──
    $uploaded_paths = [];
    if(!empty($_FILES['images']['name'][0])){
        $files = $_FILES['images'];
        $count = min(count($files['name']), 5);
        for($i = 0; $i < $count; $i++){
            if($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if(!in_array($ext, ['jpg','jpeg','png','webp'])){
                $error = "Only JPG, PNG, WEBP images are allowed!";
                break;
            }
            $newname = uniqid("img_") . "." . $ext;
            $dest = "../uploads/images/" . $newname;
            if(move_uploaded_file($files['tmp_name'][$i], $dest)){
                $uploaded_paths[] = "uploads/images/" . $newname;
            }
        }
    }

    // First image becomes the legacy image_path for backward compat
    $img_path = !empty($uploaded_paths) ? $uploaded_paths[0] : "";

    if(empty($error)){
        $status = "pending";
        $stmt = $conn->prepare("INSERT INTO offers
            (developer_id,title,description,price,website_link,image_path,coupon_code,discount,status)
            VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("issdsssis",
            $dev_id,$title,$desc,$price,$link,$img_path,$coupon,$discount,$status
        );

        if($stmt->execute()){
            $offer_id = $conn->insert_id;

            // Insert all images into offer_images table
            if(!empty($uploaded_paths)){
                $img_stmt = $conn->prepare("INSERT INTO offer_images (offer_id, image_path, sort_order) VALUES (?,?,?)");
                foreach($uploaded_paths as $idx => $path){
                    $img_stmt->bind_param("isi", $offer_id, $path, $idx);
                    $img_stmt->execute();
                }
            }

            $success = "🚀 Website Added Successfully! Waiting for Approval";
        } else {
            $error = "Database Error!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?> - Add Website</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Premium Theme CSS -->
    <link rel="stylesheet" href="../assets/css/theme.css">

    <style>
        /* ── Multi-Image Upload Zone ── */
        .upload-zone {
            border: 2px dashed var(--accent-primary);
            border-radius: 12px;
            padding: 28px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(99,102,241,0.05);
        }
        .upload-zone:hover, .upload-zone.dragover {
            background: rgba(99,102,241,0.12);
            border-color: var(--accent-secondary);
        }
        .upload-zone input[type="file"] {
            display: none;
        }
        /* ── Preview Grid ── */
        #preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 10px;
            margin-top: 14px;
        }
        .preview-thumb {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 1;
            border: 2px solid var(--accent-primary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: fadeInUp 0.3s ease;
        }
        .preview-thumb img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .preview-thumb .badge-num {
            position: absolute; top: 4px; left: 4px;
            background: var(--accent-primary);
            color: #fff; font-size: 10px;
            border-radius: 6px; padding: 2px 6px;
            font-weight: 700;
        }
        .image-count-badge {
            display: inline-flex; align-items: center;
            gap: 6px;
            background: rgba(99,102,241,0.15);
            border: 1px solid var(--accent-primary);
            color: var(--accent-primary);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
        }
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(8px); }
            to   { opacity:1; transform:translateY(0); }
        }
    </style>
</head>
<body data-theme="dark">

<div class="d-flex" style="min-height: calc(100vh - 76px);">
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
            
            <div class="text-secondary small fw-bold mt-3 mb-2 px-3 text-uppercase" style="letter-spacing: 1px;">Content Management</div>
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
                <h2 class="display-6 fw-bold m-0">Add <span class="text-gradient">Website</span></h2>
                <p class="text-secondary mt-2">Submit a complete website to the marketplace.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button id="theme-toggle" class="btn btn-outline-secondary rounded-circle" style="width: 45px; height: 45px;"><i class="fas fa-moon"></i></button>
            </div>
        </div>

        <?php if($error): ?>
            <script>document.addEventListener('DOMContentLoaded', () => { showToast("<?= $error ?>", "danger"); });</script>
        <?php endif; ?>
        
        <?php if($success): ?>
            <script>document.addEventListener('DOMContentLoaded', () => { showToast("<?= $success ?>", "success"); });</script>
        <?php endif; ?>

        <div class="row g-4">
            <!-- ================= FORM SECTION ================= -->
            <div class="col-lg-4 animate-fade-up" style="animation-delay: 0.1s;">
                <div class="glass-panel p-4">
                    <h5 class="mb-4 fw-bold"><i class="fas fa-cloud-upload-alt me-2 text-info"></i> Upload Your Website</h5>

                    <form method="POST" enctype="multipart/form-data" id="addWebsiteForm">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Website Title</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-secondary border-secondary"><i class="fas fa-heading"></i></span>
                                <input type="text" name="title" class="form-control-premium" placeholder="E.g., Corporate Agency" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Description</label>
                            <textarea name="description" class="form-control-premium" rows="3" placeholder="Describe the features..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Live Website Link</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-primary border-secondary"><i class="fas fa-link"></i></span>
                                <input type="url" name="website_link" class="form-control-premium" placeholder="https://..." required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6 mb-3">
                                <label class="form-label text-secondary small">Price ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-success border-secondary"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" step="0.01" name="price" class="form-control-premium" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label text-secondary small">Discount (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-warning border-secondary"><i class="fas fa-percent"></i></span>
                                    <input type="number" step="0.01" name="discount" class="form-control-premium" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Coupon Code</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-info border-secondary"><i class="fas fa-ticket"></i></span>
                                <input type="text" name="coupon" class="form-control-premium" placeholder="Optional">
                            </div>
                        </div>

                        <!-- ── Multi-Image Upload ── -->
                        <div class="mb-4">
                            <label class="form-label text-secondary small d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-images me-1 text-info"></i> Preview Images <span class="text-danger">*</span></span>
                                <span class="text-secondary" style="font-size:11px;">Up to 5 images</span>
                            </label>

                            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('imagesInput').click()">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--accent-primary); opacity:0.8;"></i>
                                <p class="mb-1 fw-semibold" style="font-size:14px;">Click or Drag &amp; Drop Images</p>
                                <p class="text-secondary mb-0" style="font-size:12px;">JPG, PNG, WEBP &bull; Max 5 images</p>
                                <input type="file" name="images[]" id="imagesInput" multiple accept="image/png,image/jpeg,image/jpg,image/webp">
                            </div>

                            <!-- Count badge -->
                            <div id="imgCountBadge" class="image-count-badge" style="display:none;">
                                <i class="fas fa-check-circle"></i>
                                <span id="imgCountText">0 images selected</span>
                            </div>

                            <!-- Preview Grid -->
                            <div id="preview-grid"></div>
                        </div>

                        <button class="btn btn-premium w-100 fs-6" name="submit">
                            <i class="fas fa-paper-plane me-2"></i> Submit for Approval
                        </button>
                    </form>
                </div>
            </div>

            <!-- ================= LIST SECTION ================= -->
            <div class="col-lg-8 animate-fade-up" style="animation-delay: 0.2s;">
                <div class="glass-panel p-0 overflow-hidden">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--glass-border) !important;">
                        <h5 class="fw-bold m-0"><i class="fas fa-globe text-info me-2"></i> Your Active Websites</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-borderless align-middle m-0" style="background: transparent;">
                            <thead style="border-bottom: 1px solid var(--glass-border);">
                                <tr>
                                    <th class="py-3 px-4 text-secondary fw-normal">#</th>
                                    <th class="py-3 px-4 text-secondary fw-normal">Images</th>
                                    <th class="py-3 px-4 text-secondary fw-normal">Title</th>
                                    <th class="py-3 px-4 text-secondary fw-normal">Price</th>
                                    <th class="py-3 px-4 text-secondary fw-normal">Status</th>
                                    <th class="py-3 px-4 text-secondary fw-normal text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rs = $conn->prepare('SELECT o.*, (SELECT COUNT(*) FROM offer_images oi WHERE oi.offer_id=o.id) as img_count FROM offers o WHERE o.developer_id=? AND o.website_link IS NOT NULL AND o.website_link != \'\' ORDER BY o.id DESC');
                                $rs->bind_param('i', $dev_id); $rs->execute();
                                $res = $rs->get_result();
                                
                                if ($res && $res->num_rows > 0) {
                                    $count = 1;
                                    while($row = $res->fetch_assoc()){
                                ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="py-3 px-4"><?php echo $count++; ?></td>
                                    <td class="py-3 px-4">
                                        <?php if($row['img_count'] > 0 || $row['image_path']): ?>
                                            <div class="d-flex align-items-center gap-1">
                                                <?php
                                                // Show first image thumbnail
                                                $thumb = $row['image_path'] ?: '';
                                                if(!$thumb){
                                                    $fi2 = $conn->prepare('SELECT image_path FROM offer_images WHERE offer_id=? ORDER BY sort_order ASC LIMIT 1');
                                                    $fi2->bind_param('i', $row['id']); $fi2->execute();
                                                    $fiRow = $fi2->get_result()->fetch_assoc(); $fi2->close();
                                                    if($fiRow) $thumb = $fiRow['image_path'];
                                                }
                                                ?>
                                                <img src="../<?php echo htmlspecialchars($thumb); ?>" alt="img" class="rounded" style="width: 44px; height: 44px; object-fit: cover;">
                                                <?php if($row['img_count'] > 1): ?>
                                                    <span class="badge" style="background: var(--gradient-primary); font-size:10px;">+<?= $row['img_count'] - 1 ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center;"><i class="fas fa-image text-secondary"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 fw-medium text-start"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="py-3 px-4 text-success fw-bold">$<?php echo htmlspecialchars($row['price']); ?></td>
                                    <td class="py-3 px-4">
                                        <?php
                                        if($row['status'] == "pending") echo "<span class='badge bg-warning bg-opacity-25 text-warning border border-warning'>Pending</span>";
                                        elseif($row['status'] == "approved") echo "<span class='badge bg-success bg-opacity-25 text-success border border-success'>Approved</span>";
                                        else echo "<span class='badge bg-danger bg-opacity-25 text-danger border border-danger'>Rejected</span>";
                                        ?>
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure you want to delete this website?')" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center py-5 text-secondary"><i class="fas fa-folder-open fa-3x mb-3 d-block opacity-50"></i> No websites found. Start by submitting one!</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script>
const imagesInput  = document.getElementById('imagesInput');
const previewGrid  = document.getElementById('preview-grid');
const countBadge   = document.getElementById('imgCountBadge');
const countText    = document.getElementById('imgCountText');
const uploadZone   = document.getElementById('uploadZone');
const MAX_IMAGES   = 5;

// Drag-and-drop support
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

imagesInput.addEventListener('change', () => handleFiles(imagesInput.files));

function handleFiles(fileList) {
    const files = Array.from(fileList).slice(0, MAX_IMAGES);
    previewGrid.innerHTML = '';

    // Create a new DataTransfer to re-assign filtered files
    const dt = new DataTransfer();
    files.forEach((file, idx) => {
        if (!file.type.match(/image\/(jpeg|png|webp)/)) return;
        dt.items.add(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            const thumb = document.createElement('div');
            thumb.className = 'preview-thumb';
            thumb.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${idx+1}">
                <span class="badge-num">${idx+1}</span>
            `;
            previewGrid.appendChild(thumb);
        };
        reader.readAsDataURL(file);
    });

    imagesInput.files = dt.files;

    // Update badge
    if(files.length > 0){
        countBadge.style.display = 'inline-flex';
        countText.textContent = `${files.length} image${files.length > 1 ? 's' : ''} selected`;
    } else {
        countBadge.style.display = 'none';
    }
}
</script>

</body>
</html>

