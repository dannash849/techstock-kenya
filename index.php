<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
$page_title = 'TechStock Kenya — Buy & Sell PCs Online';

$featured = $conn->query("SELECT p.*, s.name as shop_name, s.location as shop_location FROM products p JOIN shops s ON p.shop_id=s.id WHERE p.status='available' AND p.featured=1 ORDER BY p.created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$latest   = $conn->query("SELECT p.*, s.name as shop_name, s.location as shop_location FROM products p JOIN shops s ON p.shop_id=s.id WHERE p.status='available' ORDER BY p.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$total_products = $conn->query("SELECT COUNT(*) as c FROM products WHERE status='available'")->fetch_assoc()['c'];
$total_shops    = $conn->query("SELECT COUNT(*) as c FROM shops WHERE status='active'")->fetch_assoc()['c'];
$total_sold     = $conn->query("SELECT COUNT(*) as c FROM products WHERE status='sold'")->fetch_assoc()['c'];
$cond_badges = ['new'=>'badge-new','used'=>'badge-used','refurbished'=>'badge-refurb'];
?>
<?php include 'includes/header_public.php'; ?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-content">
    <div>
      <div class="hero-tag">🇰🇪 Kenya's #1 PC Marketplace</div>
      <h1 class="hero-title">Find Your Perfect<br><span>PC in Kenya</span></h1>
      <p class="hero-sub">Browse hundreds of laptops and desktops from verified shops across Kenya. Best prices, trusted sellers, fast delivery.</p>
      <div class="hero-btns">
        <a href="marketplace.php" class="btn btn-primary btn-lg">Browse All PCs →</a>
        <a href="register.php" class="btn btn-outline btn-lg">Open a Shop</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><div class="hero-stat-val"><?= $total_products ?>+</div><div class="hero-stat-label">PCs Available</div></div>
        <div class="hero-stat"><div class="hero-stat-val"><?= $total_shops ?>+</div><div class="hero-stat-label">Verified Shops</div></div>
        <div class="hero-stat"><div class="hero-stat-val"><?= $total_sold ?>+</div><div class="hero-stat-label">PCs Sold</div></div>
        <div class="hero-stat"><div class="hero-stat-val">47</div><div class="hero-stat-label">Counties Covered</div></div>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-mockup">
        <div class="hero-mockup-bar">
          <div class="hero-mockup-dot" style="background:#ef4444"></div>
          <div class="hero-mockup-dot" style="background:#f59e0b"></div>
          <div class="hero-mockup-dot" style="background:#10b981"></div>
        </div>
        <?php foreach(array_slice($featured,0,3) as $p): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px;background:rgba(255,255,255,0.03);border-radius:8px;margin-bottom:8px;border:1px solid var(--border)">
          <div style="width:40px;height:40px;background:rgba(59,130,246,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">💻</div>
          <div style="flex:1;min-width:0">
            <div style="color:var(--white);font-size:0.82rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['title']) ?></div>
            <div style="color:var(--text2);font-size:0.72rem"><?= htmlspecialchars($p['shop_name']) ?></div>
          </div>
          <div style="color:var(--cyan);font-weight:700;font-size:0.85rem;white-space:nowrap"><?= formatPrice($p['price']) ?></div>
        </div>
        <?php endforeach; ?>
        <div style="text-align:center;padding:10px 0">
          <a href="marketplace.php" style="color:var(--blue);font-size:0.82rem">View all listings →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED -->
<?php if(!empty($featured)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Featured</div>
      <h2 class="section-title">Top <span>Picks Today</span></h2>
      <p class="section-sub">Hand-picked laptops and desktops from our verified sellers.</p>
    </div>
    <div class="products-grid">
      <?php foreach($featured as $p): ?>
      <div class="product-card fade-up">
        <div class="product-img">
          <?php if($p['image1']): ?>
          <img src="/techstock/uploads/products/<?= htmlspecialchars($p['image1']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
          <?php else: ?>💻<?php endif; ?>
          <span class="product-badge <?= $cond_badges[$p['condition_type']] ?>"><?= ucfirst($p['condition_type']) ?></span>
          <span class="badge-featured">⭐ Featured</span>
        </div>
        <div class="product-body">
          <div class="product-shop">🏬 <?= htmlspecialchars($p['shop_name']) ?> • <?= htmlspecialchars($p['shop_location']) ?></div>
          <div class="product-title"><?= htmlspecialchars($p['title']) ?></div>
          <div class="product-specs">
            <?php if($p['cpu']): ?><div class="spec-item"><span class="spec-icon">⚙️</span><?= htmlspecialchars(substr($p['cpu'],0,20)) ?></div><?php endif; ?>
            <?php if($p['ram']): ?><div class="spec-item"><span class="spec-icon">🧠</span><?= htmlspecialchars($p['ram']) ?></div><?php endif; ?>
            <?php if($p['storage']): ?><div class="spec-item"><span class="spec-icon">💾</span><?= htmlspecialchars($p['storage']) ?></div><?php endif; ?>
            <?php if($p['gpu']): ?><div class="spec-item"><span class="spec-icon">🎮</span><?= htmlspecialchars(substr($p['gpu'],0,18)) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="product-footer">
          <div>
            <div class="product-price"><?= formatPrice($p['price']) ?></div>
            <?php if($p['original_price'] && $p['original_price'] > $p['price']): ?>
            <div class="product-original"><?= formatPrice($p['original_price']) ?></div>
            <?php endif; ?>
          </div>
          <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">View →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- HOW IT WORKS -->
<section class="section" style="background:var(--bg2)">
  <div class="container">
    <div class="section-header" style="text-align:center">
      <div class="section-tag">How It Works</div>
      <h2 class="section-title">Simple & <span>Transparent</span></h2>
    </div>
    <div class="grid-3">
      <?php foreach([
        ['🔍','Browse & Filter','Search PCs by specs, price, location or brand. Find exactly what you need.'],
        ['📞','Contact Seller','WhatsApp or call the seller directly. No middlemen, no hidden fees.'],
        ['✅','Buy with Confidence','Every seller is verified. Every product is tracked for authenticity.'],
      ] as [$icon,$title,$desc]): ?>
      <div class="card card-body" style="text-align:center">
        <div style="font-size:2.5rem;margin-bottom:14px"><?=$icon?></div>
        <h3 style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:700;color:var(--white);margin-bottom:8px"><?=$title?></h3>
        <p style="color:var(--text2);font-size:0.875rem;line-height:1.6"><?=$desc?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- LATEST -->
<section class="section">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
      <div>
        <div class="section-tag">Latest</div>
        <h2 class="section-title">Just <span>Listed</span></h2>
      </div>
      <a href="marketplace.php" class="btn btn-outline">View All →</a>
    </div>
    <div class="products-grid">
      <?php foreach($latest as $p): ?>
      <div class="product-card">
        <div class="product-img">
          <?php if($p['image1']): ?>
          <img src="/techstock/uploads/products/<?= htmlspecialchars($p['image1']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
          <?php else: ?>💻<?php endif; ?>
          <span class="product-badge <?= $cond_badges[$p['condition_type']] ?>"><?= ucfirst($p['condition_type']) ?></span>
        </div>
        <div class="product-body">
          <div class="product-shop">🏬 <?= htmlspecialchars($p['shop_name']) ?></div>
          <div class="product-title"><?= htmlspecialchars($p['title']) ?></div>
          <div class="product-specs">
            <?php if($p['cpu']): ?><div class="spec-item"><span>⚙️</span><?= htmlspecialchars(substr($p['cpu'],0,20)) ?></div><?php endif; ?>
            <?php if($p['ram']): ?><div class="spec-item"><span>🧠</span><?= htmlspecialchars($p['ram']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="product-footer">
          <div class="product-price"><?= formatPrice($p['price']) ?></div>
          <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">View</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section" style="background:var(--bg2)">
  <div class="container">
    <div style="background:linear-gradient(135deg,rgba(59,130,246,0.15),rgba(6,182,212,0.08));border:1px solid rgba(59,130,246,0.2);border-radius:20px;padding:60px;text-align:center">
      <h2 style="font-family:'Syne',sans-serif;font-size:2.2rem;font-weight:800;color:var(--white);margin-bottom:14px">Own a PC Shop?</h2>
      <p style="color:var(--text2);font-size:1rem;margin-bottom:32px;max-width:500px;margin-left:auto;margin-right:auto">List your products, manage employees, track sales and grow your business with TechStock Kenya.</p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a href="register.php" class="btn btn-primary btn-lg">Start Selling Free →</a>
        <a href="shops.php" class="btn btn-outline btn-lg">View Shops</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer_public.php'; ?>
