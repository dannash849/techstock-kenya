<?php
$pg = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
$unread_orders = 0;
if(isset($conn)) {
    if($role === 'owner') {
        $unread_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
    } elseif($role === 'employee' && isset($_SESSION['shop_id'])) {
        $sid = $_SESSION['shop_id'];
        $unread_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE shop_id=$sid AND status='pending'")->fetch_assoc()['c'];
    }
}
?>
<nav class="sidebar">
  <div class="sidebar-top">
    <a href="/techstock/index.php" class="sidebar-logo">
      <div class="sidebar-logo-mark">💻</div>
      <div class="sidebar-logo-text">Tech<span>Stock</span></div>
    </a>
  </div>

  <?php if($role === 'owner'): ?>
  <div class="sidebar-section">Overview</div>
  <div class="sidebar-nav">
    <a href="/techstock/owner/dashboard.php" class="<?=$pg=='dashboard.php'&&strpos($_SERVER['PHP_SELF'],'owner')!==false?'active':''?>"><span class="s-icon">📊</span><span>Dashboard</span></a>
    <a href="/techstock/owner/shops.php" class="<?=$pg=='shops.php'?'active':''?>"><span class="s-icon">🏬</span><span>My Shops</span></a>
    <a href="/techstock/owner/employees.php" class="<?=$pg=='employees.php'?'active':''?>"><span class="s-icon">👨‍💻</span><span>Employees</span></a>
    <a href="/techstock/owner/products.php" class="<?=$pg=='products.php'?'active':''?>"><span class="s-icon">💻</span><span>All Products</span></a>
    <a href="/techstock/owner/orders.php" class="<?=$pg=='orders.php'?'active':''?>"><span class="s-icon">📦</span><span>Orders <?=$unread_orders>0?"<span class='badge badge-red' style='margin-left:4px'>$unread_orders</span>":''?></span></a>
    <a href="/techstock/owner/analytics.php" class="<?=$pg=='analytics.php'?'active':''?>"><span class="s-icon">📈</span><span>Analytics</span></a>
    <a href="/techstock/owner/audit.php" class="<?=$pg=='audit.php'?'active':''?>"><span class="s-icon">🔍</span><span>Audit Logs</span></a>
  </div>

  <?php elseif($role === 'employee'): ?>
  <div class="sidebar-section">My Shop</div>
  <div class="sidebar-nav">
    <a href="/techstock/employee/dashboard.php" class="<?=$pg=='dashboard.php'&&strpos($_SERVER['PHP_SELF'],'employee')!==false?'active':''?>"><span class="s-icon">📊</span><span>Dashboard</span></a>
    <a href="/techstock/employee/products.php" class="<?=$pg=='products.php'?'active':''?>"><span class="s-icon">💻</span><span>Products</span></a>
    <a href="/techstock/employee/add_product.php" class="<?=$pg=='add_product.php'?'active':''?>"><span class="s-icon">➕</span><span>Add Product</span></a>
    <a href="/techstock/employee/orders.php" class="<?=$pg=='orders.php'?'active':''?>"><span class="s-icon">📦</span><span>Orders <?=$unread_orders>0?"<span class='badge badge-red' style='margin-left:4px'>$unread_orders</span>":''?></span></a>
  </div>
  <?php endif; ?>

  <div class="sidebar-footer">
    <div class="user-pill">
      <div class="user-av"><?=strtoupper(substr($_SESSION['full_name']??'U',0,1))?></div>
      <div>
        <div class="user-name"><?=htmlspecialchars($_SESSION['full_name']??'')?></div>
        <div class="user-role"><?=ucfirst($_SESSION['role']??'')?></div>
      </div>
    </div>
    <a href="/techstock/logout.php" class="logout-link">⎋ <span>Sign Out</span></a>
  </div>
</nav>
