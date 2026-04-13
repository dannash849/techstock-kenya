<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireOwner();
$page_title = 'Orders';
$uid = $_SESSION['user_id'];
$msg = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $stmt=$conn->prepare("UPDATE orders o JOIN shops s ON o.shop_id=s.id SET o.status=? WHERE o.id=? AND s.owner_id=?");
    $stmt->bind_param("sii",$_POST['status'],$_POST['order_id'],$uid);
    $stmt->execute() ? $msg="Order updated." : null;
}

$orders=$conn->query("SELECT o.*, p.title as product_title, p.price, sh.name as shop_name FROM orders o JOIN products p ON o.product_id=p.id JOIN shops sh ON o.shop_id=sh.id JOIN shops s ON o.shop_id=s.id WHERE s.owner_id=$uid ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$status_colors=['pending'=>'badge-yellow','confirmed'=>'badge-blue','delivered'=>'badge-green','cancelled'=>'badge-red'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar"><div class="dash-title">Orders</div></div>
  <?php if($msg): ?><div class="alert alert-success">✅ <?=$msg?></div><?php endif; ?>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Product</th><th>Buyer</th><th>Phone</th><th>Shop</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
        <tbody>
        <?php if(empty($orders)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text2)">No orders yet</td></tr>
        <?php else: foreach($orders as $o): ?>
        <tr>
          <td style="color:var(--text2)">#<?=$o['id']?></td>
          <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($o['product_title'])?></td>
          <td><div style="font-weight:500"><?=htmlspecialchars($o['buyer_name'])?></div></td>
          <td><a href="tel:<?=$o['buyer_phone']?>" style="color:var(--blue)"><?=htmlspecialchars($o['buyer_phone'])?></a></td>
          <td style="color:var(--text2)"><?=htmlspecialchars($o['shop_name'])?></td>
          <td><span class="badge <?=$status_colors[$o['status']]?>"><?=ucfirst($o['status'])?></span></td>
          <td style="color:var(--text2)"><?=date('d M Y',strtotime($o['created_at']))?></td>
          <td>
            <form method="POST" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="order_id" value="<?=$o['id']?>">
              <select name="status" style="padding:5px 8px;font-size:0.78rem">
                <?php foreach(['pending','confirmed','delivered','cancelled'] as $s): ?>
                <option value="<?=$s?>" <?=$o['status']==$s?'selected':''?>><?=ucfirst($s)?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="/techstock/js/main.js"></script></body></html>
