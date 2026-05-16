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
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h2 class="display-4 fw-bold text-light mb-4">Get in Touch</h2>
            <p class="lead text-light mb-4">Do you have an issue, a project in mind, or just want to chat? Send us a message and our support team will respond as soon as possible.</p>
            
            <div class="d-flex align-items-center mb-4">
                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-map-marker-alt text-dark fs-5"></i>
                </div>
                <div>
                    <h5 class="text-light mb-1">Office Location</h5>
                    <p class="text-secondary mb-0">123 Tech Park, Bengaluru, India</p>
                </div>
            </div>
            
            <div class="d-flex align-items-center mb-4">
                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-phone-alt text-dark fs-5"></i>
                </div>
                <div>
                    <h5 class="text-light mb-1">Phone Number</h5>
                    <p class="text-secondary mb-0">+91 98765 43210</p>
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-envelope text-dark fs-5"></i>
                </div>
                <div>
                    <h5 class="text-light mb-1">Email Address</h5>
                    <p class="text-secondary mb-0">support@dreamwebsite.com</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="glass-card p-5 border-0">
                <h3 class="fw-bold text-light mb-4">Send Message</h3>
                <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Message sent successfully! We will get back to you soon.');">
                    <div class="mb-3">
                        <label class="form-label text-light">Full Name</label>
                        <input type="text" class="form-control bg-dark text-light border-secondary" placeholder="Your name" required value="<?php echo htmlspecialchars($_SESSION['name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Email Address</label>
                        <input type="email" class="form-control bg-dark text-light border-secondary" placeholder="Your email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Message</label>
                        <textarea class="form-control bg-dark text-light border-secondary" rows="4" placeholder="How can we help?" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-custom-warning w-100 py-2 fs-5">Submit Form <i class="fas fa-paper-plane ms-2"></i></button>
                </form>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
