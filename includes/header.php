<?php
// config.php already started the session and defined $base_url.
// This guard is kept only for edge-cases where header.php is included standalone.
if (!isset($base_url)) {
    require_once dirname(__DIR__) . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('SITE_NAME') ? SITE_NAME : 'DreamWebsites' ?> - Premium Web Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Premium Theme CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/theme.css">
</head>
<body data-theme="dark">
<div class="main-content">

