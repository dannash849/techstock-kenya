<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'TechStock Kenya' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css">
</head>
<body>
<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a href="/techstock/index.php" class="nav-logo">
      <div class="nav-logo-mark">💻</div>
      <div class="nav-logo-text">Tech<span>Stock</span> <span style="color:var(--text2);font-size:0.7rem;font-weight:400">Kenya</span></div>
    </a>
    <div class="nav-links">
      <a href="/techstock/index.php" class="<?=basename($_SERVER['PHP_SELF'])=='index.php'?'active':''?>">Home</a>
      <a href="/techstock/marketplace.php" class="<?=basename($_SERVER['PHP_SELF'])=='marketplace.php'?'active':''?>">Marketplace</a>
      <a href="/techstock/shops.php" class="<?=basename($_SERVER['PHP_SELF'])=='shops.php'?'active':''?>">Shops</a>
    </div>
    <div class="nav-actions">
      <?php if(isLoggedIn()): ?>
      <a href="/techstock/<?=$_SESSION['role']=='owner'?'owner':'employee'?>/dashboard.php" class="nav-btn nav-btn-primary">Dashboard</a>
      <?php else: ?>
      <a href="/techstock/login.php" class="nav-btn nav-btn-ghost">Sign In</a>
      <a href="/techstock/register.php" class="nav-btn nav-btn-primary">Get Started</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
