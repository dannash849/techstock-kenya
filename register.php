<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
if(isLoggedIn()) { header("Location: owner/dashboard.php"); exit(); }
$page_title = 'Register — TechStock Kenya';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $name  = trim($_POST['full_name']??'');
    $email = trim($_POST['email']??'');
    $phone = trim($_POST['phone']??'');
    $pass  = $_POST['password']??'';
    if($name && $email && $pass) {
        $check=$conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s",$email); $check->execute();
        if($check->get_result()->num_rows > 0) { $error="Email already registered."; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name,email,phone,password,role) VALUES (?,?,?,?,'owner')");
            $stmt->bind_param("ssss",$name,$email,$phone,$hash);
            if($stmt->execute()) {
                $uid = $conn->insert_id;
                $_SESSION['user_id']=$uid; $_SESSION['full_name']=$name;
                $_SESSION['email']=$email; $_SESSION['role']='owner';
                auditLog($conn,'register','New owner registered');
                header("Location: /techstock/owner/dashboard.php"); exit();
            } else { $error="Registration failed. Try again."; }
        }
    } else { $error="Please fill all required fields."; }
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
      <div style="width:72px;height:72px;background:var(--blue);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2.2rem;margin:0 auto 22px">💻</div>
      <div style="font-family:'Syne',sans-serif;font-size:2.2rem;font-weight:800;color:var(--white)">Start Selling<br><span style="color:var(--cyan)">on TechStock</span></div>
      <p style="color:var(--text2);font-size:0.875rem;margin-top:24px;line-height:1.8;max-width:280px">Create your account, set up your shop, and start listing PCs in minutes.</p>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-box">
      <div class="auth-title">Create Account</div>
      <div class="auth-sub">Register as a shop owner</div>
      <?php if($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" placeholder="Jane Doe" required></div>
        <div class="form-group"><label>Email Address *</label><input type="email" name="email" placeholder="you@example.com" required></div>
        <div class="form-group"><label>Phone Number</label><input type="tel" name="phone" placeholder="07XX XXX XXX"></div>
        <div class="form-group"><label>Password *</label><input type="password" name="password" placeholder="Min 8 characters" required></div>
        <button type="submit" class="auth-btn">Create Account →</button>
      </form>
      <p style="text-align:center;margin-top:20px;color:var(--text2);font-size:0.875rem">
        Already have an account? <a href="login.php" style="color:var(--blue);font-weight:600">Sign In</a>
      </p>
    </div>
  </div>
</div>
</body></html>
