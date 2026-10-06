<?php
require_once __DIR__.'/includes/header.php';
if(!can('phones')) redirect('dashboard.php');
$f = trim($_GET['q'] ?? '');
$w = "em.archived=0 AND (em.phone<>'' OR em.phone2<>'')"; $p = array();
if($f !== ''){ $w .= " AND (fullname LIKE ? OR phone LIKE ? OR phone2 LIKE ? OR position LIKE ?)"; $p = array("%$f%","%$f%","%$f%","%$f%"); }
if(scope_region()){ $w .= " AND region_id=?"; $p[] = scope_region(); }
$emps = rows("SELECT em.*, r.name reg_name, o.name obj_name, d.name dep_name FROM employees em
 LEFT JOIN regions r ON r.id=em.region_id LEFT JOIN objects o ON o.id=em.object_id
 LEFT JOIN departments d ON d.id=em.department_id WHERE $w ORDER BY em.fullname", ...$p);
?>
<h1>Telefon raqamlari</h1>
<form class="filters" method="get">
 <input name="q" value="<?= e($f) ?>" placeholder="Qidiruv: F.I.Sh., lavozim, raqam...">
 <button class="btn">Qidirish</button>
</form>
<div class="cards phones">
<?php foreach($emps as $em): ?>
 <div class="card phone-card">
  <b><?= e($em['fullname']) ?></b>
  <div class="muted"><?= e($em['position']) ?></div>
  <div class="phone-num"><?= e($em['phone']) ?></div>
  <?php if($em['phone2']): ?><div class="phone-num"><?= e($em['phone2']) ?></div><?php endif; ?>
  <div class="muted small"><?= e($em['reg_name']) ?> · <?= e($em['dep_name'] ?: $em['obj_name']) ?></div>
 </div>
<?php endforeach; if(!$emps): ?><p class="muted">Topilmadi.</p><?php endif; ?>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
