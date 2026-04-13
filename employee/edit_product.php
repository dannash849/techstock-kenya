<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireEmployee();
$uid = $_SESSION['user_id'];
$shop = getEmployeeShop($conn, $uid);
$sid = $shop['id'];
$pid = (int)($_GET['id']??0);
$product = $conn->query("SELECT * FROM products WHERE id=$pid AND shop_id=$sid AND added_by=$uid")->fetch_assoc();
if(!$product) { echo "<p style='color:red;padding:20px'>Product not found or you don't have permission to edit it.</p>"; exit(); }
if($product['status']==='sold') { echo "<p style='color:red;padding:20px'>🔒 This product is SOLD and cannot be edited.</p>"; exit(); }
$page_title = 'Edit Product';
$msg=''; $err='';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $title=trim($_POST['title']??''); $price=floatval($_POST['price']??0);
    if($title && $price>0) {
        $stmt=$conn->prepare("UPDATE products SET title=?,brand=?,cpu=?,ram=?,storage=?,gpu=?,display=?,os=?,condition_type=?,price=?,original_price=?,description=? WHERE id=? AND added_by=? AND status!='sold'");
        $stmt->bind_param("sssssssssddsii",$title,$_POST['brand']??'',$_POST['cpu']??'',$_POST['ram']??'',$_POST['storage']??'',$_POST['gpu']??'',$_POST['display']??'',$_POST['os']??'',$_POST['condition_type']??'used',$price,floatval($_POST['original_price']??0),$_POST['description']??'',$pid,$uid);
        $stmt->execute() ? ($msg="Product updated!") && auditLog($conn,'product_edited',"ID:$pid Title:$title") : $err=$conn->error;
    } else { $err="Title and price required."; }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar"><div class="dash-title">Edit Product</div><a href="products.php" class="btn btn-ghost">← Back</a></div>
  <?php if($msg): ?><div class="alert alert-success">✅ <?=$msg?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-error">❌ <?=$err?></div><?php endif; ?>
  <form method="POST">
    <div class="grid-2" style="gap:24px;align-items:start">
      <div class="card card-body">
        <h3 style="color:var(--white);font-weight:600;margin-bottom:18px">📋 Basic Info</h3>
        <div class="form-group"><label>Title *</label><input type="text" name="title" required value="<?=htmlspecialchars($product['title'])?>"></div>
        <div class="form-grid">
          <div class="form-group"><label>Brand</label><input type="text" name="brand" value="<?=htmlspecialchars($product['brand']??'')?>"></div>
          <div class="form-group"><label>Condition</label>
            <select name="condition_type">
              <?php foreach(['new','used','refurbished'] as $c): ?>
              <option value="<?=$c?>" <?=$product['condition_type']==$c?'selected':''?>><?=ucfirst($c)?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Price (KSh) *</label><input type="number" name="price" required value="<?=$product['price']?>"></div>
          <div class="form-group"><label>Original Price</label><input type="number" name="original_price" value="<?=$product['original_price']??''?>"></div>
        </div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="4"><?=htmlspecialchars($product['description']??'')?></textarea></div>
      </div>
      <div class="card card-body">
        <h3 style="color:var(--white);font-weight:600;margin-bottom:18px">⚙️ Specifications</h3>
        <div class="form-group"><label>CPU</label><input type="text" name="cpu" value="<?=htmlspecialchars($product['cpu']??'')?>"></div>
        <div class="form-group"><label>RAM</label><input type="text" name="ram" value="<?=htmlspecialchars($product['ram']??'')?>"></div>
        <div class="form-group"><label>Storage</label><input type="text" name="storage" value="<?=htmlspecialchars($product['storage']??'')?>"></div>
        <div class="form-group"><label>GPU</label><input type="text" name="gpu" value="<?=htmlspecialchars($product['gpu']??'')?>"></div>
        <div class="form-group"><label>Display</label><input type="text" name="display" value="<?=htmlspecialchars($product['display']??'')?>"></div>
        <div class="form-group"><label>OS</label><input type="text" name="os" value="<?=htmlspecialchars($product['os']??'')?>"></div>
        <button type="submit" class="btn btn-primary btn-block">Save Changes →</button>
      </div>
    </div>
  </form>
</main>
</div>
<script src="/techstock/js/main.js"></script></body></html>
