<?php
require_once __DIR__.'/includes/header.php';
if(!can('settings')) redirect('dashboard.php');
$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    $act = $_POST['act'] ?? '';
    if($act==='main'){
        set_setting('org_name', trim($_POST['org_name']));
        set_setting('app_name', trim($_POST['app_name']));
        set_setting('session_timeout', max(1,(int)$_POST['session_timeout']));
        audit('settings',0,'update');
        flash('Sozlamalar saqlandi.');
    }
    if($act==='status'){
        $sid = (int)($_POST['id'] ?? 0);
        if($sid){ q("UPDATE statuses SET name=?, color=?, ord=?, active=? WHERE id=?",
            trim($_POST['name']), $_POST['color'], (int)$_POST['ord'], isset($_POST['active'])?1:0, $sid); }
        else { q("INSERT INTO statuses(name,color,ord,active) VALUES(?,?,?,1)", trim($_POST['name']), $_POST['color'], (int)$_POST['ord']); }
        audit('status',$sid ?: (int)mysqli_insert_id(db()),'save');
        flash('Holat saqlandi.');
    }
    if($act==='seed' && $u['role']==='admin'){
        // Demo ma'lumot (test uchun)
        $oid = (int)one("SELECT id FROM objects WHERE code='01'");
        for($i=1;$i<=5;$i++){
            $inv = generate_inv_no($oid, null);
            if(!row("SELECT id FROM devices WHERE inventory_no=?", $inv))
                q("INSERT INTO devices(inventory_no,type_code,manufacturer,model,serial,status_id,region_id,object_id,created_by) VALUES(?,?,?,?,?,1,1,?,?)",
                  $inv,'kompyuter','Dell','OptiPlex 7010','SN-DEMO-'.$i,$oid,$u['id']);
        }
        audit('seed',0,'create');
        flash('Demo qurilmalar qo\'shildi (5 ta, Qarshi shahar 1-YOQQ).');
    }
}
$statuses = rows("SELECT * FROM statuses ORDER BY ord");
?>
<h1>Sozlamalar</h1>
<form method="post" class="form panel"><?= csrf_field() ?>
 <input type="hidden" name="act" value="main">
 <div class="grid3">
  <label>Tashkilot nomi <input name="org_name" value="<?= e(setting('org_name')) ?>"></label>
  <label>Tizim nomi <input name="app_name" value="<?= e(setting('app_name')) ?>"></label>
  <label>Sessiya timeout (daqiqa) <input type="number" name="session_timeout" value="<?= e(setting('session_timeout','30')) ?>" min="1"></label>
 </div>
 <button class="btn primary">Saqlash</button>
</form>
<h2>Qurilma holatlari</h2>
<form method="post" class="inline-form panel"><?= csrf_field() ?>
 <input type="hidden" name="act" value="status">
 <label>Nom <input name="name" required></label>
 <label>Rang <input type="color" name="color" value="#22c55e"></label>
 <label>Tartib <input type="number" name="ord" value="10" style="width:80px"></label>
 <button class="btn primary">+ Yangi holat</button>
</form>
<table class="tbl"><tr><th></th><th>Nom</th><th>Rang</th><th>Tartib</th><th>Faol</th><th></th></tr>
<?php foreach($statuses as $s): ?>
<tr><td><span class="dot" style="background:<?= e($s['color']) ?>"></span></td><td><?= e($s['name']) ?></td>
<td><code><?= e($s['color']) ?></code></td><td><?= (int)$s['ord'] ?></td><td><?= $s['active']?'✅':'—' ?></td>
<td><details><summary class="btn tiny">Tahrir</summary>
<form method="post" class="inline-form" style="margin-top:6px"><?= csrf_field() ?>
<input type="hidden" name="act" value="status"><input type="hidden" name="id" value="<?= $s['id'] ?>">
<input name="name" value="<?= e($s['name']) ?>" class="mini">
<input type="color" name="color" value="<?= e($s['color']) ?>">
<input type="number" name="ord" value="<?= (int)$s['ord'] ?>" class="mini" style="width:70px">
<label style="margin:0"><input type="checkbox" name="active" <?= $s['active']?'checked':'' ?> style="width:auto"> Faol</label>
<button class="btn tiny primary">OK</button></form></details></td></tr>
<?php endforeach; ?>
</table>
<?php if($u['role']==='admin'): ?>
<h2>Sinov ma'lumotlari</h2>
<form method="post" onsubmit="return confirm('Demo qurilmalar qo\'shilsinmi?')"><?= csrf_field() ?>
 <input type="hidden" name="act" value="seed">
 <button class="btn">5 ta demo qurilma qo'shish</button>
</form>
<?php endif; ?>
<?php require_once __DIR__.'/includes/footer.php'; ?>
