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
        <h2 class="display-5 fw-bold text-light mb-3"><i class="fas fa-lightbulb text-warning"></i> Help & Support Center</h2>
        <p class="lead text-light mb-0">Everything you need to know to get the best out of Dream Website.</p>
    </div>

    <!-- COMMON ISSUES -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <i class="fas fa-lock fa-3x text-info mb-3"></i>
                <h4 class="fw-bold text-light">Login Problem</h4>
                <p class="text-secondary">If your email or password is incorrect or you forgot it, please use the reset option to gain access again.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <i class="fas fa-upload fa-3x text-warning mb-3"></i>
                <h4 class="fw-bold text-light">Upload Issue</h4>
                <p class="text-secondary">Make sure the uploads folder exists and proper permissions are allowed when dealing with files.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <i class="fas fa-bug fa-3x text-danger mb-3"></i>
                <h4 class="fw-bold text-light">Website Error</h4>
                <p class="text-secondary">Ensure there is an active database connection and the configuration file is correctly set up.</p>
            </div>
        </div>
    </div>

    <!-- ADMIN CONTACT -->
    <div class="glass-card text-center mb-5 p-5">
        <i class="fas fa-crown fa-4x text-warning mb-3"></i>
        <h3 class="fw-bold text-light">Contact Admin</h3>
        <p class="text-light mb-4 text-center mx-auto" style="max-width: 600px;">The admin handles all quality control, approves or rejects websites, and manages overall system stability.</p>
        <a href="mailto:admin@dreamwebsite.com" class="btn btn-custom-outline btn-lg rounded-pill"><i class="fas fa-envelope"></i> admin@dreamwebsite.com</a>
    </div>

    <!-- DEVELOPERS -->
    <div class="glass-card mb-5">
        <h3 class="fw-bold text-center mb-4 text-light"><i class="fas fa-code text-warning"></i> Reach Out to Developers</h3>
        <div class="row text-center g-4 mt-2">
            <!-- Dev 1 -->
            <div class="col-md-4 col-sm-6">
                <div class="p-3">
                    <img src="https://randomuser.me/api/portraits/men/11.jpg" class="rounded-circle mb-3 border border-warning border-3" width="90" height="90">
                    <h5 class="fw-bold text-light mb-1">Abdul Sahil</h5>
                    <p class="text-secondary mb-1"><small>Frontend Developer &bull; 2 Yrs Exp</small></p>
                    <a href="mailto:sahil@dev.com" class="text-warning text-decoration-none"><small>sahil@dev.com</small></a>
                </div>
            </div>
            <!-- Dev 2 -->
            <div class="col-md-4 col-sm-6">
                <div class="p-3">
                    <img src="https://randomuser.me/api/portraits/men/22.jpg" class="rounded-circle mb-3 border border-warning border-3" width="90" height="90">
                    <h5 class="fw-bold text-light mb-1">Rahul Verma</h5>
                    <p class="text-secondary mb-1"><small>Backend Developer &bull; 3 Yrs Exp</small></p>
                    <a href="mailto:rahul@dev.com" class="text-warning text-decoration-none"><small>rahul@dev.com</small></a>
                </div>
            </div>
            <!-- Dev 3 -->
            <div class="col-md-4 col-sm-6">
                <div class="p-3">
                    <img src="https://randomuser.me/api/portraits/men/33.jpg" class="rounded-circle mb-3 border border-warning border-3" width="90" height="90">
                    <h5 class="fw-bold text-light mb-1">Aman Khan</h5>
                    <p class="text-secondary mb-1"><small>Full Stack &bull; 4 Yrs Exp</small></p>
                    <a href="mailto:aman@dev.com" class="text-warning text-decoration-none"><small>aman@dev.com</small></a>
                </div>
            </div>
            <!-- Dev 4 -->
            <div class="col-md-4 col-sm-6">
                <div class="p-3">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" class="rounded-circle mb-3 border border-warning border-3" width="90" height="90">
                    <h5 class="fw-bold text-light mb-1">Neha Sharma</h5>
                    <p class="text-secondary mb-1"><small>UI/UX Designer &bull; 3 Yrs Exp</small></p>
                    <a href="mailto:neha@dev.com" class="text-warning text-decoration-none"><small>neha@dev.com</small></a>
                </div>
            </div>
            <!-- Dev 5 -->
            <div class="col-md-4 col-sm-6 justify-content-center m-auto">
                <div class="p-3">
                    <img src="https://randomuser.me/api/portraits/men/55.jpg" class="rounded-circle mb-3 border border-warning border-3" width="90" height="90">
                    <h5 class="fw-bold text-light mb-1">Arjun Das</h5>
                    <p class="text-secondary mb-1"><small>Security Expert &bull; 5 Yrs Exp</small></p>
                    <a href="mailto:arjun@dev.com" class="text-warning text-decoration-none"><small>arjun@dev.com</small></a>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
