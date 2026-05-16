<?php
session_start();
include "../config.php";

// Role Security
if(!isset($_SESSION['role']) || $_SESSION['role'] != "user"){
    header("Location: " . $base_url . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Handle Profile Update
if(isset($_POST['update_profile'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if(empty($name) || empty($email)){
        $error = "Name and Email are required!";
    } else {
        if(!empty($password)){
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $hashed, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }
        
        if($stmt->execute()){
            $_SESSION['name'] = $name;
            $success = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile. Email might already be in use.";
        }
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

include "../includes/header.php";
include "../includes/navbar.php";
?>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 animate-fade-up">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="display-6 fw-bold m-0">Your <span class="text-gradient">Profile</span></h2>
                <a href="dashboard.php" class="btn btn-premium-outline btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger shadow-sm border-0 alert-dismissible fade show rounded-3">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success shadow-sm border-0 alert-dismissible fade show rounded-3">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="glass-panel p-4 p-md-5 position-relative overflow-hidden">
                <div class="position-absolute" style="top: -20px; right: -20px; font-size: 8rem; color: var(--accent-primary); opacity: 0.05;">
                    <i class="fas fa-user-circle"></i>
                </div>

                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_data['name']) ?>&background=random&size=100" alt="Avatar" class="rounded-circle mb-3 border border-secondary" style="border-width: 3px !important;">
                    <h5 class="fw-bold m-0"><?= htmlspecialchars($user_data['name']) ?></h5>
                    <p class="text-secondary small">Member since <?= date("M Y", strtotime($user_data['created_at'])) ?></p>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent text-secondary border-secondary"><i class="fas fa-user"></i></span>
                            <input type="text" name="name" class="form-control-premium w-100" value="<?= htmlspecialchars($user_data['name']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent text-secondary border-secondary"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control-premium w-100" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small">New Password <span class="text-secondary opacity-50">(Leave blank to keep current)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent text-secondary border-secondary"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control-premium w-100" placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-premium w-100 fs-6">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
