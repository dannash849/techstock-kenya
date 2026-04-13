<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireOwner();
$page_title = 'Owner Dashboard — TechStock';
$uid = $_SESSION['user_id'];

$shops_count    = $conn->query("SELECT COUNT(*) as c FROM shops WHERE owner_id=$uid")->fetch_assoc()['c'];
$products_count = $conn->query("SELECT COUNT(*) as c FROM products p JOIN shops s ON p.shop_id=s.id WHERE s.owner_id=$uid AND p.status='available'")->fetch_assoc()['c'];
$sold_count     = $conn->query("SELECT COUNT(*) as c FROM products p JOIN shops s ON p.shop_id=s.id WHERE s.owner_id=$uid AND p.status='sold'")->fetch_assoc()['c'];
$orders_pending = $conn->query("SELECT COUNT(*) as c FROM orders o JOIN shops s ON o.shop_id=s.id WHERE s.owner_id=$uid AND o.status='pending'")->fetch_assoc()['c'];
$employees_count= $conn->query("SELECT COUNT(*) as c FROM shop_employees se JOIN shops s ON se.shop_id=s.id WHERE s.owner_id=$uid")->fetch_assoc()['c'];

$recent_orders  = $conn->query("SELECT o.*, p.title as product_title, sh.name as shop_name FROM orders o JOIN products p ON o.product_id=p.id JOIN shops sh ON o.shop_id=sh.id JOIN shops s ON o.shop_id=s.id WHERE s.owner_id=$uid ORDER BY o.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$my_shops       = $conn->query("SELECT s.*, COUNT(p.id) as prod_count FROM shops s LEFT JOIN products p ON p.shop_id=s.id AND p.status='available' WHERE s.owner_id=$uid GROUP BY s.id ORDER BY s.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?=$page_title?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css">
</head>
<body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar">
    <div>
      <div class="dash-title">Dashboard</div>
      <div class="dash-sub">Welcome back, <?= htmlspecialchars(explode(' ',$_SESSION['full_name'])[0]) ?> 👋 — <?= date('l, d F Y') ?></div>
    </div>
    <a href="shops.php" class="btn btn-primary btn-sm">+ New Shop</a>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">🏬</span><span class="badge badge-blue">Shops</span></div><div class="stat-val"><?=$shops_count?></div><div class="stat-label">My Shops</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">💻</span><span class="badge badge-green">Live</span></div><div class="stat-val"><?=$products_count?></div><div class="stat-label">Available Products</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">✅</span><span class="badge badge-cyan">Sold</span></div><div class="stat-val"><?=$sold_count?></div><div class="stat-label">Products Sold</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">📦</span><span class="badge badge-yellow">Pending</span></div><div class="stat-val"><?=$orders_pending?></div><div class="stat-label">Pending Orders</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">👨‍💻</span><span class="badge badge-purple">Team</span></div><div class="stat-val"><?=$employees_count?></div><div class="stat-label">Employees</div></div>
  </div>

  <div class="grid-2">
    <!-- My Shops -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">🏬 My Shops</div>
        <a href="shops.php" class="btn btn-ghost btn-sm">Manage</a>
      </div>
      <div class="card-body" style="padding-top:0">
        <?php if(empty($my_shops)): ?>
        <div style="text-align:center;padding:30px">
          <p style="color:var(--text2);margin-bottom:14px">No shops yet</p>
          <a href="shops.php" class="btn btn-primary btn-sm">Create Your First Shop</a>
        </div>
        <?php else: foreach($my_shops as $s): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:rgba(59,130,246,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center">🏬</div>
            <div>
              <div style="color:var(--white);font-size:0.875rem;font-weight:500"><?=htmlspecialchars($s['name'])?></div>
              <div style="color:var(--text2);font-size:0.75rem">📍 <?=htmlspecialchars($s['location']??'—')?> • <?=$s['prod_count']?> products</div>
            </div>
          </div>
          <span class="badge <?=$s['status']=='active'?'badge-green':'badge-red' ?>"><?=ucfirst($s['status'])?></span>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">📦 Recent Orders</div>
        <a href="orders.php" class="btn btn-ghost btn-sm">View All</a>
      </div>
      <div style="overflow-x:auto">
        <table>
          <thead><tr><th>Product</th><th>Buyer</th><th>Shop</th><th>Status</th></tr></thead>
          <tbody>
          <?php if(empty($recent_orders)): ?>
          <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text2)">No orders yet</td></tr>
          <?php else: foreach($recent_orders as $o): ?>
          <tr>
            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($o['product_title'])?></td>
            <td><div><?=htmlspecialchars($o['buyer_name'])?></div><div style="color:var(--text2);font-size:0.75rem"><?=htmlspecialchars($o['buyer_phone'])?></div></td>
            <td style="color:var(--text2)"><?=htmlspecialchars($o['shop_name'])?></td>
            <td><span class="badge <?=['pending'=>'badge-yellow','confirmed'=>'badge-blue','delivered'=>'badge-green','cancelled'=>'badge-red'][$o['status']]?>"><?=ucfirst($o['status'])?></span></td>
          </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div style="margin-top:20px">
    <div class="card card-body">
      <h3 style="color:var(--white);font-weight:600;margin-bottom:16px">⚡ Quick Actions</h3>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="shops.php" class="btn btn-ghost">🏬 New Shop</a>
        <a href="employees.php" class="btn btn-ghost">👨‍💻 Add Employee</a>
        <a href="products.php" class="btn btn-ghost">💻 View Products</a>
        <a href="orders.php" class="btn btn-ghost">📦 View Orders</a>
        <a href="audit.php" class="btn btn-ghost">🔍 Audit Logs</a>
        <a href="analytics.php" class="btn btn-ghost">📈 Analytics</a>
      </div>
    </div>
  </div>
</main>
</div>
<script src="/techstock/js/main.js"></script>
</body></html>
