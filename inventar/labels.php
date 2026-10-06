<?php
require_once __DIR__.'/includes/header.php';
$reg = scope_region();
$devs = rows("SELECT inventory_no, model FROM devices WHERE archived=0 ".($reg?"AND region_id=$reg":"")." ORDER BY inventory_no LIMIT 200");
?>
<h1>QR/Barcode stikerlar (A4)</h1>
<p class="muted">Chop etishda brauzerda "Save as PDF" tanlang. Koddan skanerlash: <?= e(rtrim((!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']),'/')) ?>/device_view.php?id=... yoki faqat inventar raqami.</p>
<div class="labels">
<?php foreach($devs as $d): ?>
 <div class="label-item">
  <div class="qrmini" data-inv="<?= e($d['inventory_no']) ?>"></div>
  <svg class="bcmini" data-inv="<?= e($d['inventory_no']) ?>"></svg>
  <div class="small"><?= e($d['model']) ?></div>
 </div>
<?php endforeach; ?>
</div>
<button class="btn primary" onclick="window.print()">Chop etish / PDF</button>
<script>
document.querySelectorAll('.qrmini').forEach(el=>{
  if(typeof QRCode!=='undefined') new QRCode(el,{text:el.dataset.inv,width:70,height:70});
});
document.querySelectorAll('.bcmini').forEach(el=>{
  if(typeof JsBarcode!=='undefined') JsBarcode(el, el.dataset.inv, {format:'CODE128',height:24,width:1,fontSize:9,displayValue:true});
});
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?>
