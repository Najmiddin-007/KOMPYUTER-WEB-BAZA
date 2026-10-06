<?php
require_once __DIR__.'/includes/functions.php';
require_login();
if(!can('export')) redirect('dashboard.php');
global $DEVICE_TYPES;
$type = $_GET['type'] ?? '';
if($type && isset($DEVICE_TYPES[$type])){
    $fields = array_keys($DEVICE_TYPES[$type]['fields']);
    $header = array_merge(array('inventory_no','tur','ishlab_chiqaruvchi','model','serial'), $fields,
      array('holat','hudud','obyekt','bolim','masul','xona','olingan_sana','izoh'));
    $reg = scope_region();
    $devs = rows("SELECT d.*, s.name st, r.name reg, o.name obj, dep.name dep, e.fullname emp FROM devices d
     LEFT JOIN statuses s ON s.id=d.status_id LEFT JOIN regions r ON r.id=d.region_id LEFT JOIN objects o ON o.id=d.object_id
     LEFT JOIN departments dep ON dep.id=d.department_id LEFT JOIN employees e ON e.id=d.employee_id
     WHERE d.type_code=? AND d.archived=0 ".($reg?"AND d.region_id=$reg":"")." ORDER BY d.inventory_no", $type);
    $data = array();
    foreach($devs as $d){
        $ex = json_decode($d['extra'] ?? '{}', true);
        $row = array($d['inventory_no'], type_name($type), $d['manufacturer'], $d['model'], $d['serial']);
        foreach($fields as $f) $row[] = $ex[$f] ?? '';
        $row[] = $d['st']; $row[] = $d['reg']; $row[] = $d['obj']; $row[] = $d['dep']; $row[] = $d['emp'];
        $row[] = $d['room']; $row[] = $d['acquired']; $row[] = $d['note'];
        $data[] = $row;
    }
    audit('export',0,'csv',null,$type);
    csv_out("inventar_$type_".date('Ymd').'.csv', $header, $data);
}
require_once __DIR__.'/includes/header.php';
?>
<h1>Excel export (CSV)</h1>
<p class="muted">Har bir qurilma turi uchun alohida shablon. Excelda ochganda UTF-8 formatida ochiladi.</p>
<div class="cards">
<?php foreach($DEVICE_TYPES as $k=>$t): ?>
 <div class="card"><b><?= e($t['name']) ?></b><br>
  <a class="btn small" href="export.php?type=<?= e($k) ?>">⬇ CSV yuklash</a></div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
