<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
$page_title = 'Marketplace — TechStock Kenya';

$search   = $_GET['search'] ?? '';
$brand    = $_GET['brand'] ?? '';
$cond     = $_GET['condition'] ?? '';
$min      = $_GET['min'] ?? '';
$max      = $_GET['max'] ?? '';
$location = $_GET['location'] ?? '';
$sort     = $_GET['sort'] ?? 'newest';

$where = ["p.status='available'"];
if($search)   $where[] = "(p.title LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR p.cpu LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR p.brand LIKE '%".mysqli_real_escape_string($conn,$search)."%')";
if($brand)    $where[] = "p.brand='".mysqli_real_escape_string($conn,$brand)."'";
if($cond)     $where[] = "p.condition_type='".mysqli_real_escape_string($conn,$cond)."'";
if($min)      $where[] = "p.price >= ".intval($min);
if($max)      $where[] = "p.price <= ".intval($max);
if($location) $where[] = "s.location LIKE '%".mysqli_real_escape_string($conn,$location)."%'";

$order = $sort === 'price_asc' ? 'p.price ASC' : ($sort === 'price_desc' ? 'p.price DESC' : 'p.created_at DESC');
$query = "SELECT p.*, s.name as shop_name, s.location as shop_location, s.whatsapp FROM products p JOIN shops s ON p.shop_id=s.id WHERE ".implode(' AND ',$where)." ORDER BY p.featured DESC, $order";
$products = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

$brands    = $conn->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND status='available' ORDER BY brand")->fetch_all(MYSQLI_ASSOC);
$locations = $conn->query("SELECT DISTINCT location FROM shops WHERE status='active' ORDER BY location")->fetch_all(MYSQLI_ASSOC);
$cond_badges = ['new'=>'badge-new','used'=>'badge-used','refurbished'=>'badge-refurb'];
?>
<?php include 'includes/header_public.php'; ?>
<div style="padding-top:80px;padding-bottom:60px">
  <div class="container">
    <div style="margin-bottom:32px">
      <h1 style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--white);margin-bottom:6px">PC Marketplace</h1>
      <p style="color:var(--text2)"><?= count($products) ?> listings found</p>
    </div>

    <!-- Filters -->
    <div class="card card-body" style="margin-bottom:24px">
      <form method="GET" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:12px;align-items:flex-end">
        <div class="form-group" style="margin-bottom:0">
          <label>Search</label>
          <input type="text" name="search" placeholder="HP EliteBook, i7, 16GB..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label>Brand</label>
          <select name="brand">
            <option value="">All Brands</option>
            <?php foreach($brands as $b): ?>
            <option value="<?= $b['brand'] ?>" <?= $brand==$b['brand']?'selected':'' ?>><?= $b['brand'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label>Condition</label>
          <select name="condition">
            <option value="">All</option>
            <option value="new" <?= $cond=='new'?'selected':'' ?>>New</option>
            <option value="used" <?= $cond=='used'?'selected':'' ?>>Used</option>
            <option value="refurbished" <?= $cond=='refurbished'?'selected':'' ?>>Refurbished</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label>Location</label>
          <select name="location">
            <option value="">All Locations</option>
            <?php foreach($locations as $l): ?>
            <option value="<?= $l['location'] ?>" <?= $location==$l['location']?'selected':'' ?>><?= $l['location'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label>Sort By</label>
          <select name="sort">
            <option value="newest" <?= $sort=='newest'?'selected':'' ?>>Newest</option>
            <option value="price_asc" <?= $sort=='price_asc'?'selected':'' ?>>Price: Low-High</option>
            <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>Price: High-Low</option>
          </select>
        </div>
        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary">🔍</button>
          <a href="marketplace.php" class="btn btn-ghost">✕</a>
        </div>
      </form>
    </div>

    <!-- Products -->
    <?php if(empty($products)): ?>
    <div style="text-align:center;padding:80px 20px">
      <div style="font-size:4rem;margin-bottom:16px">💻</div>
      <h3 style="color:var(--white);margin-bottom:8px">No products found</h3>
      <p style="color:var(--text2)">Try different search terms or filters</p>
    </div>
    <?php else: ?>
    <div class="products-grid">
      <?php foreach($products as $p): ?>
      <div class="product-card">
        <div class="product-img">
          <?php if($p['image1']): ?>
          <img src="/techstock/uploads/products/<?= htmlspecialchars($p['image1']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
          <?php else: ?>💻<?php endif; ?>
          <span class="product-badge <?= $cond_badges[$p['condition_type']] ?>"><?= ucfirst($p['condition_type']) ?></span>
          <?php if($p['featured']): ?><span class="badge-featured">⭐</span><?php endif; ?>
        </div>
        <div class="product-body">
          <div class="product-shop">🏬 <?= htmlspecialchars($p['shop_name']) ?> • <?= htmlspecialchars($p['shop_location']) ?></div>
          <div class="product-title"><?= htmlspecialchars($p['title']) ?></div>
          <div class="product-specs">
            <?php if($p['cpu']): ?><div class="spec-item"><span>⚙️</span><?= htmlspecialchars(substr($p['cpu'],0,18)) ?></div><?php endif; ?>
            <?php if($p['ram']): ?><div class="spec-item"><span>🧠</span><?= htmlspecialchars($p['ram']) ?></div><?php endif; ?>
            <?php if($p['storage']): ?><div class="spec-item"><span>💾</span><?= htmlspecialchars($p['storage']) ?></div><?php endif; ?>
            <?php if($p['gpu']): ?><div class="spec-item"><span>🎮</span><?= htmlspecialchars(substr($p['gpu'],0,16)) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="product-footer">
          <div>
            <div class="product-price"><?= formatPrice($p['price']) ?></div>
            <?php if($p['original_price'] && $p['original_price'] > $p['price']): ?>
            <div class="product-original"><?= formatPrice($p['original_price']) ?></div>
            <?php endif; ?>
          </div>
          <div style="display:flex;gap:6px">
            <?php if($p['whatsapp']): ?>
            <a href="https://wa.me/<?= $p['whatsapp'] ?>?text=Hi, I'm interested in <?= urlencode($p['title']) ?>" target="_blank" class="btn btn-whatsapp btn-sm">WhatsApp</a>
            <?php endif; ?>
            <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">View</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include 'includes/footer_public.php'; ?>
