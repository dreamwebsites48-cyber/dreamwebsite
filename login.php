<?php
/**
 * login.php — User login page.
 * Security: prepared statements, bcrypt verify, session regeneration,
 *            activity logging, cache-control headers.
 */
require_once __DIR__ . '/config.php';

// No-cache headers
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user';
    if ($role === 'admin')         header('Location: ' . $base_url . 'admin/dashboard.php');
    elseif ($role === 'developer') header('Location: ' . $base_url . 'developer/dashboard.php');
    else                           header('Location: ' . $base_url . 'user/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '⚠️ Invalid request token. Please refresh and try again.';
    } else {
        $email = clean_input($_POST['email']   ?? '');
        $pass  = trim($_POST['password'] ?? '');

        if (empty($email) || empty($pass)) {
            $error = '⚠️ Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '⚠️ Invalid email format.';
        } else {
            $stmt = $conn->prepare('SELECT id, name, email, password, role, developer_status FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($pass, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                if ($user['role'] === 'developer') {
                    if ($user['developer_status'] === 'approved') {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name']    = $user['name'];
                        $_SESSION['role']    = $user['role'];

                        // Log activity
                        $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                        $log = $conn->prepare('INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)');
                        $act = 'User logged in';
                        $log->bind_param('iss', $user['id'], $act, $ip);
                        $log->execute(); $log->close();

                        header('Location: ' . $base_url . 'developer/dashboard.php');
                        exit();
                    } elseif ($user['developer_status'] === 'pending') {
                        $error = '⏳ Your developer request is still pending admin approval.';
                    } else {
                        $error = '❌ Your developer request was rejected.';
                    }
                } elseif ($user['role'] === 'admin') {
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['name']       = $user['name'];
                    $_SESSION['role']       = $user['role'];
                    $_SESSION['login_time'] = time();

                    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $log = $conn->prepare('INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)');
                    $act = 'User logged in';
                    $log->bind_param('iss', $user['id'], $act, $ip);
                    $log->execute(); $log->close();

                    header('Location: ' . $base_url . 'admin/dashboard.php');
                    exit();
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name']    = $user['name'];
                    $_SESSION['role']    = $user['role'];

                    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $log = $conn->prepare('INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)');
                    $act = 'User logged in';
                    $log->bind_param('iss', $user['id'], $act, $ip);
                    $log->execute(); $log->close();

                    header('Location: ' . $base_url . 'user/dashboard.php');
                    exit();
                }
            } else {
                // Generic message to avoid user enumeration
                $error = '❌ Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> — Sign In</title>
    <meta name="description" content="Sign in to your <?= SITE_NAME ?> account.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .hero-bg-glow {
            position: fixed;
            width: 800px; height: 800px;
            background: radial-gradient(circle, rgba(139,92,246,.15) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            z-index: -1;
            border-radius: 50%;
            pointer-events: none;
        }
        .login-card { width: 100%; max-width: 420px; padding: 40px; }
        .error-box {
            background: rgba(239,68,68,.10);
            border: 1px solid rgba(239,68,68,.30);
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: .9rem;
        }
    </style>
</head>
<body data-theme="dark">

<div class="hero-bg-glow"></div>

<div class="glass-panel login-card animate-fade-up">
    <div class="text-center mb-4">
        <i class="fas fa-fingerprint text-gradient mb-3" style="font-size:3rem;"></i>
        <h1 class="h3 fw-bold m-0">Welcome Back</h1>
        <p class="text-secondary small mt-1">Sign in to continue to <?= htmlspecialchars(SITE_NAME) ?></p>
    </div>

    <?php if ($error): ?>
        <div class="error-box"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="login-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3 position-relative">
            <i class="fas fa-envelope position-absolute text-secondary" style="top:15px;left:15px;"></i>
            <input type="email" name="email" id="login-email"
                   class="form-control-premium w-100" style="padding-left:45px;"
                   placeholder="Email Address"
                   value="<?= isset($_POST['email']) ? htmlspecialchars(clean_input($_POST['email'])) : '' ?>"
                   required autocomplete="email">
        </div>

        <div class="mb-4 position-relative">
            <i class="fas fa-lock position-absolute text-secondary" style="top:15px;left:15px;"></i>
            <input type="password" name="password" id="login-password"
                   class="form-control-premium w-100" style="padding-left:45px;padding-right:45px;"
                   placeholder="Password" required autocomplete="current-password">
            <i class="fas fa-eye position-absolute text-secondary" id="toggle-pw"
               style="top:15px;right:15px;cursor:pointer;" title="Show/hide password"></i>
        </div>

        <button type="submit" name="login" id="login-submit" class="btn btn-premium w-100 mb-3">
            Sign In <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </form>

    <div class="text-center mt-4">
        <p class="text-secondary mb-1" style="font-size:.9rem;">
            <a href="forgot.php" class="text-decoration-none" style="color:var(--accent-warning);">Forgot Password?</a>
        </p>
        <p class="text-secondary m-0" style="font-size:.9rem;">
            Don't have an account? <a href="register.php" class="fw-bold">Sign Up</a>
        </p>
    </div>
</div>

<script src="assets/js/theme.js"></script>
<script>
// Toggle password visibility
document.getElementById('toggle-pw').addEventListener('click', function () {
    const pw = document.getElementById('login-password');
    const icon = this;
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
// Prevent double-submit
document.getElementById('login-form').addEventListener('submit', function () {
    const btn = document.getElementById('login-submit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In…';
});
</script>
</body>
</html>