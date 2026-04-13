<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireOwner();
$page_title = 'All Products';
$uid = $_SESSION['user_id'];
$msg = ''; $err = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $pid = (int)($_POST['product_id']??0);
    if($_POST['action']==='mark_sold') {
        // Check product belongs to owner's shop
        $check=$conn->query("SELECT p.id,p.status FROM products p JOIN shops s ON p.shop_id=s.id WHERE p.id=$pid AND s.owner_id=$uid")->fetch_assoc();
        if($check && $check['status']!=='sold') {
            $conn->query("UPDATE products SET status='sold' WHERE id=$pid");
            $msg="Product marked as SOLD. 🔒 It is now locked permanently.";
            auditLog($conn,'product_sold',"Product ID:$pid marked sold by owner");
        }
    }
    if($_POST['action']==='toggle_featured') {
        $check=$conn->query("SELECT featured FROM products p JOIN shops s ON p.shop_id=s.id WHERE p.id=$pid AND s.owner_id=$uid")->fetch_assoc();
        if($check) {
            $new = $check['featured'] ? 0 : 1;
            $conn->query("UPDATE products SET featured=$new WHERE id=$pid");
            $msg="Featured status updated.";
        }
    }
    if($_POST['action']==='delete' && isOwner()) {
        $check=$conn->query("SELECT status FROM products p JOIN shops s ON p.shop_id=s.id WHERE p.id=$pid AND s.owner_id=$uid")->fetch_assoc();
        if($check && $check['status']==='sold') { $err="❌ Cannot delete SOLD products. This is a fraud prevention measure."; }
        else {
            $conn->query("DELETE FROM products WHERE id=$pid");
            $msg="Product deleted."; auditLog($conn,'product_deleted',"ID:$pid");
        }
    }
}

$shop_filter = (int)($_GET['shop']??0);
$status_filter = $_GET['status']??'';
$search = $_GET['search']??'';

$where = ["s.owner_id=$uid"];
if($shop_filter) $where[] = "p.shop_id=$shop_filter";
if($status_filter) $where[] = "p.status='".mysqli_real_escape_string($conn,$status_filter)."'";
if($search) $where[] = "p.title LIKE '%".mysqli_real_escape_string($conn,$search)."%'";

$products=$conn->query("SELECT p.*, s.name as shop_name, u.full_name as added_by_name FROM products p JOIN shops s ON p.shop_id=s.id JOIN users u ON p.added_by=u.id WHERE ".implode(' AND ',$where)." ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$my_shops=$conn->query("SELECT id,name FROM shops WHERE owner_id=$uid")->fetch_all(MYSQLI_ASSOC);
$cond_colors=['new'=>'badge-green','used'=>'badge-yellow','refurbished'=>'badge-purple'];
$status_colors=['available'=>'badge-green','reserved'=>'badge-yellow','sold'=>'badge-red'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar">
    <div><div class="dash-title">All Products</div><div class="dash-sub"><?=count($products)?> products found</div></div>
  </div>
  <?php if($msg): ?><div class="alert alert-success">✅ <?=$msg?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-error"><?=$err?></div><?php endif; ?>

  <!-- Filters -->
  <div class="card card-body" style="margin-bottom:20px">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      <div class="form-group" style="margin-bottom:0;flex:1;min-width:180px"><label>Search</label><input type="text" name="search" placeholder="Product name..." value="<?=htmlspecialchars($search)?>"></div>
      <div class="form-group" style="margin-bottom:0"><label>Shop</label>
        <select name="shop">
          <option value="">All Shops</option>
          <?php foreach($my_shops as $s): ?><option value="<?=$s['id']?>" <?=$shop_filter==$s['id']?'selected':''?>><?=htmlspecialchars($s['name'])?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0"><label>Status</label>
        <select name="status">
          <option value="">All</option>
          <option value="available" <?=$status_filter=='available'?'selected':''?>>Available</option>
          <option value="reserved" <?=$status_filter=='reserved'?'selected':''?>>Reserved</option>
          <option value="sold" <?=$status_filter=='sold'?'selected':''?>>Sold</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="products.php" class="btn btn-ghost">Clear</a>
    </form>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Product</th><th>Shop</th><th>Price</th><th>Condition</th><th>Status</th><th>Added By</th><th>Featured</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if(empty($products)): ?>
        <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text2)">No products found</td></tr>
        <?php else: foreach($products as $p): $sold=$p['status']==='sold'; ?>
        <tr class="<?=$sold?'locked-row':''?>">
          <td style="color:var(--text2)">#<?=$p['id']?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:36px;height:36px;background:rgba(59,130,246,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1rem">💻</div>
              <div>
                <div style="color:var(--white);font-size:0.875rem;font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($p['title'])?></div>
                <div style="color:var(--text2);font-size:0.72rem"><?=htmlspecialchars($p['cpu']??'')?></div>
              </div>
            </div>
          </td>
          <td style="color:var(--text2)"><?=htmlspecialchars($p['shop_name'])?></td>
          <td style="color:var(--white);font-weight:600"><?=formatPrice($p['price'])?></td>
          <td><span class="badge <?=$cond_colors[$p['condition_type']]?>"><?=ucfirst($p['condition_type'])?></span></td>
          <td>
            <span class="badge <?=$status_colors[$p['status']]?>">
              <?=$sold?'🔒 ':''?><?=ucfirst($p['status'])?>
            </span>
          </td>
          <td style="color:var(--text2)"><?=htmlspecialchars($p['added_by_name'])?></td>
          <td><?=$p['featured']?'⭐':'—'?></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <?php if(!$sold): ?>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="mark_sold">
                <input type="hidden" name="product_id" value="<?=$p['id']?>">
                <button type="submit" class="btn btn-sm btn-ghost confirm-btn" data-confirm="Mark as SOLD? This cannot be undone!">Mark Sold</button>
              </form>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle_featured">
                <input type="hidden" name="product_id" value="<?=$p['id']?>">
                <button type="submit" class="btn btn-sm btn-ghost"><?=$p['featured']?'Unfeature':'Feature'?></button>
              </form>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" value="<?=$p['id']?>">
                <button type="submit" class="btn btn-danger btn-sm confirm-btn" data-confirm="Delete this product?">Del</button>
              </form>
              <?php else: ?>
              <span class="locked-badge">🔒 Locked</span>
              <?php endif; ?>
            </div>
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
