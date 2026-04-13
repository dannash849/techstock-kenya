<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireEmployee();
$uid = $_SESSION['user_id'];
$shop = getEmployeeShop($conn, $uid);
if(!$shop) { header("Location: dashboard.php"); exit(); }
$sid = $shop['id'];
$page_title = 'Shop Products';
$products=$conn->query("SELECT p.*, u.full_name as added_by_name FROM products p JOIN users u ON p.added_by=u.id WHERE p.shop_id=$sid ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$status_colors=['available'=>'badge-green','reserved'=>'badge-yellow','sold'=>'badge-red'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar">
    <div><div class="dash-title">Shop Products</div><div class="dash-sub">🏬 <?=htmlspecialchars($shop['name'])?> — <?=count($products)?> products</div></div>
    <a href="add_product.php" class="btn btn-primary">+ Add Product</a>
  </div>
  <div class="alert alert-info">👨‍💻 You can only edit products YOU added. Sold products are permanently locked.</div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Product</th><th>Price</th><th>Specs</th><th>Condition</th><th>Status</th><th>Added By</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach($products as $p): $sold=$p['status']==='sold'; $mine=$p['added_by']==$uid; ?>
        <tr class="<?=$sold?'locked-row':''?>">
          <td>
            <div style="color:var(--white);font-size:0.875rem;font-weight:500;max-width:200px"><?=htmlspecialchars($p['title'])?></div>
            <?php if($p['brand']): ?><div style="color:var(--text2);font-size:0.72rem"><?=htmlspecialchars($p['brand'])?></div><?php endif; ?>
          </td>
          <td style="color:var(--white);font-weight:600"><?=formatPrice($p['price'])?></td>
          <td style="color:var(--text2);font-size:0.78rem">
            <?php if($p['cpu']): ?><div>⚙️ <?=htmlspecialchars(substr($p['cpu'],0,20))?></div><?php endif; ?>
            <?php if($p['ram']): ?><div>🧠 <?=htmlspecialchars($p['ram'])?></div><?php endif; ?>
          </td>
          <td><span class="badge badge-gray"><?=ucfirst($p['condition_type'])?></span></td>
          <td><span class="badge <?=$status_colors[$p['status']]?>"><?=$sold?'🔒 ':''?><?=ucfirst($p['status'])?></span></td>
          <td>
            <div style="font-size:0.78rem;color:<?=$mine?'var(--cyan)':'var(--text2)'?>"><?=$mine?'You':htmlspecialchars($p['added_by_name'])?></div>
          </td>
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
