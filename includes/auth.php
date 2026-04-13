<?php
session_start();
function isLoggedIn() { return isset($_SESSION['user_id']); }
function isOwner()    { return ($_SESSION['role'] ?? '') === 'owner'; }
function isEmployee() { return ($_SESSION['role'] ?? '') === 'employee'; }
function requireLogin()    { if (!isLoggedIn()) { header("Location: /techstock/login.php"); exit(); } }
function requireOwner()    { requireLogin(); if (!isOwner()) { header("Location: /techstock/dashboard.php"); exit(); } }
function requireEmployee() { requireLogin(); if (!isOwner() && !isEmployee()) { header("Location: /techstock/index.php"); exit(); } }

function auditLog($conn, $action, $details = '') {
    if (!isset($_SESSION['user_id'])) return;
    $uid = $_SESSION['user_id'];
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id,action,details,ip_address) VALUES (?,?,?,?)");
    $stmt->bind_param("isss", $uid, $action, $details, $ip);
    $stmt->execute();
}

function getEmployeeShop($conn, $user_id) {
    $stmt = $conn->prepare("SELECT s.* FROM shops s JOIN shop_employees se ON s.id=se.shop_id WHERE se.employee_id=? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function formatPrice($p) { return 'KSh ' . number_format($p, 0); }
?>
