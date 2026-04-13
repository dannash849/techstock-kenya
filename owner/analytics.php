<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireOwner();
$page_title = 'Analytics';
$uid = $_SESSION['user_id'];

$total_products=$conn->query("SELECT COUNT(*) as c FROM products p JOIN shops s ON p.shop_id=s.id WHERE s.owner_id=$uid")->fetch_assoc()['c'];
$available=$conn->query("SELECT COUNT(*) as c FROM products p JOIN shops s ON p.shop_id=s.id WHERE s.owner_id=$uid AND p.status='available'")->fetch_assoc()['c'];
$sold=$conn->query("SELECT COUNT(*) as c FROM products p JOIN shops s ON p.shop_id=s.id WHERE s.owner_id=$uid AND p.status='sold'")->fetch_assoc()['c'];
$total_orders=$conn->query("SELECT COUNT(*) as c FROM orders o JOIN shops s ON o.shop_id=s.id WHERE s.owner_id=$uid")->fetch_assoc()['c'];
$pending_orders=$conn->query("SELECT COUNT(*) as c FROM orders o JOIN shops s ON o.shop_id=s.id WHERE s.owner_id=$uid AND o.status='pending'")->fetch_assoc()['c'];

$shop_perf=$conn->query("SELECT s.name, COUNT(p.id) as total_products, SUM(p.status='available') as available, SUM(p.status='sold') as sold FROM shops s LEFT JOIN products p ON p.shop_id=s.id WHERE s.owner_id=$uid GROUP BY s.id ORDER BY sold DESC");

$top_products=$conn->query("SELECT p.title, p.price, p.views, p.status, s.name as shop_name FROM products p JOIN shops s ON p.shop_id=s.id WHERE s.owner_id=$uid ORDER BY p.views DESC LIMIT 10");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar"><div class="dash-title">Analytics</div></div>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">💻</span><span class="badge badge-blue">Total</span></div><div class="stat-val"><?=$total_products?></div><div class="stat-label">Total Products</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">✅</span><span class="badge badge-green">Live</span></div><div class="stat-val"><?=$available?></div><div class="stat-label">Available</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">🔒</span><span class="badge badge-red">Sold</span></div><div class="stat-val"><?=$sold?></div><div class="stat-label">Sold</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">📦</span><span class="badge badge-yellow">Pending</span></div><div class="stat-val"><?=$pending_orders?></div><div class="stat-label">Pending Orders</div></div>
  </div>

  <div class="grid-2">
    <div class="card card-body">
      <h3 style="color:var(--white);font-weight:600;margin-bottom:16px">🏬 Shop Performance</h3>
      <?php while($r=$shop_perf->fetch_assoc()): ?>
      <div style="padding:12px 0;border-bottom:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
          <span style="color:var(--white);font-weight:500"><?=htmlspecialchars($r['name'])?></span>
          <span class="badge badge-green"><?=$r['sold']?> sold</span>
        </div>
        <div style="height:6px;background:rgba(255,255,255,0.06);border-radius:3px">
          <div style="height:6px;background:var(--blue);border-radius:3px;width:<?=$r['total_products']>0?round($r['available']/$r['total_products']*100):0?>%"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:4px">
          <span style="color:var(--text2);font-size:0.72rem"><?=$r['available']?> available of <?=$r['total_products']?> total</span>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <div class="card card-body">
      <h3 style="color:var(--white);font-weight:600;margin-bottom:16px">👀 Most Viewed Products</h3>
      <?php while($r=$top_products->fetch_assoc()): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
        <div>
          <div style="color:var(--white);font-size:0.82rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px"><?=htmlspecialchars($r['title'])?></div>
          <div style="color:var(--text2);font-size:0.72rem"><?=formatPrice($r['price'])?> • <?=htmlspecialchars($r['shop_name'])?></div>
        </div>
        <div style="text-align:right">
          <div style="color:var(--cyan);font-weight:600"><?=$r['views']?> views</div>
          <span class="badge <?=$r['status']==='sold'?'badge-red':'badge-green' ?>" style="font-size:0.65rem"><?=$r['status']?></span>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</main>
</div>
<script src="/techstock/js/main.js"></script></body></html>
