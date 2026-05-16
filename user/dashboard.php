<?php
session_start();
include "../config.php";

// Role Security
if(!isset($_SESSION['role']) || $_SESSION['role'] != "user"){
    header("Location: " . $base_url . "login.php");
    exit();
}

include "../includes/header.php";
include "../includes/navbar.php";
?>

<div class="container py-5 mt-4">
    <!-- Welcome Header -->
    <div class="glass-panel text-center mb-5 p-5 animate-fade-up">
        <h2 class="display-5 fw-bold mb-3">Welcome, <span class="text-gradient"><?= htmlspecialchars($_SESSION['name']); ?></span>! 👋</h2>
        <p class="lead text-secondary mb-0">Your user dashboard is ready. Explore top-notch websites or get in touch with our top developers.</p>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4 justify-content-center">
        <!-- Browse Websites Card -->
        <div class="col-md-4 animate-fade-up" style="animation-delay: 0.1s;">
            <div class="glass-panel text-center h-100 d-flex flex-column p-4 position-relative overflow-hidden">
                <div class="position-absolute" style="top: -20px; right: -20px; font-size: 8rem; color: var(--accent-primary); opacity: 0.05;">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <i class="fas fa-laptop-code fa-3x text-gradient mb-4 mt-2"></i>
                <h4 class="fw-bold mb-3">Browse Websites</h4>
                <p class="text-secondary mb-4 flex-grow-1">Discover completely built websites ready for deployment. Filter by category, price, and features.</p>
                <a href="<?= $base_url; ?>user/websites.php" class="btn btn-premium w-100">Explore Websites</a>
            </div>
        </div>
        
        <!-- Contact Developers Card -->
        <div class="col-md-4 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="glass-panel text-center h-100 d-flex flex-column p-4 position-relative overflow-hidden">
                <div class="position-absolute" style="top: -20px; right: -20px; font-size: 8rem; color: var(--accent-secondary); opacity: 0.05;">
                    <i class="fas fa-users"></i>
                </div>
                <i class="fas fa-users fa-3x text-gradient mb-4 mt-2"></i>
                <h4 class="fw-bold mb-3">Contact Developers</h4>
                <p class="text-secondary mb-4 flex-grow-1">Need something custom built? Browse our verified developers and start a conversation.</p>
                <a href="<?= $base_url; ?>user/about.php" class="btn btn-premium-outline w-100">View Developers</a>
            </div>
        </div>
        
        <!-- Latest Offers Card -->
        <div class="col-md-4 animate-fade-up" style="animation-delay: 0.3s;">
            <div class="glass-panel text-center h-100 d-flex flex-column p-4 position-relative overflow-hidden">
                <div class="position-absolute" style="top: -20px; right: -20px; font-size: 8rem; color: var(--accent-tertiary); opacity: 0.05;">
                    <i class="fas fa-tags"></i>
                </div>
                <i class="fas fa-tags fa-3x text-gradient mb-4 mt-2"></i>
                <h4 class="fw-bold mb-3">Latest Offers</h4>
                <p class="text-secondary mb-4 flex-grow-1">Check out the latest discounts and limited-time deals on premium and custom website templates.</p>
                <a href="<?= $base_url; ?>user/websites.php" class="btn btn-premium-outline w-100">See Offers</a>
            </div>
        </div>
    </div>

    <!-- Authorized Content / Bookings -->
    <div class="mt-5 animate-fade-up" style="animation-delay: 0.4s;">
        <h4 class="fw-bold mb-4"><i class="fas fa-box-open me-2 text-gradient"></i> My Authorized Content & Bookings</h4>
        <div class="glass-panel p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle m-0" style="background: transparent;">
                    <thead style="border-bottom: 1px solid var(--glass-border);">
                        <tr>
                            <th class="py-3 px-4 text-secondary fw-normal">Item Details</th>
                            <th class="py-3 px-4 text-secondary fw-normal">Price</th>
                            <th class="py-3 px-4 text-secondary fw-normal">Date Requested</th>
                            <th class="py-3 px-4 text-secondary fw-normal">Status</th>
                            <th class="py-3 px-4 text-secondary fw-normal text-end">Access</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $stmt = $conn->prepare("
                            SELECT b.*, o.title, o.price, o.image_path, o.website_link, o.zip_file 
                            FROM bookings b 
                            JOIN offers o ON b.offer_id = o.id 
                            WHERE b.user_id = ? 
                            ORDER BY b.id DESC
                        ");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $bookings = $stmt->get_result();

                        if ($bookings && $bookings->num_rows > 0) {
                            while($row = $bookings->fetch_assoc()){
                        ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center">
                                    <?php if($row['image_path']){ ?>
                                        <img src="../<?= htmlspecialchars($row['image_path']); ?>" alt="img" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid var(--glass-border);">
                                    <?php } else { ?>
                                        <div class="rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border);">
                                            <i class="fas fa-image text-secondary"></i>
                                        </div>
                                    <?php } ?>
                                    <div>
                                        <h6 class="m-0 fw-bold"><?= htmlspecialchars($row['title']); ?></h6>
                                        <small class="text-secondary">ID: #<?= $row['offer_id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-success fw-bold">$<?= number_format($row['price'], 2); ?></td>
                            <td class="py-3 px-4 text-secondary"><?= date("M d, Y", strtotime($row['created_at'])); ?></td>
                            <td class="py-3 px-4">
                                <?php
                                if($row['status'] == "pending") echo "<span class='badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-2 rounded-pill'>Pending</span>";
                                elseif($row['status'] == "accepted" || $row['status'] == "completed") echo "<span class='badge bg-success bg-opacity-25 text-success border border-success px-3 py-2 rounded-pill'>Authorized</span>";
                                else echo "<span class='badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-2 rounded-pill'>Rejected</span>";
                                ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <?php if($row['status'] == "accepted" || $row['status'] == "completed"): ?>
                                    <?php if(!empty($row['website_link'])): ?>
                                        <a href="<?= htmlspecialchars($row['website_link']); ?>" target="_blank" class="btn btn-sm btn-premium rounded-pill px-3">
                                            <i class="fas fa-external-link-alt me-1"></i> Access Content
                                        </a>
                                    <?php elseif(!empty($row['zip_file'])): ?>
                                        <a href="../<?= htmlspecialchars($row['zip_file']); ?>" download class="btn btn-sm btn-info text-white rounded-pill px-3">
                                            <i class="fas fa-download me-1"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3 disabled">Authorized</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 disabled" title="Pending approval">
                                        <i class="fas fa-lock me-1"></i> Locked
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center py-5 text-secondary"><i class="fas fa-box-open fa-3x mb-3 d-block opacity-50"></i> You haven\'t requested or purchased any content yet.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>

<?php include "../includes/footer.php"; ?>
