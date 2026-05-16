<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?> - Explore Websites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        /* ── Card (non-clickable) ── */
        .site-card {
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .site-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 24px 60px rgba(99,102,241,0.22);
        }

        /* ── Carousel inside card ── */
        .card-carousel {
            position: relative;
            height: 220px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .card-carousel .carousel-inner,
        .card-carousel .carousel-item { height: 100%; }
        .card-carousel .carousel-item img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .card-carousel .carousel-indicators { margin-bottom: 6px; }
        .card-carousel .carousel-indicators [data-bs-target] {
            width: 6px; height: 6px; border-radius: 50%;
            background-color: rgba(255,255,255,0.6); border: none;
        }
        .card-carousel .carousel-indicators .active {
            background-color: var(--accent-primary, #6366f1);
        }
        /* Image count pill */
        .img-count-pill {
            position: absolute; top: 10px; right: 10px;
            background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);
            color: #fff; font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 20px; z-index: 10;
            pointer-events: none;
        }

        /* ── Lightbox Modal ── */
        #lightboxModal .modal-dialog {
            max-width: 860px;
        }
        #lightboxModal .modal-content {
            background: rgba(10,10,18,0.97);
            border: 1px solid rgba(99,102,241,0.3);
            border-radius: 16px;
            overflow: hidden;
        }
        #lightboxModal .modal-header {
            background: rgba(99,102,241,0.1);
            border-bottom: 1px solid rgba(99,102,241,0.2);
            padding: 14px 20px;
        }
        #lightboxModal .lb-carousel .carousel-item img {
            width: 100%;
            max-height: 520px;
            object-fit: contain;
            background: #000;
            border-radius: 8px;
        }
        #lightboxModal .lb-carousel .carousel-control-prev,
        #lightboxModal .lb-carousel .carousel-control-next {
            width: 44px; height: 44px;
            background: rgba(99,102,241,0.7);
            border-radius: 50%;
            top: 50%; transform: translateY(-50%);
            margin: 0 10px;
        }
        #lightboxModal .lb-thumb-strip {
            display: flex; gap: 8px;
            overflow-x: auto; padding: 12px 16px;
            background: rgba(0,0,0,0.4);
            scrollbar-width: thin;
        }
        #lightboxModal .lb-thumb {
            flex-shrink: 0;
            width: 64px; height: 64px;
            border-radius: 8px; overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer; transition: border-color 0.2s;
        }
        #lightboxModal .lb-thumb.active { border-color: var(--accent-primary, #6366f1); }
        #lightboxModal .lb-thumb img { width: 100%; height: 100%; object-fit: cover; }

        /* ── Card buttons row ── */
        .card-btn-row {
            display: flex; gap: 8px; margin-top: 12px;
        }
        .card-btn-row .btn { flex: 1; font-size: 13px; }
    </style>
