<?php
/**
 * setup.php — One-time database setup & hash generator
 * Run via: http://localhost/dreamwebsitegpt/setup.php
 * DELETE this file after running!
 */

// Admin credentials
$admin_email    = 'admin@dreamwebsites.com';
$admin_password = 'Admin@1234';
$admin_name     = 'Super Admin';

// Pre-computed bcrypt hash (cost=12) for 'Admin@1234'
$hash = '$2y$12$BJkRXo0ALTp5NFN2SfOWgeSF0dShQ85EPRJdSuygdfpsAkHR0S9TC';

// DB connection
$conn = new mysqli('localhost', 'root', '', '');
if ($conn->connect_error) {
    die('<pre style="color:red">DB Connection Failed: ' . $conn->connect_error . '</pre>');
}

$steps  = [];
$errors = [];

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS `dream_website` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conn->select_db('dream_website');
$conn->set_charset('utf8mb4');
$steps[] = '✅ Database <b>dream_website</b> created / verified.';

// Run SQL file
$sqlFile = __DIR__ . '/database.sql';
if (!file_exists($sqlFile)) {
    $errors[] = '❌ database.sql not found at: ' . $sqlFile;
} else {
    $sql = file_get_contents($sqlFile);
    // Execute multi-query
    if ($conn->multi_query($sql)) {
        do { $conn->store_result(); } while ($conn->next_result());
        $steps[] = '✅ database.sql imported successfully.';
    } else {
        $errors[] = '❌ SQL import error: ' . $conn->error;
    }
}

// Ensure admin user has correct hash
$conn->query("UPDATE `dream_website`.`users` SET password='" . $conn->real_escape_string($hash) . "' WHERE email='" . $conn->real_escape_string($admin_email) . "'");
if ($conn->affected_rows >= 0) {
    $steps[] = '✅ Admin password hash updated correctly.';
} else {
    $errors[] = '⚠️ Could not update admin password hash.';
}

// Verify login
$stmt = $conn->prepare("SELECT password FROM users WHERE email=? LIMIT 1");
$stmt->bind_param('s', $admin_email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$loginOk = $row && password_verify($admin_password, $row['password']);
if ($loginOk) {
    $steps[] = '✅ Login verification PASSED — admin can log in.';
} else {
    $errors[] = '❌ Login verification FAILED — check hash.';
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DreamWebsites — Setup</title>
<style>
  body{font-family:system-ui,sans-serif;background:#0f111a;color:#f0f6fc;max-width:680px;margin:60px auto;padding:20px;}
  h1{color:#3b82f6;}
  .card{background:#161b22;border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:24px;margin-bottom:20px;}
  .step{padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05);}
  .step:last-child{border:none;}
  .creds{background:#1f242e;border-radius:10px;padding:16px;margin-top:16px;}
  .creds b{color:#10b981;}
  .error{color:#ef4444;}
  .warn{background:#422006;border:1px solid #f59e0b;border-radius:10px;padding:14px;margin-top:16px;color:#fbbf24;font-size:.9rem;}
</style>
</head>
<body>
<h1>🚀 DreamWebsites Setup</h1>

<div class="card">
  <h3>Setup Steps</h3>
  <?php foreach ($steps as $s): ?>
    <div class="step"><?= $s ?></div>
  <?php endforeach; ?>
  <?php foreach ($errors as $e): ?>
    <div class="step error"><?= $e ?></div>
  <?php endforeach; ?>
</div>

<?php if (empty($errors) && $loginOk): ?>
<div class="card">
  <h3>✅ Setup Complete!</h3>
  <p>Your database is ready. Use these credentials to log in:</p>
  <div class="creds">
    <div>🌐 URL: <b><a href="http://localhost/dreamwebsitegpt/login.php" style="color:#3b82f6;">http://localhost/dreamwebsitegpt/login.php</a></b></div>
    <div style="margin-top:10px;">📧 Email: <b><?= htmlspecialchars($admin_email) ?></b></div>
    <div style="margin-top:6px;">🔑 Password: <b><?= htmlspecialchars($admin_password) ?></b></div>
    <div style="margin-top:6px;">👤 Role: <b>Admin</b></div>
  </div>
  <div class="warn">
    ⚠️ <strong>Security Notice:</strong> Delete <code>setup.php</code> after running it! 
    It contains sensitive credentials.
  </div>
</div>
<?php endif; ?>

<div class="card">
  <h3>📊 Generated Hash (for reference)</h3>
  <p style="font-family:monospace;font-size:.8rem;word-break:break-all;color:#8b949e;"><?= htmlspecialchars($hash) ?></p>
</div>
</body>
</html>
