<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireOwner();
$page_title = 'My Shops';
$uid = $_SESSION['user_id'];
$msg = ''; $err = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    if($_POST['action']==='add') {
        $stmt=$conn->prepare("INSERT INTO shops (owner_id,name,location,address,phone,whatsapp,email,description) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isssssss",$uid,$_POST['name'],$_POST['location'],$_POST['address'],$_POST['phone'],$_POST['whatsapp'],$_POST['email'],$_POST['description']);
        $stmt->execute() ? ($msg="Shop created!") && auditLog($conn,'shop_created',$_POST['name']) : $err=$conn->error;
    }
    if($_POST['action']==='toggle') {
        $s=$conn->query("SELECT status FROM shops WHERE id=".(int)$_POST['shop_id']." AND owner_id=$uid")->fetch_assoc();
        $new=$s['status']==='active'?'inactive':'active';
        $conn->query("UPDATE shops SET status='$new' WHERE id=".(int)$_POST['shop_id']." AND owner_id=$uid");
        $msg="Shop status updated.";
    }
    if($_POST['action']==='delete') {
        $conn->query("DELETE FROM shops WHERE id=".(int)$_POST['shop_id']." AND owner_id=$uid");
        $msg="Shop deleted."; auditLog($conn,'shop_deleted',"ID:".$_POST['shop_id']);
    }
}
$shops=$conn->query("SELECT s.*, COUNT(DISTINCT p.id) as prod_count, COUNT(DISTINCT se.id) as emp_count FROM shops s LEFT JOIN products p ON p.shop_id=s.id LEFT JOIN shop_employees se ON se.shop_id=s.id WHERE s.owner_id=$uid GROUP BY s.id ORDER BY s.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar">
    <div><div class="dash-title">My Shops</div><div class="dash-sub"><?=count($shops)?> shops total</div></div>
    <button class="btn btn-primary" onclick="openModal('addShopModal')">+ New Shop</button>
  </div>
  <?php if($msg): ?><div class="alert alert-success">✅ <?=$msg?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-error">❌ <?=$err?></div><?php endif; ?>

  <?php if(empty($shops)): ?>
  <div style="text-align:center;padding:80px 20px">
    <div style="font-size:4rem;margin-bottom:16px">🏬</div>
    <h3 style="color:var(--white);margin-bottom:8px">No shops yet</h3>
    <p style="color:var(--text2);margin-bottom:20px">Create your first shop to start listing PCs</p>
    <button class="btn btn-primary" onclick="openModal('addShopModal')">Create First Shop</button>
  </div>
  <?php else: ?>
  <div class="grid-3">
    <?php foreach($shops as $s): ?>
    <div class="shop-card">
      <div class="shop-header">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div class="shop-name"><?=htmlspecialchars($s['name'])?></div>
            <div class="shop-location">📍 <?=htmlspecialchars($s['location']??'—')?></div>
          </div>
          <span class="badge <?=$s['status']==='active'?'badge-green':'badge-red' ?>"><?=ucfirst($s['status'])?></span>
        </div>
      </div>
      <div class="shop-body">
        <div class="shop-stat"><span style="color:var(--text2)">Products</span><span style="color:var(--white);font-weight:600"><?=$s['prod_count']?></span></div>
        <div class="shop-stat"><span style="color:var(--text2)">Employees</span><span style="color:var(--white);font-weight:600"><?=$s['emp_count']?></span></div>
        <?php if($s['phone']): ?><div class="shop-stat"><span style="color:var(--text2)">Phone</span><span style="color:var(--white)"><?=htmlspecialchars($s['phone'])?></span></div><?php endif; ?>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
          <a href="products.php?shop=<?=$s['id']?>" class="btn btn-ghost btn-sm">💻 Products</a>
          <a href="employees.php?shop=<?=$s['id']?>" class="btn btn-ghost btn-sm">👨‍💻 Staff</a>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="shop_id" value="<?=$s['id']?>">
            <button type="submit" class="btn btn-ghost btn-sm"><?=$s['status']==='active'?'Deactivate':'Activate'?></button>
          </form>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="shop_id" value="<?=$s['id']?>">
            <button type="submit" class="btn btn-danger btn-sm confirm-btn" data-confirm="Delete this shop and all its data?">Del</button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
</div>

<div class="modal-overlay" id="addShopModal">
<div class="modal">
  <div class="modal-header"><div class="modal-title">🏬 Create New Shop</div><button class="modal-close" onclick="closeModal('addShopModal')">✕</button></div>
  <form method="POST"><input type="hidden" name="action" value="add">
    <div class="form-grid">
      <div class="form-group form-full"><label>Shop Name *</label><input type="text" name="name" required placeholder="e.g. TechHub Nairobi"></div>
      <div class="form-group"><label>Location *</label><input type="text" name="location" required placeholder="e.g. Nairobi CBD"></div>
      <div class="form-group"><label>Phone</label><input type="tel" name="phone" placeholder="0712345678"></div>
      <div class="form-group"><label>WhatsApp (with country code)</label><input type="text" name="whatsapp" placeholder="254712345678"></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="shop@example.com"></div>
      <div class="form-group form-full"><label>Address</label><input type="text" name="address" placeholder="Building, Floor, Street"></div>
      <div class="form-group form-full"><label>Description</label><textarea name="description" placeholder="Brief description of your shop..."></textarea></div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
      <button type="button" class="btn btn-ghost" onclick="closeModal('addShopModal')">Cancel</button>
      <button type="submit" class="btn btn-primary">Create Shop →</button>
    </div>
  </form>
</div></div>
<script src="/techstock/js/main.js"></script></body></html>
