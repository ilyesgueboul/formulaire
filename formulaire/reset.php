<?php
session_start();

session_unset();
session_destroy();
session_start();
session_regenerate_id(true);

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

$redirect_url = "index.php";
if (isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) === $_SERVER['HTTP_HOST']) {
    $redirect_url = $_SERVER['HTTP_REFERER'];
}

header("Location: " . htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8'));
exit;
?>
