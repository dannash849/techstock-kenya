<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireOwner();
$page_title = 'Employees';
$uid = $_SESSION['user_id'];
$msg = ''; $err = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    if($_POST['action']==='add_employee') {
        $name=$_POST['full_name']??''; $email=$_POST['email']??''; $phone=$_POST['phone']??''; $pass=$_POST['password']??'password123'; $shop_id=(int)$_POST['shop_id'];
        if($name && $email && $shop_id) {
            $check=$conn->prepare("SELECT id FROM users WHERE email=?"); $check->bind_param("s",$email); $check->execute();
            if($check->get_result()->num_rows>0) { $err="Email already exists."; }
            else {
                $hash=password_hash($pass,PASSWORD_DEFAULT);
                $stmt=$conn->prepare("INSERT INTO users (full_name,email,phone,password,role) VALUES (?,?,?,?,'employee')");
                $stmt->bind_param("ssss",$name,$email,$phone,$hash);
                if($stmt->execute()) {
                    $eid=$conn->insert_id;
                    $s2=$conn->prepare("INSERT INTO shop_employees (shop_id,employee_id) VALUES (?,?)");
                    $s2->bind_param("ii",$shop_id,$eid); $s2->execute();
                    $msg="Employee added!"; auditLog($conn,'employee_added',"$name assigned to shop $shop_id");
                } else { $err=$conn->error; }
            }
        } else { $err="All fields required."; }
    }
    if($_POST['action']==='remove') {
        $eid=(int)$_POST['emp_id'];
        $conn->query("DELETE FROM shop_employees WHERE employee_id=$eid");
        $conn->query("DELETE FROM users WHERE id=$eid AND role='employee'");
        $msg="Employee removed."; auditLog($conn,'employee_removed',"ID:$eid");
    }
}

$my_shops=$conn->query("SELECT id,name FROM shops WHERE owner_id=$uid AND status='active'")->fetch_all(MYSQLI_ASSOC);
$employees=$conn->query("SELECT u.*, s.name as shop_name, s.id as shop_id FROM users u JOIN shop_employees se ON u.id=se.employee_id JOIN shops s ON se.shop_id=s.id WHERE s.owner_id=$uid ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar">
    <div><div class="dash-title">Employees</div><div class="dash-sub"><?=count($employees)?> staff members</div></div>
    <button class="btn btn-primary" onclick="openModal('addEmpModal')">+ Add Employee</button>
  </div>
  <?php if($msg): ?><div class="alert alert-success">✅ <?=$msg?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-error">❌ <?=$err?></div><?php endif; ?>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Assigned Shop</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
        <?php if(empty($employees)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2)">No employees yet. Add your first staff member.</td></tr>
        <?php else: foreach($employees as $e): ?>
        <tr>
          <td style="color:var(--text2)">#<?=$e['id']?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:32px;height:32px;border-radius:50%;background:var(--blue);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.82rem;color:white"><?=strtoupper(substr($e['full_name'],0,1))?></div>
              <strong><?=htmlspecialchars($e['full_name'])?></strong>
            </div>
          </td>
          <td style="color:var(--text2)"><?=htmlspecialchars($e['email'])?></td>
          <td style="color:var(--text2)"><?=htmlspecialchars($e['phone']??'—')?></td>
          <td><span class="badge badge-blue">🏬 <?=htmlspecialchars($e['shop_name'])?></span></td>
          <td style="color:var(--text2)"><?=date('d M Y',strtotime($e['created_at']))?></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="emp_id" value="<?=$e['id']?>">
              <button type="submit" class="btn btn-danger btn-sm confirm-btn" data-confirm="Remove this employee?">Remove</button>
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

<div class="modal-overlay" id="addEmpModal">
<div class="modal">
  <div class="modal-header"><div class="modal-title">👨‍💻 Add Employee</div><button class="modal-close" onclick="closeModal('addEmpModal')">✕</button></div>
  <form method="POST"><input type="hidden" name="action" value="add_employee">
    <div class="form-grid">
      <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required placeholder="Jane Doe"></div>
      <div class="form-group"><label>Phone</label><input type="tel" name="phone" placeholder="07XX XXX XXX"></div>
      <div class="form-group form-full"><label>Email *</label><input type="email" name="email" required placeholder="employee@example.com"></div>
      <div class="form-group"><label>Assign to Shop *</label>
        <select name="shop_id" required>
          <option value="">Select shop</option>
          <?php foreach($my_shops as $s): ?>
          <option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Password (default: password123)</label><input type="text" name="password" placeholder="password123" value="password123"></div>
    </div>
    <div class="alert alert-info" style="margin-top:8px">ℹ Employee can only access their assigned shop.</div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
      <button type="button" class="btn btn-ghost" onclick="closeModal('addEmpModal')">Cancel</button>
      <button type="submit" class="btn btn-primary">Add Employee →</button>
    </div>
  </form>
</div></div>
<script src="/techstock/js/main.js"></script></body></html>
