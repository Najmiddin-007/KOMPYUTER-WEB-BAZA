<?php
require_once __DIR__.'/includes/header.php';
if(!can('audit')) redirect('dashboard.php');
$f = trim($_GET['q'] ?? '');
$w = '1=1'; $p = array();
if($f !== ''){ $w .= " AND (a.entity LIKE ? OR a.action LIKE ? OR u.login LIKE ?)"; $p = array("%$f%","%$f%","%$f%"); }
$logs = rows("SELECT a.*, u.login FROM audit_log a LEFT JOIN users u ON u.id=a.user_id WHERE $w ORDER BY a.id DESC LIMIT 500", ...$p);
?>
<h1>Audit / O'zgarishlar tarixi</h1>
<form class="filters" method="get">
 <input name="q" value="<?= e($f) ?>" placeholder="Qidiruv: entity, amal, login...">
 <button class="btn">Qidirish</button>
</form>
<table class="tbl responsive">
<tr><th>Sana</th><th>Foydalanuvchi</th><th>Amal</th><th>Entity</th><th>ID</th><th>Eski qiymat</th><th>Yangi qiymat</th><th>IP</th></tr>
<?php foreach($logs as $l): ?>
<tr><td><?= e($l['created_at']) ?></td><td><?= e($l['login']) ?></td><td><?= e($l['action']) ?></td>
<td><?= e($l['entity']) ?></td><td><?= (int)$l['entity_id'] ?></td>
<td><small><?= e(mb_strimwidth((string)$l['old_value'],0,60,'...')) ?></small></td>
<td><small><?= e(mb_strimwidth((string)$l['new_value'],0,60,'...')) ?></small></td>
<td><?= e($l['ip']) ?></td></tr>
<?php endforeach; ?>
</table>
<?php require_once __DIR__.'/includes/footer.php'; ?>
