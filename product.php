<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$product = $conn->query("SELECT p.*, s.name as shop_name, s.location as shop_location, s.phone as shop_phone, s.whatsapp as shop_wa, s.description as shop_desc, u.full_name as seller_name FROM products p JOIN shops s ON p.shop_id=s.id JOIN users u ON s.owner_id=u.id WHERE p.id=$id")->fetch_assoc();
if(!$product) { header("Location: marketplace.php"); exit(); }
// Increment views
$conn->query("UPDATE products SET views=views+1 WHERE id=$id");
$page_title = htmlspecialchars($product['title']).' — TechStock Kenya';
$msg = ''; $err = '';

// Handle order request
if($_SERVER['REQUEST_METHOD']==='POST') {
    $bname  = trim($_POST['buyer_name']??'');
    $bphone = trim($_POST['buyer_phone']??'');
    $bemail = trim($_POST['buyer_email']??'');
    $bmsg   = trim($_POST['message']??'');
    if($bname && $bphone) {
        $buyer_id = isLoggedIn() ? $_SESSION['user_id'] : null;
        $stmt = $conn->prepare("INSERT INTO orders (product_id,shop_id,buyer_id,buyer_name,buyer_phone,buyer_email,message) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("iiissss",$id,$product['shop_id'],$buyer_id,$bname,$bphone,$bemail,$bmsg);
        $stmt->execute() ? $msg="Order request sent! The seller will contact you soon." : $err="Failed. Please try again.";
    } else { $err="Name and phone are required."; }
}
$cond_badges = ['new'=>'badge-new','used'=>'badge-used','refurbished'=>'badge-refurb'];
?>
<?php include 'includes/header_public.php'; ?>
<div style="padding-top:90px;padding-bottom:60px">
  <div class="container">
    <!-- Breadcrumb -->
    <div style="margin-bottom:20px;font-size:0.82rem;color:var(--text2)">
      <a href="index.php" style="color:var(--blue)">Home</a> → <a href="marketplace.php" style="color:var(--blue)">Marketplace</a> → <?= htmlspecialchars($product['title']) ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 380px;gap:32px;align-items:start">
      <!-- Left -->
      <div>
        <!-- Image -->
        <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px;height:320px;display:flex;align-items:center;justify-content:center">
          <?php if($product['image1']): ?>
          <img src="/techstock/uploads/products/<?= htmlspecialchars($product['image1']) ?>" style="width:100%;height:320px;object-fit:cover">
          <?php else: ?>
          <div style="font-size:8rem">💻</div>
          <?php endif; ?>
        </div>

        <!-- Specs -->
        <div class="card card-body" style="margin-bottom:20px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
            <h1 style="font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;color:var(--white)"><?= htmlspecialchars($product['title']) ?></h1>
            <span class="product-badge <?= $cond_badges[$product['condition_type']] ?>" style="position:static"><?= ucfirst($product['condition_type']) ?></span>
          </div>
          <div style="font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;color:var(--white);margin-bottom:6px"><?= formatPrice($product['price']) ?></div>
          <?php if($product['original_price'] && $product['original_price']>$product['price']): ?>
          <div style="color:var(--text3);text-decoration:line-through;font-size:0.875rem;margin-bottom:12px"><?= formatPrice($product['original_price']) ?></div>
          <?php endif; ?>

          <div class="divider"></div>
          <h3 style="color:var(--white);font-weight:600;margin-bottom:14px">Specifications</h3>
          <div class="grid-2">
            <?php foreach([
              ['⚙️','Processor',$product['cpu']],
              ['🧠','RAM',$product['ram']],
              ['💾','Storage',$product['storage']],
              ['🎮','GPU',$product['gpu']],
              ['🖥','Display',$product['display']],
              ['💿','OS',$product['os']],
              ['🏷','Brand',$product['brand']],
              ['📦','Condition',ucfirst($product['condition_type'])],
            ] as [$icon,$label,$val]): if(!$val) continue; ?>
            <div style="padding:10px;background:rgba(255,255,255,0.03);border-radius:8px">
              <div style="color:var(--text2);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px"><?=$icon?> <?=$label?></div>
              <div style="color:var(--white);font-size:0.875rem;font-weight:500"><?= htmlspecialchars($val) ?></div>
            </div>
            <?php endforeach; ?>
          </div>

          <?php if($product['description']): ?>
          <div class="divider"></div>
          <h3 style="color:var(--white);font-weight:600;margin-bottom:10px">Description</h3>
          <p style="color:var(--text2);font-size:0.875rem;line-height:1.7"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right -->
      <div style="position:sticky;top:90px">
        <!-- Shop info -->
        <div class="card card-body" style="margin-bottom:16px">
          <h3 style="color:var(--white);font-weight:600;margin-bottom:12px">🏬 <?= htmlspecialchars($product['shop_name']) ?></h3>
          <div style="color:var(--text2);font-size:0.82rem;margin-bottom:8px">📍 <?= htmlspecialchars($product['shop_location']) ?></div>
          <?php if($product['shop_phone']): ?>
          <div style="color:var(--text2);font-size:0.82rem;margin-bottom:12px">📞 <?= htmlspecialchars($product['shop_phone']) ?></div>
          <?php endif; ?>
          <?php if($product['shop_wa']): ?>
          <a href="https://wa.me/<?= $product['shop_wa'] ?>?text=Hi, I'm interested in <?= urlencode($product['title']) ?> listed on TechStock Kenya for <?= formatPrice($product['price']) ?>" target="_blank" class="btn btn-whatsapp btn-block" style="margin-bottom:8px">💬 WhatsApp Seller</a>
          <?php endif; ?>
          <?php if($product['shop_phone']): ?>
          <a href="tel:<?= $product['shop_phone'] ?>" class="btn btn-outline btn-block">📞 Call Seller</a>
          <?php endif; ?>
        </div>

        <!-- Order form -->
        <?php if($product['status'] === 'available'): ?>
        <div class="card card-body">
          <h3 style="color:var(--white);font-weight:600;margin-bottom:16px">📋 Request This PC</h3>
          <?php if($msg): ?><div class="alert alert-success">✅ <?= $msg ?></div><?php endif; ?>
          <?php if($err): ?><div class="alert alert-error">❌ <?= $err ?></div><?php endif; ?>
          <form method="POST">
            <div class="form-group"><label>Your Name *</label><input type="text" name="buyer_name" required placeholder="John Doe" value="<?= htmlspecialchars($_SESSION['full_name']??'') ?>"></div>
            <div class="form-group"><label>Phone *</label><input type="tel" name="buyer_phone" required placeholder="07XX XXX XXX"></div>
            <div class="form-group"><label>Email</label><input type="email" name="buyer_email" placeholder="optional"></div>
            <div class="form-group"><label>Message</label><textarea name="message" rows="3" placeholder="Any questions or requests..."></textarea></div>
            <button type="submit" class="btn btn-primary btn-block">Send Request →</button>
          </form>
        </div>
        <?php else: ?>
        <div class="card card-body" style="text-align:center;padding:30px">
          <div style="font-size:3rem;margin-bottom:12px">🔒</div>
          <div style="color:var(--red);font-weight:600;margin-bottom:6px">Product <?= ucfirst($product['status']) ?></div>
          <p style="color:var(--text2);font-size:0.82rem">This product is no longer available.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer_public.php'; ?>
