<?php
require_once __DIR__.'/includes/functions.php';
require_login(); csrf_check();
$act = $_POST['act'] ?? ''; $id = (int)($_POST['id'] ?? 0);
$d = row("SELECT * FROM devices WHERE id=?", $id);
if(!$d) redirect('devices.php');
if(scope_region() && (int)$d['region_id'] !== scope_region()){ flash('Ruxsat yo\'q'); redirect('devices.php'); }
if($act==='archive' && can('archive')){
    q("UPDATE devices SET archived=1 WHERE id=?", $id);
    dev_history($id,'arxivlandi'); audit('device',$id,'archive');
    flash('Arxivlandi.');
}
if($act==='restore' && can('restore')){
    q("UPDATE devices SET archived=0 WHERE id=?", $id);
    dev_history($id,'tiklandi'); audit('device',$id,'restore');
    flash('Tiklandi.');
}
if($act==='transfer' && can('edit')){
    $no = (int)($_POST['object_id'] ?? 0); $nd = (int)($_POST['department_id'] ?? 0); $ne = (int)($_POST['employee_id'] ?? 0);
    $nr = $no ? (int)one("SELECT region_id FROM objects WHERE id=?", $no) : $d['region_id'];
    if(scope_region() && $nr !== scope_region()){ flash('Boshqa hududga ko\'chira olmaysiz'); redirect('devices.php'); }
    q("UPDATE devices SET object_id=?, department_id=?, employee_id=?, region_id=? WHERE id=?",
      $no ?: null, $nd ?: null, $ne ?: null, $nr, $id);
    dev_history($id,'kochirish', array('object'=>$d['object_id'],'employee'=>$d['employee_id']),
      array('object'=>$no,'employee'=>$ne));
    audit('device',$id,'transfer');
    flash('Ko\'chirildi. Inventar raqami o\'zgarmadi.');
}
redirect($_SERVER['HTTP_REFERER'] ?? 'devices.php');
