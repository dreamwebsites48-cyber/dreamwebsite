<?php
session_start();
require_once __DIR__ . '/../config.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($role == 'developer') {
        header("Location: developer/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?> - Premium Web Platform</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Premium Theme CSS -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        .website-card-link { text-decoration: none; color: inherit; display: block; }
        .website-card-link:hover .glass-panel { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(99,102,241,0.25); border-color: var(--accent-primary); }
        .glass-panel { transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease; }
        .card-carousel { position: relative; height: 200px; overflow: hidden; border-radius: 12px 12px 0 0; }
        .card-carousel .carousel-inner, .card-carousel .carousel-item { height: 100%; }
        .card-carousel .carousel-item img { width: 100%; height: 100%; object-fit: cover; }
        .card-carousel .carousel-indicators { margin-bottom: 6px; }
        .card-carousel .carousel-indicators [data-bs-target] { width: 6px; height: 6px; border-radius: 50%; background-color: rgba(255,255,255,0.7); border: none; }
        .card-carousel .carousel-indicators .active { background-color: var(--accent-primary, #6366f1); }
        .img-count-pill { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.55); backdrop-filter: blur(6px); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; z-index: 10; pointer-events: none; }
        .visit-overlay { position: absolute; bottom: 10px; left: 10px; background: rgba(99,102,241,0.85); color: #fff; font-size: 11px; font-weight: 600; padding: 4px 12px; border-radius: 20px; z-index: 10; pointer-events: none; display: flex; align-items: center; gap: 5px; opacity: 0; transition: opacity 0.25s ease; }
        .website-card-link:hover .visit-overlay { opacity: 1; }
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
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="websites.php"><i class="fas fa-laptop-code me-1"></i> Websites</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
                    <li class="nav-item"><a class="nav-link" href="help.php"><i class="fas fa-question-circle me-1"></i> Help</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="text-decoration-none dropdown-toggle fw-bold d-flex align-items-center me-3" href="#" role="button" data-bs-toggle="dropdown" style="color: var(--text-primary);">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random" alt="Avatar" class="rounded-circle me-2" width="35" height="35">
                            <?= htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end glass-panel border-0" style="background: var(--bg-secondary);">
                            <?php
                            $dash_link = "user/dashboard.php";
                            if ($_SESSION['role'] == 'admin') $dash_link = "admin/dashboard.php";
                            if ($_SESSION['role'] == 'developer') $dash_link = "developer/dashboard.php";
                            ?>
                            <li><a class="dropdown-item text-secondary hover-primary" href="<?= $dash_link ?>"><i class="fas fa-user-circle me-2"></i> Dashboard</a></li>
                            <li>
                                <hr class="dropdown-divider border-secondary opacity-25">
                            </li>
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

    <!-- HERO SECTION -->
    <section class="hero-premium text-center">
        <div class="hero-bg-glow"></div>
        <div class="container relative z-10">
            <h1 class="display-4 fw-bolder mb-4 animate-fade-up">Build Your <span class="text-gradient">Dream Website</span></h1>
            <p class="lead mb-5 text-secondary animate-fade-up" style="max-width: 600px; margin: 0 auto; animation-delay: 0.1s;">
                Buy, Sell, and Customize premium websites. Connect with top developers to bring your vision to life.
            </p>
            <div class="d-flex justify-content-center gap-3 animate-fade-up" style="animation-delay: 0.2s;">
                <a href="websites.php" class="btn btn-premium btn-lg px-5"><i class="fas fa-rocket"></i> Explore Websites</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-premium-outline btn-lg px-5">Join Now</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ROLES -->
    <section class="py-5" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="text-center mb-5 animate-fade-up">
                <h2 class="fw-bold">Platform Roles</h2>
                <p class="text-secondary">A unified ecosystem for everyone</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="glass-panel p-5 text-center h-100">
                        <div class="mb-4">
                            <i class="fas fa-user text-gradient" style="font-size: 3rem;"></i>
                        </div>
                        <h4>User</h4>
                        <p class="text-secondary">Browse websites, contact developers, and buy safely with our secure platform.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="glass-panel p-5 text-center h-100 position-relative overflow-hidden">
                        <div class="position-absolute top-0 end-0 p-2"><span class="badge" style="background: var(--gradient-primary);">Hot</span></div>
                        <div class="mb-4">
                            <i class="fas fa-code text-gradient" style="font-size: 3rem;"></i>
                        </div>
                        <h4>Developer</h4>
                        <p class="text-secondary">Upload your projects, manage client requests, and earn money.</p>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="register.php" class="btn btn-sm btn-premium mt-3">Become a Developer</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="glass-panel p-5 text-center h-100">
                        <div class="mb-4">
                            <i class="fas fa-shield-alt text-gradient" style="font-size: 3rem;"></i>
                        </div>
                        <h4>Admin</h4>
                        <p class="text-secondary">Complete control over the platform, ensuring quality and security for everyone.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED WEBSITES -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5 animate-fade-up">
                <div>
                    <h2 class="fw-bold m-0">Featured Websites</h2>
                    <p class="text-secondary m-0 mt-2">Discover our top-rated projects</p>
                </div>
                <a href="websites.php" class="btn btn-premium-outline">View All <i class="fas fa-arrow-right ms-2"></i></a>
            </div>

            <div class="row g-4">
                <?php
                $result = $conn->query("SELECT * FROM offers WHERE status='approved' ORDER BY id DESC LIMIT 6");
                if ($result && $result->num_rows > 0):
                    $delay = 0.1;
                    $cidx  = 0;
                    while ($row = $result->fetch_assoc()):
                        $imgRes = $conn->query("SELECT image_path FROM offer_images WHERE offer_id={$row['id']} ORDER BY sort_order ASC");
                        $images = [];
                        if($imgRes && $imgRes->num_rows > 0){ while($img=$imgRes->fetch_assoc()) $images[]=$img['image_path']; }
                        if(empty($images) && !empty($row['image_path'])) $images[] = $row['image_path'];
                        $cid     = 'uc-' . $cidx++;
                        $hasLink = !empty($row['website_link']);
                        $cardTag = $hasLink ? 'a' : 'div';
                        $attrs   = $hasLink ? 'href="'.htmlspecialchars($row['website_link']).'" target="_blank" rel="noopener noreferrer" class="website-card-link"' : 'class="website-card-link"';
                ?>
                        <div class="col-md-4 animate-fade-up" style="animation-delay: <?= $delay ?>s;">
                            <<?= $cardTag ?> <?= $attrs ?>>
                                <div class="glass-panel h-100 overflow-hidden d-flex flex-column">
                                    <?php if(!empty($images)): ?>
                                    <div class="card-carousel position-relative">
                                        <?php if(count($images)>1): ?><span class="img-count-pill"><i class="fas fa-images me-1"></i><?=count($images)?></span><?php endif; ?>
                                        <div class="visit-overlay"><i class="fas fa-external-link-alt"></i> Visit Site</div>
                                        <div id="<?=$cid?>" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="2800">
                                            <?php if(count($images)>1): ?>
                                            <div class="carousel-indicators"><?php foreach($images as $ii=>$im): ?><button type="button" data-bs-target="#<?=$cid?>" data-bs-slide-to="<?=$ii?>" <?=$ii===0?'class="active" aria-current="true"':''?>></button><?php endforeach; ?></div>
                                            <?php endif; ?>
                                            <div class="carousel-inner h-100"><?php foreach($images as $ii=>$ip): ?><div class="carousel-item <?=$ii===0?'active':''?> h-100"><img src="<?=htmlspecialchars($ip)?>" alt="Preview"></div><?php endforeach; ?></div>
                                            <?php if(count($images)>1): ?>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#<?=$cid?>" data-bs-slide="prev" onclick="event.preventDefault();event.stopPropagation();"><span class="carousel-control-prev-icon"></span></button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#<?=$cid?>" data-bs-slide="next" onclick="event.preventDefault();event.stopPropagation();"><span class="carousel-control-next-icon"></span></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="position-relative" style="height:200px; background:linear-gradient(45deg,var(--bg-tertiary),var(--bg-secondary)); display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-image text-secondary" style="font-size:3rem;opacity:0.5;"></i>
                                        <?php if($hasLink): ?><div class="visit-overlay"><i class="fas fa-external-link-alt"></i> Visit Site</div><?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="p-4 d-flex flex-column flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="fw-bold mb-0 text-truncate" style="max-width:70%;"><?=htmlspecialchars($row['title'])?></h5>
                                            <span class="badge" style="background:var(--gradient-success);">$<?=htmlspecialchars($row['price'])?></span>
                                        </div>
                                        <p class="text-secondary small flex-grow-1"><?=substr(htmlspecialchars($row['description']),0,100)?>...</p>
                                        <?php if($hasLink): ?>
                                        <div class="btn btn-premium w-100 mt-3" style="pointer-events:none;"><i class="fas fa-external-link-alt me-2"></i>View Details</div>
                                        <?php else: ?>
                                        <div class="btn mt-3 w-100" style="pointer-events:none;opacity:0.45;border:1px solid var(--glass-border);"><i class="fas fa-ban me-2"></i>No Link Available</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </<?= $cardTag ?>>
                        </div>
                    <?php
                        $delay += 0.1;
                    endwhile;
                else:
                    ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open text-secondary mb-3" style="font-size: 3rem;"></i>
                        <p class="text-secondary">No featured websites available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

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
                    <button id="theme-toggle" class="btn btn-sm btn-outline-secondary rounded-circle me-3" title="Toggle Theme">
                        <i class="fas fa-moon"></i>
                    </button>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-secondary"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>

</html>