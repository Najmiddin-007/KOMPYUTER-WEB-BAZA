<?php
require_once __DIR__.'/includes/header.php';
global $DEVICE_TYPES, $PRINTER_TYPES;
if(!can('view')) redirect('dashboard.php');

$w = "d.archived=0"; $p = array();
if(scope_region()){ $w .= " AND d.region_id=?"; $p[] = scope_region(); }
$f_status = (int)($_GET['status'] ?? 0);
$f_type = $_GET['type'] ?? '';
$f_region = (int)($_GET['region'] ?? 0);
$f_obj = (int)($_GET['object'] ?? 0);
$f_search = trim($_GET['q'] ?? '');
if($f_status){ $w .= " AND d.status_id=?"; $p[] = $f_status; }
if($f_type){ $w .= " AND d.type_code=?"; $p[] = $f_type; }
if($f_region && !scope_region()){ $w .= " AND d.region_id=?"; $p[] = $f_region; }
if($f_obj){ $w .= " AND d.object_id=?"; $p[] = $f_obj; }
if($f_search !== ''){
    $w .= " AND (d.inventory_no LIKE ? OR d.serial LIKE ? OR d.model LIKE ? OR d.manufacturer LIKE ? OR d.extra LIKE ?)";
    $like = "%$f_search%"; array_push($p,$like,$like,$like,$like,$like);
}
$sql = "SELECT d.*, s.name st_name, s.color st_color, o.name obj_name, r.name reg_name, e.fullname emp_name
 FROM devices d LEFT JOIN statuses s ON s.id=d.status_id
 LEFT JOIN objects o ON o.id=d.object_id LEFT JOIN regions r ON r.id=d.region_id
 LEFT JOIN employees e ON e.id=d.employee_id
 WHERE $w ORDER BY d.id DESC LIMIT 500";
$devs = rows($sql, ...$p);
?>
<div class="page-head"><h1>Qurilmalar</h1>
 <?php if(can('add')): ?><a class="btn primary" href="device_edit.php">+ Yangi qurilma</a><?php endif; ?>
 <a class="btn" href="labels.php">Stikerlar chop etish</a></div>
<form class="filters" method="get">
 <input name="q" value="<?= e($f_search) ?>" placeholder="Qidiruv: inventar, serial, IP, IMEI, model...">
 <select name="type"><option value="">— Tur —</option>
  <?php foreach($DEVICE_TYPES as $k=>$t): ?><option value="<?= e($k) ?>" <?= $f_type===$k?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select>
 <select name="status"><option value="">— Holat —</option>
  <?php foreach(rows("SELECT * FROM statuses WHERE active=1 ORDER BY ord") as $s): ?>
   <option value="<?= $s['id'] ?>" <?= $f_status===(int)$s['id']?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
 <?php if(!scope_region()): ?>
 <select name="region"><option value="">— Hudud —</option>
  <?php foreach(rows("SELECT * FROM regions ORDER BY id") as $r): ?>
   <option value="<?= $r['id'] ?>" <?= $f_region===(int)$r['id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select>
 <?php endif; ?>
 <select name="object"><option value="">— Obyekt —</option>
  <?php foreach(rows("SELECT id,name FROM objects WHERE archived=0 ORDER BY code") as $o): ?>
   <option value="<?= $o['id'] ?>" <?= $f_obj===(int)$o['id']?'selected':'' ?>><?= e($o['name']) ?></option><?php endforeach; ?></select>
 <button class="btn">Qidirish</button>
</form>
<table class="tbl responsive">
<tr><th>Inventar</th><th>Tur</th><th>Ishlab chiqaruvchi</th><th>Model</th><th>Serial</th><th>Holat</th><th>Hudud</th><th>Obyekt</th><th>Mas'ul</th><th></th></tr>
<?php foreach($devs as $d): ?>
<tr>
 <td><a href="device_view.php?id=<?= $d['id'] ?>"><b><?= e($d['inventory_no']) ?></b></a></td>
 <td><?= e(type_name($d['type_code'])) ?><?= $d['printer_type'] ? ' ('.e($PRINTER_TYPES[$d['printer_type']] ?? $d['printer_type']).')' : '' ?></td>
 <td><?= e($d['manufacturer']) ?></td><td><?= e($d['model']) ?></td><td><?= e($d['serial']) ?></td>
 <td><span class="dot" style="background:<?= e($d['st_color']) ?>"></span><?= e($d['st_name']) ?></td>
 <td><?= e($d['reg_name']) ?></td><td><?= e($d['obj_name']) ?></td><td><?= e($d['emp_name']) ?></td>
 <td class="nowrap">
  <?php if(can('edit')): ?><a class="btn tiny" href="device_edit.php?id=<?= $d['id'] ?>">Tahrir</a><?php endif; ?>
  <?php if(can('archive')): ?>
   <form method="post" action="device_actions.php" style="display:inline" onsubmit="return confirm('Arxivlash?')">
    <?= csrf_field() ?><input type="hidden" name="act" value="archive"><input type="hidden" name="id" value="<?= $d['id'] ?>">
    <button class="btn tiny danger">Arxiv</button></form><?php endif; ?>
 </td></tr>
<?php endforeach; ?>
<?php if(!$devs): ?><tr><td colspan="10" class="muted">Ma'lumot topilmadi.</td></tr><?php endif; ?>
</table>
<p class="muted small">Ko'rsatildi: <?= count($devs) ?> (maks. 500)</p>
<?php require_once __DIR__.'/includes/footer.php'; ?>
