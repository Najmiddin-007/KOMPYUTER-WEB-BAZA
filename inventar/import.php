<?php
require_once __DIR__.'/includes/header.php';
if(!can('import')) redirect('dashboard.php');
global $DEVICE_TYPES;
$result = null;
if($_SERVER['REQUEST_METHOD']==='POST' && !empty($_FILES['csv']['tmp_name'])){
    csrf_check();
    $tc = $_POST['type_code'] ?? 'boshqa';
    $fields = array_keys($DEVICE_TYPES[$tc]['fields']);
    $handle = fopen($_FILES['csv']['tmp_name'],'r');
    $first = fgetcsv($handle, 0, ';'); // header
    if($first && strpos($first[0],"\xEF\xBB\xBF")===0) $first[0] = substr($first[0],3);
    $ok=0; $warn=0; $errors=array(); $line=1;
    while(($r = fgetcsv($handle, 0, ';')) !== false){
        $line++;
        if(count($r) < 5){ $errors[] = "Qator $line: kam ustun"; continue; }
        $manual = trim($r[0]);
        $manuf = trim($r[2]); $model = trim($r[3]); $serial = trim($r[4]);
        if($serial && row("SELECT id FROM devices WHERE serial=? AND archived=0", $serial)){
            $warn++; $errors[] = "Qator $line: duplicate serial ($serial)"; continue;
        }
        $extra = array();
        foreach($fields as $i=>$f) $extra[$f] = trim($r[5+$i] ?? '');
        $inv = generate_inv_no(null, null, $manual);
        if(row("SELECT id FROM devices WHERE inventory_no=?", $inv)){ $warn++; $errors[]="Qator $line: inventar band ($inv)"; continue; }
        q("INSERT INTO devices(inventory_no,type_code,manufacturer,model,serial,status_id,extra,created_by) VALUES(?,?,?,?,?,1,?,?)",
          $inv,$tc,$manuf,$model,$serial,json_encode($extra,JSON_UNESCAPED_UNICODE),$u['id']);
        $ok++;
    }
    fclose($handle);
    audit('import',0,'csv',null,"ok=$ok warn=$warn");
    $result = array('ok'=>$ok,'warn'=>$warn,'errors'=>$errors);
}
?>
<h1>Excel import (CSV)</h1>
<p class="muted">Shablonni <a href="export.php">Export</a> sahifasidan oling. Ustunlar: inventar(bo'sh=avto);tur;ishlab_chiqaruvchi;model;serial;[qo'shimcha maydonlar...];holat;hudud;obyekt;...</p>
<?php if($result): ?>
<div class="panel">
 <p>✅ Muvaffaqiyatli: <b><?= $result['ok'] ?></b> · ⚠️ Ogohlantirish: <b><?= $result['warn'] ?></b> · ❌ Xatolar: <b><?= count($result['errors']) ?></b></p>
 <?php if($result['errors']): ?>
  <p><a class="btn small" href="data:text/plain;charset=utf-8,<?= rawurlencode(implode("\n", $result['errors'])) ?>" download="xatolar.txt">Xatolar ro'yxatini yuklash</a></p>
  <ul class="small"><?php foreach(array_slice($result['errors'],0,50) as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
 <?php endif; ?>
</div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form panel">
 <?= csrf_field() ?>
 <label>Qurilma turi
  <select name="type_code"><?php foreach($DEVICE_TYPES as $k=>$t): ?>
   <option value="<?= e($k) ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></label>
 <label>CSV fayl (separator: ;) <input type="file" name="csv" accept=".csv" required></label>
 <button class="btn primary">Import (preview + validation bilan)</button>
</form>
<?php require_once __DIR__.'/includes/footer.php'; ?>