</head>
<body data-theme="dark">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-premium px-4 sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="fas fa-globe me-2" style="color: var(--accent-primary);"></i>
            <span class="text-gradient"><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="mainNavbar">
            <ul class="navbar-nav mb-2 mb-lg-0 gap-2">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="websites.php"><i class="fas fa-laptop-code me-1"></i> Websites</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
                <li class="nav-item"><a class="nav-link" href="help.php"><i class="fas fa-question-circle me-1"></i> Help</a></li>
            </ul>
        </div>
        <div class="d-flex align-items-center">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a class="text-decoration-none dropdown-toggle fw-bold d-flex align-items-center me-3" href="#" role="button" data-bs-toggle="dropdown" style="color: var(--text-primary);">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random" alt="Avatar" class="rounded-circle me-2" width="35" height="35">
                        <?= htmlspecialchars($_SESSION['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end glass-panel border-0" style="background: var(--bg-secondary);">
                        <?php 
                        $dash_link = "user/dashboard.php";
                        if($_SESSION['role'] == 'admin') $dash_link = "admin/dashboard.php";
                        if($_SESSION['role'] == 'developer') $dash_link = "developer/dashboard.php";
                        ?>
                        <li><a class="dropdown-item text-secondary" href="<?= $dash_link ?>"><i class="fas fa-user-circle me-2"></i> Dashboard</a></li>
                        <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-premium-outline rounded-pill px-4 me-2">Login</a>
                <a href="register.php" class="btn btn-premium rounded-pill px-4 fw-bold">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HEADER -->
<section class="py-5 text-center position-relative" style="background: var(--bg-secondary);">
    <div class="container animate-fade-up">
        <h1 class="fw-bold display-5 mb-3">Explore <span class="text-gradient">Premium Websites</span></h1>
        <p class="text-secondary lead" style="max-width: 600px; margin: 0 auto;">Discover high-quality, fully built websites and themes ready to launch.</p>
    </div>
</section>

<!-- SEARCH & FILTER -->
<div class="container mt-5">
    <div class="glass-panel p-4 mb-5 animate-fade-up" style="animation-delay: 0.1s;">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute text-secondary" style="top: 14px; left: 15px;"></i>
                    <input type="text" name="search" class="form-control-premium w-100" style="padding-left: 45px;" placeholder="Search websites..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="position-relative">
                    <i class="fas fa-tag position-absolute text-secondary" style="top: 14px; left: 15px;"></i>
                    <select name="price" class="form-control-premium w-100" style="padding-left: 45px; appearance: auto;">
                        <option value="">All Prices</option>
                        <option value="1000" <?= (isset($_GET['price']) && $_GET['price'] == '1000') ? 'selected' : '' ?>>Below $1000</option>
                        <option value="5000" <?= (isset($_GET['price']) && $_GET['price'] == '5000') ? 'selected' : '' ?>>Below $5000</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <button class="btn btn-premium w-100" style="height: 46px;">Search <i class="fas fa-arrow-right ms-2"></i></button>
            </div>
        </form>
    </div>

    <!-- ── CARD GRID ── -->
    <div class="row g-4 mb-5">
    <?php
    $search = $_GET['search'] ?? '';
    $price  = $_GET['price']  ?? '';
    $query  = "SELECT * FROM offers WHERE status='approved'";
    if(!empty($search)) $query .= " AND title LIKE '%" . $conn->real_escape_string($search) . "%'";
    if(!empty($price))  $query .= " AND price <= " . (int)$price;
    $query .= " ORDER BY id DESC";
    $res = $conn->query($query);

    // Collect all offer data + images first (needed for lightbox JS)
    $offers = [];
    if($res && $res->num_rows > 0){
        while($row = $res->fetch_assoc()){
            $imgRes = $conn->query("SELECT image_path FROM offer_images WHERE offer_id={$row['id']} ORDER BY sort_order ASC");
            $images = [];
            if($imgRes && $imgRes->num_rows > 0)
                while($img = $imgRes->fetch_assoc()) $images[] = $img['image_path'];
            if(empty($images) && !empty($row['image_path'])) $images[] = $row['image_path'];
            $row['_images'] = $images;
            $offers[] = $row;
        }
    }

    if(!empty($offers)):
        $delay = 0.2;
        foreach($offers as $idx => $row):
            $images     = $row['_images'];
            $carouselId = "car-{$idx}";
            $hasLink    = !empty($row['website_link']);
    ?>
        <div class="col-md-4 animate-fade-up" style="animation-delay: <?= $delay ?>s;">
            <div class="glass-panel site-card">

                <!-- ── Auto-play Carousel (images only, NOT a link) ── -->
                <?php if(!empty($images)): ?>
                <div class="card-carousel position-relative">
                    <?php if(count($images) > 1): ?>
                        <span class="img-count-pill"><i class="fas fa-images me-1"></i><?= count($images) ?></span>
                    <?php endif; ?>

                    <div id="<?= $carouselId ?>" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="2800">
                        <?php if(count($images) > 1): ?>
                        <div class="carousel-indicators">
                            <?php foreach($images as $ii => $im): ?>
                            <button type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide-to="<?= $ii ?>" <?= $ii===0 ? 'class="active" aria-current="true"' : '' ?>></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="carousel-inner h-100">
                            <?php foreach($images as $ii => $imgPath): ?>
                            <div class="carousel-item <?= $ii===0 ? 'active' : '' ?> h-100">
                                <img src="<?= htmlspecialchars($imgPath) ?>" alt="Preview <?= $ii+1 ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if(count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div style="height: 220px; background: linear-gradient(45deg, var(--bg-tertiary), var(--bg-secondary)); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image text-secondary" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <?php endif; ?>

                <!-- ── Card Body ── -->
                <div class="p-4 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0 text-truncate" style="max-width: 70%;"><?= htmlspecialchars($row['title']) ?></h5>
                        <span class="badge" style="background: var(--gradient-success); font-size: 0.85rem;">$<?= number_format($row['price'], 2) ?></span>
                    </div>
                    <p class="text-secondary small flex-grow-1"><?= substr(htmlspecialchars($row['description']), 0, 110) ?>...</p>

                    <!-- ── Action buttons ── -->
                    <div class="card-btn-row">
                        <!-- View Details → opens lightbox with all images -->
                        <?php if(!empty($images)): ?>
                        <button class="btn btn-premium-outline"
                                onclick="openLightbox(<?= $idx ?>)"
                                title="View all images">
                            <i class="fas fa-expand me-1"></i> View Details
                        </button>
                        <?php endif; ?>

                        <!-- Visit Website → opens external link -->
                        <?php if($hasLink): ?>
                        <a href="<?= htmlspecialchars($row['website_link']) ?>" target="_blank" rel="noopener noreferrer"
                           class="btn btn-premium">
                            <i class="fas fa-external-link-alt me-1"></i> Visit Site
                        </a>
                        <?php else: ?>
                        <button class="btn btn-premium" disabled style="opacity:0.45;">
                            <i class="fas fa-ban me-1"></i> No Link
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
        $delay += 0.1;
        endforeach;
    else:
    ?>
        <div class="col-12 text-center py-5 animate-fade-up">
            <i class="fas fa-box-open text-secondary mb-3 opacity-50" style="font-size: 4rem;"></i>
            <h4 class="fw-bold mb-2">No websites found</h4>
            <p class="text-secondary">Try adjusting your search criteria.</p>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- ══════════════ LIGHTBOX MODAL ══════════════ -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold text-gradient" id="lbTitle">Website Preview</h6>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <span class="badge" style="background:var(--gradient-primary);" id="lbImgCount"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <!-- Main carousel -->
            <div id="lbCarousel" class="carousel slide lb-carousel" data-bs-ride="false">
                <div class="carousel-inner" id="lbCarouselInner"></div>
                <button class="carousel-control-prev" type="button" data-bs-target="#lbCarousel" data-bs-slide="prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#lbCarousel" data-bs-slide="next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Thumbnail strip -->
            <div class="lb-thumb-strip" id="lbThumbs"></div>

            <!-- Footer with link -->
            <div class="modal-footer border-0 justify-content-between" style="background: rgba(0,0,0,0.3);">
                <span class="text-secondary small" id="lbDesc"></span>
                <a href="#" id="lbVisitBtn" target="_blank" rel="noopener noreferrer" class="btn btn-premium rounded-pill px-4">
                    <i class="fas fa-external-link-alt me-2"></i>Visit Website
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="text-center py-4 mt-auto" style="border-top: 1px solid var(--glass-border); background: var(--bg-secondary);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-md-start mb-3 mb-md-0">
                <span class="fs-5 fw-bold" style="font-family: var(--font-heading);"><i class="fas fa-globe" style="color: var(--accent-primary);"></i> <?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?></span>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <p class="mb-0 text-secondary">&copy; <?= date("Y") ?> <?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?>. All rights reserved.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button id="theme-toggle" class="btn btn-sm btn-outline-secondary rounded-circle me-3" title="Toggle Theme"><i class="fas fa-moon"></i></button>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme.js"></script>
<script>
// ── Offer data for lightbox ──
const offersData = <?php
    $jsData = [];
    foreach($offers as $o){
        $jsData[] = [
            'title'   => $o['title'],
            'desc'    => substr($o['description'], 0, 120) . '...',
            'link'    => $o['website_link'] ?? '',
            'images'  => $o['_images'],
        ];
    }
    echo json_encode($jsData, JSON_HEX_TAG | JSON_HEX_QUOT);
?>;

let lbModal;

function openLightbox(idx) {
    const d = offersData[idx];
    if (!d) return;

    // Set title & description
    document.getElementById('lbTitle').textContent    = d.title;
    document.getElementById('lbDesc').textContent     = d.desc;
    document.getElementById('lbImgCount').textContent = d.images.length + ' photo' + (d.images.length !== 1 ? 's' : '');

    // Visit link
    const visitBtn = document.getElementById('lbVisitBtn');
    if (d.link) {
        visitBtn.href = d.link;
        visitBtn.style.display = '';
    } else {
        visitBtn.style.display = 'none';
    }

    // Build carousel slides
    const inner = document.getElementById('lbCarouselInner');
    inner.innerHTML = '';
    d.images.forEach((src, i) => {
        const div = document.createElement('div');
        div.className = 'carousel-item' + (i === 0 ? ' active' : '');
        div.innerHTML = `<img src="${src}" alt="Image ${i+1}" class="d-block">`;
        inner.appendChild(div);
    });

    // Build thumbnail strip
    const thumbs = document.getElementById('lbThumbs');
    thumbs.innerHTML = '';
    d.images.forEach((src, i) => {
        const th = document.createElement('div');
        th.className = 'lb-thumb' + (i === 0 ? ' active' : '');
        th.innerHTML = `<img src="${src}" alt="thumb ${i+1}">`;
        th.addEventListener('click', () => {
            const lbCar = bootstrap.Carousel.getInstance(document.getElementById('lbCarousel'));
            lbCar.to(i);
        });
        thumbs.appendChild(th);
    });

    // Sync thumbnails on slide change
    const carEl = document.getElementById('lbCarousel');
    carEl.removeEventListener('slid.bs.carousel', syncThumbs); // prevent duplicates
    carEl.addEventListener('slid.bs.carousel', syncThumbs);

    // Reset carousel to first slide
    const existingCar = bootstrap.Carousel.getInstance(carEl);
    if (existingCar) existingCar.to(0);

    // Show modal
    if (!lbModal) lbModal = new bootstrap.Modal(document.getElementById('lightboxModal'));
    lbModal.show();
}

function syncThumbs(e) {
    const thumbEls = document.querySelectorAll('#lbThumbs .lb-thumb');
    thumbEls.forEach((t, i) => t.classList.toggle('active', i === e.to));
    // Scroll active thumb into view
    thumbEls[e.to]?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
}
</script>
</body>
</html>