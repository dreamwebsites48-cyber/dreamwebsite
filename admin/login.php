<?php
/**
 * admin/login.php — Admin-specific login page.
 * Security: config.php only, CSRF, session regeneration, prepared statements.
 */
require_once __DIR__ . '/../config.php';

// Already logged-in admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ' . $base_url . 'admin/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '⚠️ Invalid request token.';
    } else {
        $email    = clean_input($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $error = '⚠️ Email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '⚠️ Invalid email format.';
        } else {
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['role']       = 'admin';
                $_SESSION['name']       = $user['name'];
                $_SESSION['login_time'] = time();
                $_SESSION['msg']        = '✅ Welcome, ' . htmlspecialchars($user['name']);
                header('Location: ' . $base_url . 'admin/dashboard.php');
                exit();
            } else {
                $error = '❌ Invalid credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
</head>
<body data-theme="dark" style="min-height:100vh;display:flex;justify-content:center;align-items:center;background:var(--bg-primary);">
<div class="glass-panel text-center" style="width: 380px; padding: 40px 30px;">
    <h2 class="mb-4 fw-bold">🛡️ Admin Login</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success py-2"><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
            <input type="email" name="email" class="form-control-premium text-center" placeholder="Admin Email" required>
        </div>
        <div class="mb-4">
            <input type="password" name="password" class="form-control-premium text-center" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-premium w-100"><i class="fas fa-shield-alt me-2"></i>Secure Login</button>
    </form>

    <p class="mt-3 mb-0">
        <a href="../login.php" class="text-info">⬅ Back to User Login</a>
    </p>
</div>
</body>
</html>

