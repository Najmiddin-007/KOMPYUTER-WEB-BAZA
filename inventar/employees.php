<?php
require_once __DIR__.'/includes/header.php';
if(!can('employees')) redirect('dashboard.php');
if($_SERVER['REQUEST_METHOD']==='POST' && can('edit')){
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $data = array(trim($_POST['fullname']), trim($_POST['position']), trim($_POST['phone']), trim($_POST['phone2']),
      (int)($_POST['region_id'] ?? 0) ?: null, (int)($_POST['department_id'] ?? 0) ?: null,
      (int)($_POST['object_id'] ?? 0) ?: null, trim($_POST['note']));
    if(scope_region()) $data[4] = scope_region();
    if($id){ q("UPDATE employees SET fullname=?,position=?,phone=?,phone2=?,region_id=?,department_id=?,object_id=?,note=? WHERE id=?", ...[...$data,$id]); audit('employee',$id,'update'); }
    else { q("INSERT INTO employees(fullname,position,phone,phone2,region_id,department_id,object_id,note) VALUES(?,?,?,?,?,?,?,?)", ...$data); audit('employee',(int)mysqli_insert_id(db()),'create'); }
    flash('Saqlandi.'); redirect('employees.php');
}
$edit = isset($_GET['edit']) ? row("SELECT * FROM employees WHERE id=?", (int)$_GET['edit']) : null;
$f = trim($_GET['q'] ?? '');
$w = "em.archived=0"; $p = array();
if($f !== ''){ $w .= " AND (em.fullname LIKE ? OR em.position LIKE ? OR em.phone LIKE ?)"; $p = array("%$f%","%$f%","%$f%"); }
$emps = rows("SELECT em.*, r.name reg_name, d.name dep_name, o.name obj_name,
 (SELECT COUNT(*) FROM devices dv WHERE dv.employee_id=em.id AND dv.archived=0) dc
 FROM employees em LEFT JOIN regions r ON r.id=em.region_id LEFT JOIN departments d ON d.id=em.department_id
 LEFT JOIN objects o ON o.id=em.object_id WHERE $w ORDER BY em.fullname LIMIT 500", ...$p);
$regs = rows("SELECT * FROM regions ORDER BY id"); $deps = rows("SELECT * FROM departments ORDER BY id");
$objs = rows("SELECT id,code,name FROM objects WHERE archived=0 ORDER BY code");
?>
<div class="page-head"><h1>Xodimlar</h1>
 <?php if(can('add')): ?><a class="btn primary" href="employees.php?new=1">+ Yangi xodim</a><?php endif; ?></div>
<?php if(isset($_GET['new']) || $edit): if(can('add')||can('edit')): $em = $edit ?: array(); ?>
<form method="post" class="form panel"><?= csrf_field() ?>
 <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
 <div class="grid3">
  <label>F.I.Sh. <input name="fullname" value="<?= e($em['fullname'] ?? '') ?>" required></label>
  <label>Lavozimi <input name="position" value="<?= e($em['position'] ?? '') ?>"></label>
  <label>Telefon <input name="phone" value="<?= e($em['phone'] ?? '') ?>"></label>
 </div>
 <div class="grid3">
  <label>Qo'shimcha telefon <input name="phone2" value="<?= e($em['phone2'] ?? '') ?>"></label>
  <?php if(!scope_region()): ?>
  <label>Hudud <select name="region_id"><option value="">—</option><?php foreach($regs as $r): ?>
   <option value="<?= $r['id'] ?>" <?= ($em['region_id'] ?? '')===(string)$r['id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></label>
  <?php else: ?><input type="hidden" name="region_id" value="<?= scope_region() ?>"><?php endif; ?>
  <label>Bo'lim <select name="department_id"><option value="">—</option><?php foreach($deps as $d): ?>
   <option value="<?= $d['id'] ?>" <?= ($em['department_id'] ?? '')===(string)$d['id']?'selected':'' ?>><?= e($d['code'].' — '.$d['name']) ?></option><?php endforeach; ?></select></label>
 </div>
 <div class="grid3">
  <label>Obyekt <select name="object_id"><option value="">—</option><?php foreach($objs as $o): ?>
   <option value="<?= $o['id'] ?>" <?= ($em['object_id'] ?? '')===(string)$o['id']?'selected':'' ?>><?= e($o['code'].' — '.$o['name']) ?></option><?php endforeach; ?></select></label>
 </div>
 <label>Izoh <textarea name="note" rows="2"><?= e($em['note'] ?? '') ?></textarea></label>
 <button class="btn primary">Saqlash</button> <a class="btn" href="employees.php">Bekor</a>
</form>
<?php endif; endif; ?>
<form class="filters" method="get">
 <input name="q" value="<?= e($f) ?>" placeholder="F.I.Sh., lavozim yoki telefon bo'yicha qidiruv...">
 <button class="btn">Qidirish</button>
</form>
<table class="tbl responsive">
<tr><th>F.I.Sh.</th><th>Lavozimi</th><th>Telefon</th><th>Qo'shimcha</th><th>Hudud</th><th>Bo'lim/Obyekt</th><th>Qurilmalar</th><th></th></tr>
<?php foreach($emps as $em): ?>
<tr><td><b><?= e($em['fullname']) ?></b></td><td><?= e($em['position']) ?></td>
<td><?= e($em['phone']) ?></td><td><?= e($em['phone2']) ?></td><td><?= e($em['reg_name']) ?></td>
<td><?= e($em['dep_name'] ?: $em['obj_name']) ?></td><td><?= (int)$em['dc'] ?></td>
<td class="nowrap"><?php if(can('edit')): ?><a class="btn tiny" href="employees.php?edit=<?= $em['id'] ?>">Tahrir</a><?php endif; ?></td></tr>
<?php endforeach; if(!$emps): ?><tr><td colspan="8" class="muted">Topilmadi</td></tr><?php endif; ?>
</table>
<?php require_once __DIR__.'/includes/footer.php'; ?>
