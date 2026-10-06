<?php
require_once __DIR__.'/includes/header.php';
global $DEVICE_TYPES, $PRINTER_TYPES;
$d = row("SELECT d.*, s.name st_name, s.color st_color, o.name obj_name, o.code obj_code,
 r.name reg_name, e.fullname emp_name, e.position emp_pos, dep.name dep_name, dep.code dep_code
 FROM devices d LEFT JOIN statuses s ON s.id=d.status_id LEFT JOIN objects o ON o.id=d.object_id
 LEFT JOIN regions r ON r.id=d.region_id LEFT JOIN employees e ON e.id=d.employee_id
 LEFT JOIN departments dep ON dep.id=d.department_id WHERE d.id=?", (int)($_GET['id'] ?? 0));
if(!$d){ flash('Topilmadi'); redirect('devices.php'); }
$extra = $d['extra'] ? json_decode($d['extra'], true) : array();
$fields = $DEVICE_TYPES[$d['type_code']]['fields'] ?? array();
$imgs = $d['images'] ? json_decode($d['images'], true) : array();
$hist = rows("SELECT h.*, u.login FROM device_history h LEFT JOIN users u ON u.id=h.user_id WHERE h.device_id=? ORDER BY h.id DESC", $d['id']);
$ticket = row("SELECT id FROM tickets WHERE device_id=? ORDER BY id DESC LIMIT 1", $d['id']);
?>
<div class="page-head">
 <h1>Qurilma pasporti: <?= e($d['inventory_no']) ?></h1>
 <div>
  <?php if(can('edit')): ?><a class="btn" href="device_edit.php?id=<?= $d['id'] ?>">Tahrirlash</a><?php endif; ?>
  <button class="btn" onclick="window.print()">PDF / Chop etish</button>
  <a class="btn" href="chat.php?device_id=<?= $d['id'] ?>">Texnik yordam</a>
 </div>
</div>
<div class="grid2">
 <div class="panel">
  <table class="tbl kv">
   <tr><th>Inventar raqami</th><td><b><?= e($d['inventory_no']) ?></b></td></tr>
   <tr><th>Turi</th><td><?= e(type_name($d['type_code'])) ?><?= $d['printer_type'] ? ' — '.e($PRINTER_TYPES[$d['printer_type']] ?? $d['printer_type']) : '' ?></td></tr>
   <tr><th>Ishlab chiqaruvchi</th><td><?= e($d['manufacturer']) ?></td></tr>
   <tr><th>Model</th><td><?= e($d['model']) ?></td></tr>
   <tr><th>Serial</th><td><?= e($d['serial']) ?></td></tr>
   <?php foreach($fields as $k=>$label): if(!empty($extra[$k])): ?>
    <tr><th><?= e($label) ?></th><td><?= e($extra[$k]) ?></td></tr>
   <?php endif; endforeach; ?>
   <tr><th>Holat</th><td><span class="dot" style="background:<?= e($d['st_color']) ?>"></span><?= e($d['st_name']) ?></td></tr>
   <tr><th>Hudud</th><td><?= e($d['reg_name']) ?></td></tr>
   <tr><th>Obyekt</th><td><?= e(($d['obj_code']? $d['obj_code'].' — ':'').$d['obj_name']) ?></td></tr>
   <?php if($d['dep_name']): ?><tr><th>Bo'lim</th><td><?= e($d['dep_code'].' — '.$d['dep_name']) ?></td></tr><?php endif; ?>
   <tr><th>Mas'ul xodim</th><td><?= e($d['emp_name']) ?><?= $d['emp_pos'] ? ' ('.e($d['emp_pos']).')' : '' ?></td></tr>
   <tr><th>Xona</th><td><?= e($d['room']) ?></td></tr>
   <tr><th>Olingan sana</th><td><?= e($d['acquired']) ?></td></tr>
   <?php if($d['note']): ?><tr><th>Izoh</th><td><?= nl2br(e($d['note'])) ?></td></tr><?php endif; ?>
  </table>
 </div>
 <div class="panel center">
  <div class="codes">
   <div id="qrbox" class="qrbox"></div>
   <svg id="barcode"></svg>
   <div class="muted small"><?= e($d['inventory_no']) ?></div>
  </div>
  <?php if($imgs): ?>
   <div class="gallery">
    <?php foreach($imgs as $im): ?>
     <a href="uploads/<?= e($im) ?>" target="_blank"><img src="uploads/<?= e($im) ?>" class="<?= $d['main_image']===$im?'main':'' ?>"></a>
    <?php endforeach; ?>
   </div>
  <?php endif; ?>
 </div>
</div>
<h2>Harakat tarixi</h2>
<table class="tbl"><tr><th>Sana</th><th>Amal</th><th>Foydalanuvchi</th><th>O'zgarish</th></tr>
<?php foreach($hist as $h): ?>
<tr><td><?= e($h['created_at']) ?></td><td><?= e($h['action']) ?></td><td><?= e($h['login']) ?></td>
<td><small><?= e($h['old_value']) ?> &rarr; <?= e($h['new_value']) ?></small></td></tr>
<?php endforeach; if(!$hist): ?><tr><td colspan="4" class="muted">Tarix yo'q</td></tr><?php endif; ?>
</table>
<script>
const inv = <?= json_encode($d['inventory_no']) ?>;
if(typeof QRCode!=='undefined'){ new QRCode(document.getElementById('qrbox'), {text: inv, width:140, height:140}); }
if(typeof JsBarcode!=='undefined'){ JsBarcode('#barcode', inv, {format:'CODE128', height:50, width:1.6, displayValue:true, fontSize:12}); }
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?>
