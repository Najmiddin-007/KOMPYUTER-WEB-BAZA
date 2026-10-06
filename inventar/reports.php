<?php
require_once __DIR__.'/includes/header.php';
if(!can('pdf')) redirect('dashboard.php');
$type = $_GET['type'] ?? 'umumiy';
$reg = scope_region();
$title = ''; $data = array(); $header = array();
if($type==='umumiy'){ $title='Umumiy inventar hisoboti';
 $data = rows("SELECT d.inventory_no, d.type_code, d.manufacturer, d.model, d.serial, s.name st, r.name reg, o.name obj, e.fullname emp, d.acquired
  FROM devices d LEFT JOIN statuses s ON s.id=d.status_id LEFT JOIN regions r ON r.id=d.region_id
  LEFT JOIN objects o ON o.id=d.object_id LEFT JOIN employees e ON e.id=d.employee_id
  WHERE d.archived=0 ".($reg?"AND d.region_id=$reg":"")." ORDER BY d.inventory_no");
 $header = array('Inventar','Tur','Ishlab chiqaruvchi','Model','Serial','Holat','Hudud','Obyekt',"Mas'ul",'Olingan');
} elseif($type==='hudud'){ $title='Hudud bo\'yicha hisobot';
 $data = rows("SELECT r.name hudud, COUNT(*) jami, SUM(d.status_id=3) ishlamaydi, SUM(d.status_id=5) chiqarilgan FROM devices d JOIN regions r ON r.id=d.region_id WHERE d.archived=0 ".($reg?"AND d.region_id=$reg":"")." GROUP BY r.id ORDER BY r.id");
 $header = array('Hudud','Jami','Ishlamayapti','Hisobdan chiq.');
} elseif($type==='obyekt'){ $title='Obyekt bo\'yicha';
 $data = rows("SELECT o.code, o.name, r.name reg, COUNT(d.id) jami FROM objects o LEFT JOIN regions r ON r.id=o.region_id LEFT JOIN devices d ON d.object_id=o.id AND d.archived=0 WHERE o.archived=0 ".($reg?"AND o.region_id=$reg":"")." GROUP BY o.id ORDER BY o.code");
 $header = array('Kod','Obyekt','Hudud','Qurilmalar');
} elseif($type==='tur'){ $title='Qurilma turi bo\'yicha';
 $data = rows("SELECT type_code, COUNT(*) jami FROM devices WHERE archived=0 ".($reg?"AND region_id=$reg":"")." GROUP BY type_code ORDER BY jami DESC");
 $header = array('Tur','Soni');
} elseif($type==='holat'){ $title='Holati bo\'yicha';
 $data = rows("SELECT s.name, COUNT(*) jami FROM statuses s LEFT JOIN devices d ON d.status_id=s.id AND d.archived=0 ".($reg?"AND d.region_id=$reg":"")." GROUP BY s.id ORDER BY s.ord");
 $header = array('Holat','Soni');
} elseif($type==='yil'){ $title='Olingan yili bo\'yicha';
 $data = rows("SELECT YEAR(acquired) y, COUNT(*) jami FROM devices WHERE archived=0 AND acquired IS NOT NULL ".($reg?"AND region_id=$reg":"")." GROUP BY y ORDER BY y");
 $header = array('Yil','Soni');
} elseif($type==='xodim'){ $title='Mas\'ul xodim bo\'yicha';
 $data = rows("SELECT e.fullname, e.position, COUNT(d.id) jami FROM employees e LEFT JOIN devices d ON d.employee_id=e.id AND d.archived=0 WHERE e.archived=0 ".($reg?"AND e.region_id=$reg":"")." GROUP BY e.id ORDER BY jami DESC");
 $header = array('F.I.Sh.','Lavozim','Qurilmalar');
} elseif($type==='printer'){ $title='Printerlar hisoboti (Oddiy/Rangli/3 tasi birda)';
 $data = rows("SELECT d.inventory_no, d.manufacturer, d.model, d.printer_type, o.name obj, e.fullname emp FROM devices d LEFT JOIN objects o ON o.id=d.object_id LEFT JOIN employees e ON e.id=d.employee_id WHERE d.type_code='printer' AND d.archived=0 ".($reg?"AND d.region_id=$reg":"")." ORDER BY d.printer_type, d.inventory_no");
 $header = array('Inventar','Ishlab chiqaruvchi','Model','Subtur','Obyekt',"Mas'ul");
} elseif($type==='kochirish'){ $title='Qurilma ko\'chirish tarixi';
 $data = rows("SELECT d.inventory_no, h.old_value, h.new_value, u.login, h.created_at FROM device_history h JOIN devices d ON d.id=h.device_id LEFT JOIN users u ON u.id=h.user_id WHERE h.action='kochirish' ORDER BY h.id DESC LIMIT 200");
 $header = array('Inventar','Oldingi','Yangi','Kim','Sana');
} elseif($type==='inventory'){ $title='Inventarizatsiya dalolatnomasi';
 $cid = (int)($_GET['campaign'] ?? 0);
 $camp = row("SELECT * FROM inventory_campaigns WHERE id=?", $cid);
 $data = rows("SELECT d.inventory_no, d.type_code, d.model, o.name obj, x.result, x.note, x.scanned_at FROM inventory_results x JOIN devices d ON d.id=x.device_id LEFT JOIN objects o ON o.id=d.object_id WHERE x.campaign_id=? ORDER BY d.inventory_no", $cid);
 $header = array('Inventar','Tur','Model','Obyekt','Natija','Izoh','Sana');
 $title .= ' — '.($camp['year'] ?? $cid).' yil';
}
?>
<div class="page-head no-print"><h1>Hisobotlar</h1>
 <div class="chips">
 <?php foreach(array('umumiy','hudud','obyekt','tur','holat','yil','xodim','printer','kochirish') as $t): ?>
  <a class="chip <?= $type===$t?'active':'' ?>" href="reports.php?type=<?= $t ?>"><?= ucfirst($t) ?></a>
 <?php endforeach; ?>
 </div>
 <button class="btn primary" onclick="window.print()">PDF / Chop etish</button>
</div>
<div class="report-doc">
 <div class="r-head">
  <div class="r-logo"><img src="assets/img/logo.png"></div>
  <div><b><?= e(setting('org_name')) ?></b><br>
  <span class="muted"><?= e($title) ?></span><br>
  <span class="small">Sana: <?= date('d.m.Y') ?> · Sana/vaqt: <?= date('H:i') ?></span></div>
 </div>
 <table class="tbl report-tbl">
 <tr><?php foreach($header as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr>
 <?php $sum=0; foreach($data as $row): ?><tr>
  <?php foreach($row as $k=>$v): if($k==='type_code') $v = type_name($v); ?>
   <td><?= e($v) ?></td>
  <?php if(is_numeric($v)) $sum += (int)$v; endforeach; ?></tr>
 <?php endforeach; if(!$data): ?><tr><td colspan="<?= count($header) ?>" class="muted">Ma'lumot yo'q</td></tr><?php endif; ?>
 </table>
 <div class="r-foot">
  <p><b>Jami yozuvlar: <?= count($data) ?></b></p>
  <div class="signs"><div>Mas'ul: ________________</div><div>Rahbar: ________________</div><div>Sana: _____._____.20___</div></div>
 </div>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
