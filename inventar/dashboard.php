<?php
require_once __DIR__.'/includes/header.php';
$reg = scope_region();
$w = $reg ? "AND region_id=$reg" : "";
$total = (int)one("SELECT COUNT(*) FROM devices WHERE archived=0 $w");
$byStatus = rows("SELECT s.name,s.color,COUNT(d.id) c FROM statuses s LEFT JOIN devices d ON d.status_id=s.id AND d.archived=0 $w GROUP BY s.id ORDER BY s.ord");
$byType = rows("SELECT type_code, COUNT(*) c FROM devices WHERE archived=0 $w GROUP BY type_code ORDER BY c DESC LIMIT 8");
$regions = rows("SELECT r.name, COUNT(d.id) c FROM regions r LEFT JOIN devices d ON d.region_id=r.id AND d.archived=0 GROUP BY r.id ORDER BY c DESC LIMIT 8");
$maxT = max(1,(int)($byType ? max(array_column($byType,'c')) : 1));
$maxR = max(1,(int)($regions ? max(array_column($regions,'c')) : 1));
?>
<h1>Bosh sahifa</h1>
<div class="cards">
 <div class="card"><div class="num"><?= $total ?></div><div>Jami qurilmalar</div></div>
 <div class="card"><div class="num"><?= (int)one("SELECT COUNT(*) FROM objects WHERE archived=0") ?></div><div>Obyektlar</div></div>
 <div class="card"><div class="num"><?= (int)one("SELECT COUNT(*) FROM employees WHERE archived=0") ?></div><div>Xodimlar</div></div>
 <div class="card"><div class="num"><?= (int)one("SELECT COUNT(*) FROM devices WHERE archived=1 $w") ?></div><div>Arxivdagi</div></div>
</div>
<h2>Holat bo'yicha</h2>
<div class="chips"><?php foreach($byStatus as $s): ?>
 <span class="chip" style="border-color:<?= e($s['color']) ?>"><i style="background:<?= e($s['color']) ?>"></i><?= e($s['name']) ?>: <b><?= (int)$s['c'] ?></b></span>
<?php endforeach; ?></div>
<div class="grid2">
 <div class="panel"><h3>Qurilma turlari bo'yicha</h3>
  <?php foreach($byType as $t): $p = round($t['c']/$maxT*100); ?>
   <div class="bar-row"><span class="bar-label"><?= e(type_name($t['type_code'])) ?></span>
   <div class="bar"><div class="bar-fill" style="width:<?= $p ?>%"></div></div><b><?= (int)$t['c'] ?></b></div>
  <?php endforeach; ?>
 </div>
 <div class="panel"><h3>Hududlar bo'yicha</h3>
  <?php foreach($regions as $r): $p = round($r['c']/$maxR*100); ?>
   <div class="bar-row"><span class="bar-label"><?= e($r['name']) ?></span>
   <div class="bar"><div class="bar-fill alt" style="width:<?= $p ?>%"></div></div><b><?= (int)$r['c'] ?></b></div>
  <?php endforeach; ?>
 </div>
</div>
<?php if(can('audit')): ?>
<h2>So'nggi o'zgarishlar</h2>
<table class="tbl"><tr><th>Sana</th><th>Amal</th><th>Entity</th><th>ID</th><th>Foydalanuvchi</th></tr>
<?php foreach(rows("SELECT a.*,u.login FROM audit_log a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 10") as $a): ?>
<tr><td><?= e($a['created_at']) ?></td><td><?= e($a['action']) ?></td><td><?= e($a['entity']) ?></td><td><?= (int)$a['entity_id'] ?></td><td><?= e($a['login']) ?></td></tr>
<?php endforeach; ?></table>
<?php endif; ?>
<?php require_once __DIR__.'/includes/footer.php'; ?>
