<?php
require_once __DIR__.'/includes/header.php';
global $OBJECT_TYPES;
if(!can('objects')) redirect('dashboard.php');
if($_SERVER['REQUEST_METHOD']==='POST' && can('edit')){
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $data = array(trim($_POST['name']), trim($_POST['code']), (int)$_POST['region_id'], $_POST['type'],
        ($_POST['lat']??'') ?: null, ($_POST['lng']??'') ?: null, trim($_POST['address']),
        trim($_POST['phone']), (int)($_POST['employee_id'] ?? 0) ?: null, trim($_POST['note']));
    if($id){
        q("UPDATE objects SET name=?,code=?,region_id=?,type=?,lat=?,lng=?,address=?,phone=?,employee_id=?,note=? WHERE id=?", ...[...$data,$id]);
        audit('object',$id,'update');
    } else {
        q("INSERT INTO objects(name,code,region_id,type,lat,lng,address,phone,employee_id,note) VALUES(?,?,?,?,?,?,?,?,?,?)", ...$data);
        audit('object',(int)mysqli_insert_id(db()),'create');
    }
    flash('Saqlandi.'); redirect('objects.php');
}
$edit = null;
if(isset($_GET['edit'])) $edit = row("SELECT * FROM objects WHERE id=?", (int)$_GET['edit']);
$f_region = (int)($_GET['region'] ?? 0);
$w = "o.archived=0"; $p = array();
if($f_region){ $w .= " AND o.region_id=?"; $p[] = $f_region; }
$objs = rows("SELECT o.*, r.name reg_name, e.fullname emp_name,
 (SELECT COUNT(*) FROM devices d WHERE d.object_id=o.id AND d.archived=0) dc
 FROM objects o LEFT JOIN regions r ON r.id=o.region_id LEFT JOIN employees e ON e.id=o.employee_id
 WHERE $w ORDER BY o.code", ...$p);
$regs = rows("SELECT * FROM regions ORDER BY id");
$emps = rows("SELECT * FROM employees WHERE archived=0 ORDER BY fullname");
?>
<div class="page-head"><h1>Obyektlar (43 ta boshlang'ich qism)</h1>
 <?php if(can('add')): ?><a class="btn primary" href="objects.php?new=1">+ Yangi obyekt</a><?php endif; ?>
 <a class="btn" href="objects.php?archived=1">Arxiv</a></div>

<?php if(isset($_GET['new']) || $edit): if(can('add')||can('edit')): $o = $edit ?: array(); ?>
<form method="post" class="form panel"><?= csrf_field() ?>
 <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
 <div class="grid3">
  <label>Kod <input name="code" value="<?= e($o['code'] ?? '') ?>" required></label>
  <label>Nomi <input name="name" value="<?= e($o['name'] ?? '') ?>" required></label>
  <label>Hudud
   <select name="region_id"><?php foreach($regs as $r): ?>
    <option value="<?= $r['id'] ?>" <?= ($o['region_id'] ?? '')===(string)$r['id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></label>
 </div>
 <div class="grid3">
  <label>Turi
   <select name="type"><?php foreach($OBJECT_TYPES as $t): ?>
    <option <?= ($o['type'] ?? '')===$t?'selected':'' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
  <label>Telefon <input name="phone" value="<?= e($o['phone'] ?? '') ?>"></label>
  <label>Mas'ul xodim
   <select name="employee_id"><option value="">—</option><?php foreach($emps as $em): ?>
    <option value="<?= $em['id'] ?>" <?= ($o['employee_id'] ?? '')===(string)$em['id']?'selected':'' ?>><?= e($em['fullname']) ?></option><?php endforeach; ?></select></label>
 </div>
 <div class="grid3">
  <label>Latitude <input name="lat" value="<?= e($o['lat'] ?? '') ?>" id="latInput"></label>
  <label>Longitude <input name="lng" value="<?= e($o['lng'] ?? '') ?>" id="lngInput"></label>
  <label>Manzil <input name="address" value="<?= e($o['address'] ?? '') ?>"></label>
 </div>
 <p class="muted small">Koordinatani xaritadan tanlash: <a href="map.php?pick=1" target="_blank">xarita</a> (bosgan nuqtangiz koordinatasini qo'lda kiriting).</p>
 <label>Izoh <textarea name="note" rows="2"><?= e($o['note'] ?? '') ?></textarea></label>
 <button class="btn primary">Saqlash</button> <a class="btn" href="objects.php">Bekor</a>
</form>
<?php endif; endif; ?>

<form class="filters" method="get">
 <select name="region"><option value="">— Barcha hududlar —</option>
 <?php foreach($regs as $r): ?><option value="<?= $r['id'] ?>" <?= $f_region===(int)$r['id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select>
 <button class="btn">Filtr</button>
</form>
<table class="tbl responsive">
<tr><th>Kod</th><th>Nomi</th><th>Turi</th><th>Hudud</th><th>Qurilmalar</th><th>Mas'ul</th><th>Telefon</th><th></th></tr>
<?php foreach($objs as $o): ?>
<tr><td><?= e($o['code']) ?></td><td><?= e($o['name']) ?></td><td><?= e($o['type']) ?></td>
<td><?= e($o['reg_name']) ?></td><td><?= (int)$o['dc'] ?></td><td><?= e($o['emp_name']) ?></td><td><?= e($o['phone']) ?></td>
<td class="nowrap"><?php if(can('edit')): ?><a class="btn tiny" href="objects.php?edit=<?= $o['id'] ?>">Tahrir</a><?php endif; ?></td></tr>
<?php endforeach; ?>
</table>
<?php require_once __DIR__.'/includes/footer.php'; ?>
