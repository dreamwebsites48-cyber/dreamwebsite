<?php
require_once __DIR__ . '/config.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user';
    header('Location: ' . $base_url . ($role === 'admin' ? 'admin/dashboard.php' : ($role === 'developer' ? 'developer/dashboard.php' : 'user/dashboard.php')));
    exit();
}

$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '⚠️ Invalid request token.';
    } else {
        $name    = clean_input($_POST['name'] ?? '');
        $email   = clean_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $raw_role = $_POST['role'] ?? 'user';
        $role     = in_array($raw_role, ['user','developer'], true) ? $raw_role : 'user';

        if (empty($name)||empty($email)||empty($password)||empty($confirm)) $error='⚠️ All fields required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error='⚠️ Invalid email.';
        elseif (strlen($password) < 8) $error='⚠️ Password must be at least 8 characters.';
        elseif ($password !== $confirm) $error='⚠️ Passwords do not match.';
        else {
            $chk = $conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
            $chk->bind_param('s',$email); $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) { $error='⚠️ Email already registered.'; $chk->close(); }
            else {
                $chk->close();
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
                $dev_status = ($role==='developer') ? 'pending' : 'approved';
                $ins = $conn->prepare('INSERT INTO users (name,email,password,role,developer_status) VALUES (?,?,?,?,?)');
                $ins->bind_param('sssss',$name,$email,$hash,$role,$dev_status);
                if ($ins->execute()) {
                    $uid = $ins->insert_id; $ins->close();
                    $ip=''; $ip=$_SERVER['REMOTE_ADDR']??'unknown'; $act='User registered';
                    $log=$conn->prepare('INSERT INTO activity_logs (user_id,action,ip_address) VALUES (?,?,?)');
                    $log->bind_param('iss',$uid,$act,$ip); $log->execute(); $log->close();
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $msg='✅ Registration successful! Redirecting…';
                    header('Refresh:2;url=login.php');
                } else { $error='❌ Registration failed. Please try again.'; }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= SITE_NAME ?> — Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body{display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:40px 20px;}
        .hero-bg-glow{position:fixed;width:800px;height:800px;background:radial-gradient(circle,rgba(16,185,129,.15) 0%,transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);z-index:-1;border-radius:50%;pointer-events:none;}
        .register-card{width:100%;max-width:500px;padding:40px;}
        .alert-box{padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;font-size:.9rem;}
        .error-box{background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.30);color:#ef4444;}
        .success-box{background:rgba(16,185,129,.10);border:1px solid rgba(16,185,129,.30);color:#10b981;}
    </style>
</head>
<body data-theme="dark">
<div class="hero-bg-glow"></div>
<div class="glass-panel register-card animate-fade-up">
    <div class="text-center mb-4">
        <i class="fas fa-user-plus text-gradient mb-3" style="font-size:3rem;"></i>
        <h1 class="h3 fw-bold m-0">Create Account</h1>
        <p class="text-secondary small mt-1">Join <?= htmlspecialchars(SITE_NAME) ?> today</p>
    </div>
    <?php if($error): ?><div class="alert-box error-box"><?= $error ?></div><?php endif; ?>
    <?php if($msg):   ?><div class="alert-box success-box"><?= $msg ?></div><?php endif; ?>
    <?php if(!$msg): ?>
    <form method="POST" id="reg-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="mb-3 position-relative">
            <i class="fas fa-user position-absolute text-secondary" style="top:15px;left:15px;"></i>
            <input type="text" name="name" class="form-control-premium w-100" style="padding-left:45px;" placeholder="Full Name"
                   value="<?= isset($_POST['name'])?htmlspecialchars(clean_input($_POST['name'])):'' ?>" maxlength="100" required>
        </div>
        <div class="mb-3 position-relative">
            <i class="fas fa-envelope position-absolute text-secondary" style="top:15px;left:15px;"></i>
            <input type="email" name="email" class="form-control-premium w-100" style="padding-left:45px;" placeholder="Email Address"
                   value="<?= isset($_POST['email'])?htmlspecialchars(clean_input($_POST['email'])):'' ?>" required>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6 position-relative">
                <i class="fas fa-lock position-absolute text-secondary" style="top:15px;left:25px;"></i>
                <input type="password" name="password" class="form-control-premium w-100" style="padding-left:45px;" placeholder="Password (8+ chars)" minlength="8" required>
            </div>
            <div class="col-md-6 position-relative">
                <i class="fas fa-lock position-absolute text-secondary" style="top:15px;left:25px;"></i>
                <input type="password" name="confirm_password" class="form-control-premium w-100" style="padding-left:45px;" placeholder="Confirm Password" minlength="8" required>
            </div>
        </div>
        <div class="mb-4 position-relative">
            <i class="fas fa-briefcase position-absolute text-secondary" style="top:15px;left:15px;z-index:2;"></i>
            <select name="role" class="form-control-premium w-100" style="padding-left:45px;appearance:auto;" required>
                <option value="user"      <?= (($_POST['role']??'')==='user')      ?'selected':'' ?>>👤 Normal User (Buy/Browse)</option>
                <option value="developer" <?= (($_POST['role']??'')==='developer') ?'selected':'' ?>>💻 Developer (Sell/Upload)</option>
            </select>
        </div>
        <button type="submit" name="register" id="reg-btn" class="btn btn-premium w-100 mb-3">
            Create Account <i class="fas fa-user-check ms-2"></i>
        </button>
    </form>
    <?php endif; ?>
    <div class="text-center mt-3">
        <p class="text-secondary m-0" style="font-size:.9rem;">Already have an account? <a href="login.php" class="fw-bold">Sign In</a></p>
    </div>
</div>
<script src="assets/js/theme.js"></script>
<script>
const rf=document.getElementById('reg-form');
if(rf) rf.addEventListener('submit',function(){
    const b=document.getElementById('reg-btn');
    b.disabled=true; b.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>Creating…';
});
</script>
</body>
</html>