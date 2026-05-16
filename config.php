<?php
// ============================================================
// config.php — Central Configuration & Database Hub
// ============================================================

// ── Session Security ──────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    // On localhost (HTTP), secure must be false; only true on HTTPS
    $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
             && ($_SERVER['HTTP_HOST'] ?? '') !== 'localhost'
             && strpos($_SERVER['HTTP_HOST'] ?? '', '127.') !== 0;
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Environment Detection ─────────────────────────────────
// Set to false before uploading to InfinityFree (production)
define('DEBUG', false);

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ── Site Identity ─────────────────────────────────────────
define('SITE_NAME',    'DreamWebsites');
define('SITE_TAGLINE', 'Buy · Sell · Build Your Dream Website');

// ── Database Credentials ──────────────────────────────────
//
//  LOCAL (XAMPP) — active by default
//  Comment this block and uncomment the InfinityFree block
//  before uploading.
//
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dream_website';

//
//  INFINITYFREE — uncomment before deploying
//
// $host = 'sql104.infinityfree.com';
// $user = 'if0_41574431';
// $pass = 'aA7N57KQPE';            // <— replace with your actual password
// $db   = 'if0_41574431_dream_website';

// ── Base URL ──────────────────────────────────────────────
//  LOCAL:
$base_url = 'http://localhost/dreamwebsitegpt/';
//  INFINITYFREE (uncomment before deploying):
// $base_url = 'https://your-subdomain.infinityfreeapp.com/';

// ── Database Connection ───────────────────────────────────
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    if (DEBUG) {
        die('Database Connection Failed: ' . $conn->connect_error);
    } else {
        die('Service temporarily unavailable. Please try again later.');
    }
}
$conn->set_charset('utf8mb4');

// ── CSRF Token ────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Utility Functions ─────────────────────────────────────

/**
 * Sanitise user input against XSS.
 */
function clean_input(string $data): string {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Hash a plain-text password.
 */
function hash_password(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a plain-text password against a stored hash.
 */
function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Execute a prepared statement and return the statement handle.
 * Param types are auto-detected (i = int, d = float, s = string).
 */
function safe_query(mysqli $conn, string $query, array $params = []): mysqli_stmt {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log('DB prepare error: ' . $conn->error);
        die('A database error occurred.');
    }
    if (!empty($params)) {
        $types = '';
        foreach ($params as $p) {
            if (is_int($p))   $types .= 'i';
            elseif (is_float($p)) $types .= 'd';
            else              $types .= 's';
        }
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

/**
 * Fetch all rows from a statement as an associative array.
 */
function fetch_all(mysqli_stmt $stmt): array {
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Fetch a single row from a statement as an associative array.
 */
function fetch_one(mysqli_stmt $stmt): ?array {
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Redirect if the user is not logged in.
 */
function check_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . (strpos($_SERVER['SCRIPT_NAME'], '/admin') !== false ? '../login.php' : 'login.php'));
        exit();
    }
}

/**
 * Die with an access-denied message if the session role doesn't match.
 */
function check_role(string $role): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        http_response_code(403);
        die('403 — Access Denied.');
    }
}

/**
 * Destroy the session and redirect to the login page.
 */
function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
                  $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: login.php');
    exit();
}

/**
 * Validate and move an uploaded file.
 * Returns the saved path on success, or an error string on failure.
 */
function upload_file(array $file, string $folder = 'uploads/'): string {
    $allowed  = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'zip', 'rar', 'pdf'];
    $max_size = 10 * 1024 * 1024; // 10 MB (InfinityFree limit)

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload error (code ' . $file['error'] . ').';
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return 'Invalid file type. Allowed: ' . implode(', ', $allowed);
    }
    if ($file['size'] > $max_size) {
        return 'File exceeds the 10 MB size limit.';
    }
    // Validate MIME type for images
    if (in_array($ext, ['png','jpg','jpeg','gif','webp'], true)) {
        $mime = mime_content_type($file['tmp_name']);
        if (!str_starts_with($mime, 'image/')) {
            return 'File content does not match image type.';
        }
    }
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    $new_name = uniqid('file_', true) . '.' . $ext;
    $dest     = rtrim($folder, '/') . '/' . $new_name;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $dest;
    }
    return 'Failed to save the uploaded file.';
}

/**
 * Validate a CSRF token submitted via POST.
 */
function verify_csrf(): void {
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('CSRF token mismatch. Please refresh and try again.');
    }
}

// ── Security Headers (sent once, from here) ───────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}