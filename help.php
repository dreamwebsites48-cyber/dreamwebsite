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
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?> - Help & Support</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Premium Theme CSS -->
    <link rel="stylesheet" href="assets/css/theme.css">
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
                <li class="nav-item"><a class="nav-link" href="websites.php"><i class="fas fa-laptop-code me-1"></i> Websites</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
                <li class="nav-item"><a class="nav-link active" href="help.php"><i class="fas fa-question-circle me-1"></i> Help</a></li>
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
                        <li><a class="dropdown-item text-secondary hover-primary" href="<?= $dash_link ?>"><i class="fas fa-user-circle me-2"></i> Dashboard</a></li>
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
        <h1 class="fw-bold display-5 mb-3">Help & <span class="text-gradient">Support</span></h1>
        <p class="text-secondary lead" style="max-width: 700px; margin: 0 auto;">Find answers to common questions or reach out to our team directly.</p>
    </div>
</section>

<div class="container mt-5 mb-5">
    <div class="row g-5">
        <!-- FAQ Section -->
        <div class="col-md-7 animate-fade-up" style="animation-delay: 0.1s;">
            <h3 class="fw-bold mb-4">Frequently Asked Questions</h3>
            <div class="accordion" id="faqAccordion">
                
                <div class="glass-panel mb-3 overflow-hidden">
                    <div class="accordion-item bg-transparent border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I purchase a website?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Once you find a website you like, you can contact the developer directly through the 'View Details' page to arrange the purchase and transfer details securely.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-panel mb-3 overflow-hidden">
                    <div class="accordion-item bg-transparent border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How do I become a Developer?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Sign up and select 'Developer' as your role. Your account will be put into a 'Pending' state until an Admin reviews and approves your application. Once approved, you can upload offers.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-panel mb-3 overflow-hidden">
                    <div class="accordion-item bg-transparent border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Are the websites fully built?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Yes! Most websites listed on our platform are fully functional. Developers often provide a live link so you can interact with the website before making a decision.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-md-5 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="glass-panel p-4">
                <h4 class="fw-bold mb-4">Contact Us</h4>
                <form>
                    <div class="mb-3">
                        <input type="text" class="form-control-premium w-100" placeholder="Your Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control-premium w-100" placeholder="Your Email" required>
                    </div>
                    <div class="mb-4">
                        <textarea class="form-control-premium w-100" rows="5" placeholder="How can we help you?" required></textarea>
                    </div>
                    <button type="button" class="btn btn-premium w-100" onclick="alert('Message Sent Successfully!')">Send Message <i class="fas fa-paper-plane ms-2"></i></button>
                </form>
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
                <button id="theme-toggle" class="btn btn-sm btn-outline-secondary rounded-circle me-3" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme.js"></script>
</body>
</html>