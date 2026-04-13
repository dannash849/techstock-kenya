<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
$page_title = 'Shops — TechStock Kenya';
$shops = $conn->query("SELECT s.*, u.full_name as owner_name, COUNT(p.id) as product_count FROM shops s JOIN users u ON s.owner_id=u.id LEFT JOIN products p ON p.shop_id=s.id AND p.status='available' WHERE s.status='active' GROUP BY s.id ORDER BY s.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header_public.php'; ?>
<div style="padding-top:90px;padding-bottom:60px">
  <div class="container">
    <div style="margin-bottom:36px">
      <h1 style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--white);margin-bottom:6px">All Shops</h1>
      <p style="color:var(--text2)"><?= count($shops) ?> verified shops</p>
    </div>
    <div class="grid-3">
      <?php foreach($shops as $s): ?>
      <div class="shop-card">
        <div class="shop-header">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
            <div style="width:44px;height:44px;background:rgba(59,130,246,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.4rem">🏬</div>
            <div>
              <div class="shop-name"><?= htmlspecialchars($s['name']) ?></div>
              <div class="shop-location">📍 <?= htmlspecialchars($s['location']) ?></div>
            </div>
          </div>
          <?php if($s['description']): ?>
          <p style="color:var(--text2);font-size:0.8rem;line-height:1.5"><?= htmlspecialchars(substr($s['description'],0,80)) ?>...</p>
          <?php endif; ?>
        </div>
        <div class="shop-body">
          <div class="shop-stat"><span style="color:var(--text2)">Available PCs</span><span style="color:var(--white);font-weight:600"><?= $s['product_count'] ?></span></div>
          <?php if($s['phone']): ?><div class="shop-stat"><span style="color:var(--text2)">Phone</span><span style="color:var(--white)"><?= htmlspecialchars($s['phone']) ?></span></div><?php endif; ?>
          <div style="margin-top:12px;display:flex;gap:8px">
            <a href="marketplace.php?shop=<?= $s['id'] ?>" class="btn btn-primary btn-sm btn-block">View Products</a>
            <?php if($s['whatsapp']): ?>
            <a href="https://wa.me/<?= $s['whatsapp'] ?>" target="_blank" class="btn btn-whatsapp btn-sm">💬</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer_public.php'; ?>
