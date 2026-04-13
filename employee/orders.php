<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireEmployee();
$uid = $_SESSION['user_id'];
$shop = getEmployeeShop($conn, $uid);
if(!$shop) { header("Location: dashboard.php"); exit(); }
$sid = $shop['id'];
$page_title = 'Shop Orders';
$orders=$conn->query("SELECT o.*, p.title as product_title FROM orders o JOIN products p ON o.product_id=p.id WHERE o.shop_id=$sid ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$status_colors=['pending'=>'badge-yellow','confirmed'=>'badge-blue','delivered'=>'badge-green','cancelled'=>'badge-red'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar"><div class="dash-title">Shop Orders</div><div class="dash-sub">🏬 <?=htmlspecialchars($shop['name'])?></div></div>
  <div class="alert alert-info">📦 You can view orders but only the owner can update order status.</div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Product</th><th>Buyer</th><th>Phone</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php if(empty($orders)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2)">No orders yet</td></tr>
        <?php else: foreach($orders as $o): ?>
        <tr>
          <td style="color:var(--text2)">#<?=$o['id']?></td>
          <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($o['product_title'])?></td>
          <td><?=htmlspecialchars($o['buyer_name'])?></td>
          <td><a href="tel:<?=$o['buyer_phone']?>" style="color:var(--blue)"><?=htmlspecialchars($o['buyer_phone'])?></a></td>
          <td style="color:var(--text2);max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($o['message']??'—')?></td>
          <td><span class="badge <?=$status_colors[$o['status']]?>"><?=ucfirst($o['status'])?></span></td>
          <td style="color:var(--text2)"><?=date('d M Y',strtotime($o['created_at']))?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="/techstock/js/main.js"></script></body></html>
