<?php
require_once __DIR__ . '/../config.php';

// 🔐 SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'developer') {
    header('Location: ' . $base_url . 'login.php');
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $dev_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM offers WHERE id=? AND developer_id=?");
    $stmt->bind_param("ii", $id, $dev_id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=deleted");
        exit();
    } else {
        die("❌ Failed to delete offer.");
    }
} else {
    die("❌ Invalid Request.");
}
?>

