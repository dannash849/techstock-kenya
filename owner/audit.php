<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireOwner();
$page_title = 'Audit Logs';
$logs=$conn->query("SELECT a.*, u.full_name, u.role FROM audit_logs a LEFT JOIN users u ON a.user_id=u.id ORDER BY a.created_at DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title><?=$page_title?> — TechStock</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techstock/css/style.css"></head><body>
<div class="dash-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="dash-main">
  <div class="dash-topbar"><div class="dash-title">Audit Logs</div><div class="dash-sub">All system actions tracked for fraud prevention</div></div>
  <div class="alert alert-info">🔒 Every action is logged — who did what, when, and from which IP. This prevents fraud and unauthorized activities.</div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>IP Address</th><th>Time</th></tr></thead>
        <tbody>
        <?php foreach($logs as $l): ?>
        <tr>
          <td><strong><?=htmlspecialchars($l['full_name']??'System')?></strong></td>
          <td><span class="badge <?=$l['role']==='owner'?'badge-blue':($l['role']==='employee'?'badge-green':'badge-gray') ?>"><?=ucfirst($l['role']??'—')?></span></td>
          <td style="color:var(--cyan);font-family:monospace;font-size:0.82rem"><?=htmlspecialchars($l['action'])?></td>
          <td style="color:var(--text2);max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($l['details']??'—')?></td>
          <td style="color:var(--text3);font-size:0.78rem"><?=htmlspecialchars($l['ip_address']??'—')?></td>
          <td style="color:var(--text2);font-size:0.8rem"><?=date('d M Y H:i',strtotime($l['created_at']))?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="/techstock/js/main.js"></script></body></html>
