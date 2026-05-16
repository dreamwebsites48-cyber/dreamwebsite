<?php
/**
 * forgot.php — Password reset request page.
 * Security: CSRF, prepared statements, no plaintext password storage note.
 */
require_once __DIR__ . '/config.php';

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '⚠️ Invalid request token.';
    } else {
        $email    = clean_input($_POST['email'] ?? '');
        $req_pass = clean_input($_POST['requested_password'] ?? '');

        if (empty($email) || empty($req_pass)) {
            $error = '⚠️ Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '⚠️ Invalid email format.';
        } elseif (strlen($req_pass) < 6) {
            $error = '⚠️ Requested password must be at least 6 characters.';
        } else {
            $chk = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $chk->bind_param('s', $email); $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) {
                $chk->close();
                $ins = $conn->prepare('INSERT INTO password_requests (email, requested_password) VALUES (?, ?)');
                $ins->bind_param('ss', $email, $req_pass);
                if ($ins->execute()) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $msg = '✅ Your request has been submitted. The admin will update your password within 24 hours.';
                } else {
                    $error = '❌ Failed to submit request. Please try again.';
                }
                $ins->close();
            } else {
                $chk->close();
                // Generic message to avoid account enumeration
                $msg = '✅ If that email exists in our system, a request has been submitted.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= SITE_NAME ?> — Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body{display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
        .hero-bg-glow{position:fixed;width:800px;height:800px;background:radial-gradient(circle,rgba(139,92,246,.15) 0%,transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);z-index:-1;border-radius:50%;pointer-events:none;}
        .login-card{width:100%;max-width:480px;padding:40px;}
        .error-box{background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.30);color:#ef4444;padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;font-size:.9rem;}
        .success-box{background:rgba(16,185,129,.10);border:1px solid rgba(16,185,129,.30);color:#10b981;padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;font-size:.9rem;}
    </style>
</head>
<body data-theme="dark">
<div class="hero-bg-glow"></div>
<div class="glass-panel login-card animate-fade-up">
    <div class="text-center mb-4">
        <i class="fas fa-key text-gradient mb-3" style="font-size:3rem;"></i>
        <h1 class="h3 fw-bold m-0">Reset Password</h1>
        <p class="text-secondary small mt-1">Submit your desired new password to the Admin.</p>
    </div>
    <?php if($error): ?><div class="error-box"><?= $error ?></div><?php endif; ?>
    <?php if($msg):   ?><div class="success-box"><?= $msg ?></div>
    <?php else: ?>
    <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="mb-3 position-relative">
            <i class="fas fa-envelope position-absolute text-secondary" style="top:15px;left:15px;"></i>
            <input type="email" name="email" class="form-control-premium w-100" style="padding-left:45px;" placeholder="Registered Email Address" required>
        </div>
        <div class="mb-4 position-relative">
            <i class="fas fa-lock position-absolute text-secondary" style="top:15px;left:15px;"></i>
            <input type="password" name="requested_password" class="form-control-premium w-100" style="padding-left:45px;" placeholder="Desired New Password (6+ chars)" minlength="6" required>
        </div>
        <button type="submit" name="send" class="btn btn-premium w-100 mb-3">
            Submit Request <i class="fas fa-paper-plane ms-2"></i>
        </button>
    </form>
    <?php endif; ?>
    <div class="text-center mt-4">
        <p class="text-secondary m-0" style="font-size:.9rem;">Remember your password? <a href="login.php" class="fw-bold">Sign In</a></p>
    </div>
</div>
<script src="assets/js/theme.js"></script>
</body>
</html>