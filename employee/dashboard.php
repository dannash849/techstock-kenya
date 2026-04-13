<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireEmployee();
$page_title = 'Employee Dashboard';
$uid = $_SESSION['user_id'];
$shop = getEmployeeShop($conn, $uid);
if(!$shop) { echo "<p style='color:red;padding:20px'>You are not assigned to any shop. Contact your manager.</p>"; exit(); }
$_SESSION['shop_id'] = $shop['id'];
$sid = $shop['id'];

$prod_count = $conn->query("SELECT COUNT(*) as c FROM products WHERE shop_id=$sid AND status='available'")->fetch_assoc()['c'];
$sold_count = $conn->query("SELECT COUNT(*) as c FROM products WHERE shop_id=$sid AND status='sold'")->fetch_assoc()['c'];
$my_products= $conn->query("SELECT COUNT(*) as c FROM products WHERE shop_id=$sid AND added_by=$uid")->fetch_assoc()['c'];
$orders     = $conn->query("SELECT COUNT(*) as c FROM orders WHERE shop_id=$sid AND status='pending'")->fetch_assoc()['c'];
$recent_products=$conn->query("SELECT * FROM products WHERE shop_id=$sid ORDER BY created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$status_colors=['available'=>'badge-green','reserved'=>'badge-yellow','sold'=>'badge-red'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar">
    <div>
      <div class="dash-title">My Dashboard</div>
      <div class="dash-sub">🏬 <?=htmlspecialchars($shop['name'])?> • <?=htmlspecialchars($shop['location']??'')?></div>
    </div>
    <a href="add_product.php" class="btn btn-primary">+ Add Product</a>
  </div>

  <div class="alert alert-info">👨‍💻 You are assigned to <strong><?=htmlspecialchars($shop['name'])?></strong>. You can only manage products in this shop.</div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">💻</span><span class="badge badge-green">Live</span></div><div class="stat-val"><?=$prod_count?></div><div class="stat-label">Available Products</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">🔒</span><span class="badge badge-red">Sold</span></div><div class="stat-val"><?=$sold_count?></div><div class="stat-label">Sold Products</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">➕</span><span class="badge badge-blue">Mine</span></div><div class="stat-val"><?=$my_products?></div><div class="stat-label">My Additions</div></div>
    <div class="stat-card"><div class="stat-top"><span class="stat-icon">📦</span><span class="badge badge-yellow">Pending</span></div><div class="stat-val"><?=$orders?></div><div class="stat-label">Pending Orders</div></div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Recent Products in My Shop</div><a href="products.php" class="btn btn-ghost btn-sm">View All</a></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Product</th><th>Price</th><th>Condition</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach($recent_products as $p): $sold=$p['status']==='sold'; $mine=$p['added_by']==$uid; ?>
        <tr class="<?=$sold?'locked-row':''?>">
          <td>
            <div style="color:var(--white);font-size:0.875rem;font-weight:500"><?=htmlspecialchars($p['title'])?></div>
            <div style="color:var(--text2);font-size:0.72rem"><?=$mine?'Added by you':'Added by colleague'?></div>
          </td>
          <td style="color:var(--white);font-weight:600"><?=formatPrice($p['price'])?></td>
          <td><span class="badge badge-gray"><?=ucfirst($p['condition_type'])?></span></td>
          <td><span class="badge <?=$status_colors[$p['status']]?>"><?=$sold?'🔒 ':''?><?=ucfirst($p['status'])?></span></td>
          <td>
            <?php if(!$sold && $mine): ?>
            <a href="edit_product.php?id=<?=$p['id']?>" class="btn btn-ghost btn-sm">Edit</a>
            <?php elseif($sold): ?>
            <span class="locked-badge">🔒 Locked</span>
            <?php else: ?>
            <span style="color:var(--text3);font-size:0.75rem">View only</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="/techstock/js/main.js"></script></body></html>
