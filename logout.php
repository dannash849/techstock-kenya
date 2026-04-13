<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
auditLog($conn, 'logout', 'User logged out');
session_destroy();
header("Location: /techstock/index.php");
exit();
