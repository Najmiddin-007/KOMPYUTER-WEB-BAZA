<?php
require_once __DIR__.'/includes/header.php';
if(!can('objects')) redirect('dashboard.php');
if($_SERVER['REQUEST_METHOD']==='POST' && can('settings')){
    csrf_check();
    q("UPDATE regions SET lat=?, lng=? WHERE id=?", $_POST['lat'], $_POST['lng'], (int)$_POST['id']);
    audit('region',(int)$_POST['id'],'update_coords');
    flash('Koordinata yangilandi.');
}
$regs = rows("SELECT r.*, (SELECT COUNT(*) FROM objects o WHERE o.region_id=r.id AND o.archived=0) oc,
 (SELECT COUNT(*) FROM devices d WHERE d.region_id=r.id AND d.archived=0) dc FROM regions r ORDER BY r.id");
?>
<h1>Hududlar va obyektlar</h1>
<table class="tbl responsive">
<tr><th>#</th><th>Hudud</th><th>Obyektlar</th><th>Qurilmalar</th><th>Lat</th><th>Lng</th>
<?php if(can('settings')): ?><th></th><?php endif; ?></tr>
<?php foreach($regs as $r): ?>
<tr>
 <td><?= $r['id'] ?></td><td><b><?= e($r['name']) ?></b></td>
 <td><a href="objects.php?region=<?= $r['id'] ?>"><?= (int)$r['oc'] ?></a></td>
 <td><?= (int)$r['dc'] ?></td>
 <td><?= e($r['lat']) ?></td><td><?= e($r['lng']) ?></td>
 <?php if(can('settings')): ?>
 <td><form method="post" class="inline-form"><?= csrf_field() ?>
   <input type="hidden" name="id" value="<?= $r['id'] ?>">
   <input name="lat" value="<?= e($r['lat']) ?>" class="mini" required>
   <input name="lng" value="<?= e($r['lng']) ?>" class="mini" required>
   <button class="btn tiny">OK</button></form></td>
 <?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
<?php require_once __DIR__.'/includes/footer.php'; ?>
