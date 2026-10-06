<?php
require_once __DIR__.'/includes/header.php';
if(!can('inventory')) redirect('dashboard.php');
$reg = scope_region();
if($_SERVER['REQUEST_METHOD']==='POST' && can('edit')){
    csrf_check();
    $act = $_POST['act'] ?? '';
    if($act==='new_campaign'){
        $y = (int)$_POST['year'];
        if(!row("SELECT id FROM inventory_campaigns WHERE year=?", $y)){
            q("INSERT INTO inventory_campaigns(year) VALUES(?)", $y);
            audit('inventory_campaign',$y,'create');
            flash("$y-yil kampaniyasi yaratildi.");
        } else flash('Bu yil allaqachon mavjud.');
    }
    if($act==='scan'){
        $cid = (int)$_POST['campaign_id']; $code = trim($_POST['code']);
        $d = row("SELECT id, inventory_no, object_id, region_id FROM devices WHERE inventory_no=? AND archived=0", $code);
        if(!$d){ $d = row("SELECT id, inventory_no, object_id, region_id FROM devices WHERE id=? AND archived=0", (int)$code); }
        if($d){
            if($reg && (int)$d['region_id'] !== $reg){ flash("Bu qurilma sizning hududingizga tegishli emas."); }
            else {
                $cur = (int)($_POST['current_object'] ?? 0);
                $res = 'topildi';
                if($cur && (int)$d['object_id'] !== $cur) $res = 'boshqa_obyekt';
                $ex = row("SELECT id,result FROM inventory_results WHERE campaign_id=? AND device_id=?", $cid, $d['id']);
                if($ex) q("UPDATE inventory_results SET result=?, note=?, scanned_by=?, scanned_at=NOW() WHERE id=?", $res, $_POST['note'] ?? '', $u['id'], $ex['id']);
                else q("INSERT INTO inventory_results(campaign_id,device_id,result,note,scanned_by) VALUES(?,?,?,?,?)", $cid, $d['id'], $res, $_POST['note'] ?? '', $u['id']);
                flash("✅ {$d['inventory_no']} — $res");
            }
        } else flash("❌ Kod topilmadi: $code");
    }
    if($act==='finish' && can('pdf')){
        $cid = (int)$_POST['campaign_id'];
        q("UPDATE inventory_campaigns SET status='yakunlangan' WHERE id=?", $cid);
        audit('inventory_campaign',$cid,'finish');
        flash('Kampaniya yakunlandi. Hisobot → Dalolatnoma.');
    }
}
$camps = rows("SELECT c.*, (SELECT COUNT(*) FROM inventory_results x WHERE x.campaign_id=c.id) scanned,
 (SELECT COUNT(*) FROM devices d WHERE d.archived=0 ".($reg?"AND d.region_id=$reg":"").") total FROM inventory_campaigns c ORDER BY c.year DESC");
$cur = (int)($_GET['campaign'] ?? 0);
$camp = $cur ? row("SELECT * FROM inventory_campaigns WHERE id=?", $cur) : ($camps ? $camps[0] : null);
$resList = array(); $stats = array();
if($camp){
    $resList = rows("SELECT x.*, d.inventory_no, d.model, d.type_code, o.name obj_name, e.fullname emp_name, u.login
     FROM inventory_results x JOIN devices d ON d.id=x.device_id LEFT JOIN objects o ON o.id=d.object_id
     LEFT JOIN employees e ON e.id=d.employee_id LEFT JOIN users u ON u.id=x.scanned_by
     WHERE x.campaign_id=? ORDER BY x.scanned_at DESC", $camp['id']);
    $stats = rows("SELECT result, COUNT(*) c FROM inventory_results WHERE campaign_id=? GROUP BY result", $camp['id']);
}
?>
<h1>Yillik inventarizatsiya</h1>
<?php if(can('add')): ?>
<form method="post" class="inline-form panel"><?= csrf_field() ?>
 <input type="hidden" name="act" value="new_campaign">
 <label>Yil: <input type="number" name="year" value="<?= date('Y')+1 ?>" style="width:90px"></label>
 <button class="btn primary">+ Yangi kampaniya</button>
</form>
<?php endif; ?>
<div class="chips">
<?php foreach($camps as $c): ?>
 <a class="chip <?= $camp && $camp['id']===$c['id']?'active':'' ?>" href="inventory.php?campaign=<?= $c['id'] ?>">
  <?= $c['year'] ?> · <?= e($c['status']) ?> · skan: <?= (int)$c['scanned'] ?></a>
<?php endforeach; ?>
</div>
<?php if($camp): ?>
<div class="grid2">
 <div class="panel">
  <h3>Skanerlash — <?= $camp['year'] ?></h3>
  <form method="post" class="form"><?= csrf_field() ?>
   <input type="hidden" name="act" value="scan"><input type="hidden" name="campaign_id" value="<?= $camp['id'] ?>">
   <label>QR/Barcode yoki inventar raqami
    <input name="code" id="scanInput" autofocus placeholder="INV-01.00001"></label>
   <label>Joriy obyekt (tekshirish uchun, ixtiyoriy)
    <select name="current_object"><option value="">—</option>
     <?php foreach(rows("SELECT id,name FROM objects WHERE archived=0 ORDER BY code") as $o): ?>
      <option value="<?= $o['id'] ?>"><?= e($o['name']) ?></option><?php endforeach; ?></select></label>
   <label>Izoh <input name="note"></label>
   <button class="btn primary">Belgila</button>
  </form>
  <?php $scannedIds = array_column(rows("SELECT device_id FROM inventory_results WHERE campaign_id=?", $camp['id']), 'device_id'); ?>
  <p class="muted small">Tekshirilmagan qurilmalar: <b><?= max(0, (int)($camp['total'] ?? 0) - count($scannedIds)) ?></b> / <?= (int)($camp['total'] ?? 0) ?></p>
 </div>
 <div class="panel">
  <h3>Natijalar statistikasi</h3>
  <?php foreach($stats as $st): ?>
   <div class="bar-row"><span class="bar-label"><?= e($st['result']) ?></span>
    <div class="bar"><div class="bar-fill" style="width:<?= min(100,$st['c']*10) ?>%"></div></div><b><?= (int)$st['c'] ?></b></div>
  <?php endforeach; if(!$stats): ?><p class="muted">Hali skanerlanmagan.</p><?php endif; ?>
  <?php if(can('pdf')): ?>
  <a class="btn" href="reports.php?type=inventory&campaign=<?= $camp['id'] ?>">Dalolatnoma (PDF)</a>
  <?php if($camp['status']==='ochiq'): ?>
  <form method="post" style="display:inline"><?= csrf_field() ?>
   <input type="hidden" name="act" value="finish"><input type="hidden" name="campaign_id" value="<?= $camp['id'] ?>">
   <button class="btn danger" onclick="return confirm('Yakunlaysizmi?')">Kampaniyani yakunlash</button></form>
  <?php endif; endif; ?>
 </div>
</div>
<table class="tbl responsive">
<tr><th>Inventar</th><th>Tur</th><th>Model</th><th>Obyekt</th><th>Mas'ul</th><th>Natija</th><th>Kim</th><th>Vaqt</th></tr>
<?php foreach($resList as $x): ?>
<tr><td><a href="device_view.php?id=<?= $x['device_id'] ?>"><?= e($x['inventory_no']) ?></a></td>
<td><?= e(type_name($x['type_code'])) ?></td><td><?= e($x['model']) ?></td><td><?= e($x['obj_name']) ?></td>
<td><?= e($x['emp_name']) ?></td><td><span class="res-<?= e($x['result']) ?>"><?= e($x['result']) ?></span></td>
<td><?= e($x['login']) ?></td><td><?= e($x['scanned_at']) ?></td></tr>
<?php endforeach; if(!$resList): ?><tr><td colspan="8" class="muted">Natijalar yo'q</td></tr><?php endif; ?>
</table>
<?php endif; ?>
<?php require_once __DIR__.'/includes/footer.php'; ?>
