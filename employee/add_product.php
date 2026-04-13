<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireEmployee();
$uid = $_SESSION['user_id'];
$shop = getEmployeeShop($conn, $uid);
if(!$shop) { header("Location: dashboard.php"); exit(); }
$sid = $shop['id'];
$page_title = 'Add Product';
$msg = ''; $err = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $title = trim($_POST['title']??'');
    $price = floatval($_POST['price']??0);
    if($title && $price > 0) {
        // Handle image upload
        $img1 = '';
        if(isset($_FILES['image1']) && $_FILES['image1']['error']===0) {
            $ext = pathinfo($_FILES['image1']['name'], PATHINFO_EXTENSION);
            $img1 = uniqid().'.'.$ext;
            move_uploaded_file($_FILES['image1']['tmp_name'], '../uploads/products/'.$img1);
        }
        $stmt=$conn->prepare("INSERT INTO products (shop_id,added_by,title,brand,cpu,ram,storage,gpu,display,os,condition_type,price,original_price,description,image1) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iisssssssssddss",
            $sid,$uid,$title,$_POST['brand']??'',$_POST['cpu']??'',$_POST['ram']??'',$_POST['storage']??'',$_POST['gpu']??'',$_POST['display']??'',$_POST['os']??'',$_POST['condition_type']??'used',$price,floatval($_POST['original_price']??0),$_POST['description']??'',$img1);
        if($stmt->execute()) {
            $pid = $conn->insert_id;
            $msg="Product added successfully!";
            auditLog($conn,'product_added',"Title: $title, Shop: {$shop['name']}, ID: $pid");
        } else { $err="Failed: ".$conn->error; }
    } else { $err="Title and price are required."; }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar">
    <div><div class="dash-title">Add New Product</div><div class="dash-sub">🏬 Adding to: <?=htmlspecialchars($shop['name'])?></div></div>
    <a href="products.php" class="btn btn-ghost">← Back</a>
  </div>
  <?php if($msg): ?><div class="alert alert-success">✅ <?=$msg?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-error">❌ <?=$err?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="grid-2" style="gap:24px;align-items:start">
      <!-- Basic Info -->
      <div class="card card-body">
        <h3 style="color:var(--white);font-weight:600;margin-bottom:18px">📋 Basic Information</h3>
        <div class="form-group"><label>Product Title *</label><input type="text" name="title" required placeholder="e.g. HP EliteBook 840 G6 i5 8GB 256SSD"></div>
        <div class="form-grid">
          <div class="form-group"><label>Brand</label><input type="text" name="brand" placeholder="HP, Dell, Lenovo..."></div>
          <div class="form-group"><label>Condition *</label>
            <select name="condition_type" required>
              <option value="used">Used</option>
              <option value="new">New</option>
              <option value="refurbished">Refurbished</option>
            </select>
          </div>
          <div class="form-group"><label>Selling Price (KSh) *</label><input type="number" name="price" required placeholder="45000"></div>
          <div class="form-group"><label>Original Price (KSh)</label><input type="number" name="original_price" placeholder="60000"></div>
        </div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="4" placeholder="Describe the laptop condition, any defects, accessories included..."></textarea></div>
        <div class="form-group"><label>Product Image</label><input type="file" name="image1" accept="image/*" style="background:rgba(255,255,255,0.04)"></div>
      </div>

      <!-- Specifications -->
      <div class="card card-body">
        <h3 style="color:var(--white);font-weight:600;margin-bottom:18px">⚙️ Specifications</h3>
        <div class="form-group"><label>Processor (CPU)</label><input type="text" name="cpu" placeholder="Intel Core i5-8265U 1.6GHz"></div>
        <div class="form-group"><label>RAM</label><input type="text" name="ram" placeholder="8GB DDR4"></div>
        <div class="form-group"><label>Storage</label><input type="text" name="storage" placeholder="256GB SSD"></div>
        <div class="form-group"><label>Graphics (GPU)</label><input type="text" name="gpu" placeholder="Intel UHD 620"></div>
        <div class="form-group"><label>Display</label><input type="text" name="display" placeholder='14" FHD 1920x1080'></div>
        <div class="form-group"><label>Operating System</label><input type="text" name="os" placeholder="Windows 11 Pro"></div>
        <div class="alert alert-warning">⚠️ Once this product is marked as SOLD by the owner, it cannot be edited or deleted. Make sure all details are correct.</div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Add Product →</button>
      </div>
    </div>
  </form>
</main>
</div>
<script src="/techstock/js/main.js"></script></body></html>
