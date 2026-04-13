<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
if(isLoggedIn()) { header("Location: ".($_SESSION['role']==='owner'?'owner':'employee')."/dashboard.php"); exit(); }
$page_title = 'Sign In — TechStock Kenya';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email']??''); $pass = $_POST['password']??'';
    if($email && $pass) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND status='active'");
        $stmt->bind_param("s",$email); $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if($user && password_verify($pass,$user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];
            // If employee, store their shop
            if($user['role'] === 'employee') {
                $shop = getEmployeeShop($conn, $user['id']);
                $_SESSION['shop_id'] = $shop['id'] ?? null;
            }
            auditLog($conn, 'login', 'User logged in');
            $redirect = $user['role'] === 'owner' ? 'owner/dashboard.php' : 'employee/dashboard.php';
            header("Location: /techstock/$redirect"); exit();
        } else { $error = "Invalid email or password."; }
    } else { $error = "Please fill in all fields."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $page_title ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-left">
    <div class="auth-deco auth-deco-1"></div>
    <div class="auth-deco auth-deco-2"></div>
    <div style="position:relative;z-index:1;text-align:center">
      <div style="width:72px;height:72px;background:var(--blue);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2.2rem;margin:0 auto 22px;box-shadow:0 8px 32px rgba(59,130,246,0.3)">💻</div>
      <div style="font-family:'Syne',sans-serif;font-size:2.2rem;font-weight:800;color:var(--white)">Tech<span style="color:var(--cyan)">Stock</span></div>
      <div style="color:var(--text2);font-size:0.8rem;letter-spacing:2px;text-transform:uppercase;margin-top:6px">Kenya</div>
      <p style="color:var(--text2);font-size:0.875rem;margin-top:28px;line-height:1.8;max-width:280px">Manage your PC shop, track inventory, and grow your business.</p>
      <div style="margin-top:32px;display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:280px;margin-left:auto;margin-right:auto">
        <?php foreach([['💻','Multi-Shop'],['👨‍💻','Employee Control'],['🔒','Fraud Prevention'],['📊','Analytics']] as [$i,$l]): ?>
        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
          <div style="font-size:1.3rem;margin-bottom:4px"><?=$i?></div>
          <div style="color:var(--text2);font-size:0.72rem"><?=$l?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-box">
      <div class="auth-title">Welcome back</div>
      <div class="auth-sub">Sign in to your TechStock account</div>
      <?php if($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-group"><label>Email Address</label><input type="email" name="email" placeholder="you@example.com" required autofocus></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Your password" required></div>
        <button type="submit" class="auth-btn">Sign In →</button>
      </form>
      <p style="text-align:center;margin-top:20px;color:var(--text2);font-size:0.875rem">
        Don't have an account? <a href="register.php" style="color:var(--blue);font-weight:600">Register your shop</a>
      </p>
      <p style="text-align:center;margin-top:10px;color:var(--text3);font-size:0.78rem">Owner: admin@techstock.co.ke / password</p>
    </div>
  </div>
</div>
</body></html>
