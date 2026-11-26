<?php
// Require this file before sending any HTML output.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || empty($_SESSION['user_id'])) {
    header('Location: login-form.php');
    exit;
}
?>
