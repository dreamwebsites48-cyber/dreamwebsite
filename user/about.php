<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config.php';

// Role Security
if(!isset($_SESSION['role']) || $_SESSION['role'] != "user"){
    header("Location: " . $base_url . "login.php");
    exit();
}

include "../includes/header.php";
?>

<div class="d-flex" style="min-height: calc(100vh - 76px);">
    <?php include "../includes/user_sidebar.php"; ?>
    <div class="flex-grow-1 p-4 p-md-5 bg-primary" style="background: var(--bg-primary) !important; overflow-y: auto;">
        <div class="container-fluid max-w-1200 mx-auto">
    <!-- INTRO -->
    <div class="glass-card text-center mb-5">
        <h2 class="display-5 fw-bold text-light mb-3"><i class="fas fa-rocket text-warning"></i> About Dream Website</h2>
        <p class="lead text-light">Dream Website ek modern platform hai jaha users ready-made websites buy karte hai aur developers apne projects sell karte hai.</p>
        <p class="text-light">Admin system ensure karta hai ki sirf quality websites hi users tak pahunche.</p>
    </div>

    <!-- HOW IT WORKS -->
    <div class="glass-card mb-5">
        <h3 class="fw-bold text-center mb-4"><i class="fas fa-cogs text-warning"></i> How It Works</h3>
        <div class="row text-center justify-content-center">
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <i class="fas fa-cloud-upload-alt fa-3x text-warning mb-2"></i>
                <p>1. Developer Uploads</p>
            </div>
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <i class="fas fa-user-shield fa-3x text-warning mb-2"></i>
                <p>2. Admin Approves</p>
            </div>
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <i class="fas fa-globe bg-transparent fa-3x text-warning mb-2"></i>
                <p>3. Website Live</p>
            </div>
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <i class="fas fa-envelope fa-3x text-warning mb-2"></i>
                <p>4. User Connects</p>
            </div>
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <i class="fas fa-handshake fa-3x text-warning mb-2"></i>
                <p>5. Deal Finalized</p>
            </div>
        </div>
    </div>

    <!-- FEATURES & DEVELOPERS -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="glass-card h-100">
                <h3 class="fw-bold text-center mb-4"><i class="fas fa-fire text-warning"></i> Key Features</h3>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-light border-secondary"><i class="fas fa-check text-warning me-2"></i> Buy & Sell Platform</li>
                    <li class="list-group-item bg-transparent text-light border-secondary"><i class="fas fa-check text-warning me-2"></i> 100% Secure System</li>
                    <li class="list-group-item bg-transparent text-light border-secondary"><i class="fas fa-check text-warning me-2"></i> Strict Admin Quality Control</li>
                    <li class="list-group-item bg-transparent text-light border-secondary"><i class="fas fa-check text-warning me-2"></i> Integrated Chat System</li>
                    <li class="list-group-item bg-transparent text-light border-secondary"><i class="fas fa-check text-warning me-2"></i> Dedicated Developer Panel</li>
                    <li class="list-group-item bg-transparent text-light border-secondary"><i class="fas fa-check text-warning me-2"></i> Mobile Responsive Design</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card h-100">
                <h3 class="fw-bold text-center mb-4"><i class="fas fa-users text-warning"></i> Our Top Developers</h3>
                <div class="row text-center mt-3">
                    <div class="col-4">
                        <img src="https://ui-avatars.com/api/?name=Dev+1&background=random" class="rounded-circle mb-2 border border-warning border-3" width="70">
                        <h6 class="mb-0">Alex D.</h6>
                        <small class="text-secondary">Full Stack</small>
                    </div>
                    <div class="col-4">
                        <img src="https://ui-avatars.com/api/?name=Dev+2&background=random" class="rounded-circle mb-2 border border-warning border-3" width="70">
                        <h6 class="mb-0">Sarah K.</h6>
                        <small class="text-secondary">Frontend UI</small>
                    </div>
                    <div class="col-4">
                        <img src="https://ui-avatars.com/api/?name=Dev+3&background=random" class="rounded-circle mb-2 border border-warning border-3" width="70">
                        <h6 class="mb-0">John M.</h6>
                        <small class="text-secondary">Backend</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="glass-card text-center">
        <h3 class="fw-bold mb-3">Ready to Start?</h3>
        <p class="mb-4">Browse our collection of curated templates and customized websites right now.</p>
        <a href="websites.php" class="btn btn-custom-warning btn-lg px-5 rounded-pill"><i class="fas fa-compass me-2"></i> Explore Websites</a>
    </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
