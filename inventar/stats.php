<?php
require_once __DIR__.'/includes/header.php';
if(!can('stats')) redirect('dashboard.php');
$reg = scope_region(); $w = $reg ? "AND d.region_id=$reg" : "";
$byYear = rows("SELECT YEAR(acquired) y, COUNT(*) c FROM devices WHERE archived=0 $w AND acquired IS NOT NULL GROUP BY y ORDER BY y");
$byManuf = rows("SELECT manufacturer m, COUNT(*) c FROM devices WHERE archived=0 $w AND manufacturer<>'' GROUP BY manufacturer ORDER BY c DESC LIMIT 12");
$byEmp = rows("SELECT e.fullname f, COUNT(*) c FROM devices d JOIN employees e ON e.id=d.employee_id
 WHERE d.archived=0 $w GROUP BY e.id ORDER BY c DESC LIMIT 12");
$max = max(1, (int)max(array_map(function($r){return (int)$r['c'];}, array_merge($byYear?:[['c'=>1]],$byManuf?:[['c'=>1]],$byEmp?:[['c'=>1]]))));
function bars($rows,$labelKey,$valKey,$max){ foreach($rows as $r): $p=round($r[$valKey]/$max*100); ?>
<div class="bar-row"><span class="bar-label"><?= e($r[$labelKey]) ?></span>
<div class="bar"><div class="bar-fill" style="width:<?= $p ?>%"></div></div><b><?= (int)$r[$valKey] ?></b></div>
<?php endforeach; if(!$rows) echo '<p class="muted">Ma\'lumot yo\'q</p>'; }
?>
<h1>Statistika</h1>
<div class="grid3">
 <div class="panel"><h3>Olingan yili bo'yicha</h3><?php bars($byYear,'y','c',$max); ?></div>
 <div class="panel"><h3>Ishlab chiqaruvchi bo'yicha</h3><?php bars($byManuf,'m','c',$max); ?></div>
 <div class="panel"><h3>Mas'ul xodim bo'yicha</h3><?php bars($byEmp,'f','c',$max); ?></div>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
