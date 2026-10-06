<?php
require_once __DIR__.'/includes/header.php';
if(!can('restore') && !can('archive')) redirect('dashboard.php');
$reg = scope_region();
$devs = rows("SELECT d.*, s.name st_name FROM devices d LEFT JOIN statuses s ON s.id=d.status_id
 WHERE d.archived=1 ".($reg?"AND d.region_id=$reg":"")." ORDER BY d.updated_at DESC LIMIT 300");
?>
<h1>Arxiv (tiklash)</h1>
<table class="tbl responsive">
<tr><th>Inventar</th><th>Tur</th><th>Model</th><th>Serial</th><th>Holat</th><th>Arxiv vaqti</th><th></th></tr>
<?php foreach($devs as $d): ?>
<tr><td><a href="device_view.php?id=<?= $d['id'] ?>"><?= e($d['inventory_no']) ?></a></td>
<td><?= e(type_name($d['type_code'])) ?></td><td><?= e($d['model']) ?></td><td><?= e($d['serial']) ?></td>
<td><?= e($d['st_name']) ?></td><td><?= e($d['updated_at']) ?></td>
<td><?php if(can('restore')): ?>
<form method="post" action="device_actions.php" style="display:inline"><?= csrf_field() ?>
<input type="hidden" name="act" value="restore"><input type="hidden" name="id" value="<?= $d['id'] ?>">
<button class="btn tiny">Tiklash</button></form><?php endif; ?></td></tr>
<?php endforeach; if(!$devs): ?><tr><td colspan="7" class="muted">Arxiv bo'sh</td></tr><?php endif; ?>
</table>
<?php require_once __DIR__.'/includes/footer.php'; ?>
