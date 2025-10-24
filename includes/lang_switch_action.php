<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current = $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? 'en');
$next = ($current === 'en') ? 'ar' : 'en';

$_SESSION['lang'] = $next;
setcookie('lang', $next, time() + (86400 * 30), "/");

header("HTTP/1.1 204 No Content"); // no redirect body
exit;
