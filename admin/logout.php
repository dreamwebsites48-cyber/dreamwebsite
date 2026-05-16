<?php
session_start();

// 🔐 CHECK IF USER LOGGED IN
if(isset($_SESSION['role'])){

    // 🧹 UNSET ALL SESSION DATA
    $_SESSION = [];

    // 🍪 DELETE SESSION COOKIE (VERY IMPORTANT)
    if (ini_get("session.use_cookies")) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // ❌ DESTROY SESSION
    session_destroy();
}

// 🔄 START NEW SESSION FOR MESSAGE
session_start();

// 🔔 LOGOUT MESSAGE
$_SESSION['msg'] = "🚪 Admin Logged out successfully!";

// 🔁 REDIRECT (BEST PRACTICE: always go to main login)
header("Location: ../login.php");
exit();
?>

