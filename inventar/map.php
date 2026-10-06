<?php
require_once __DIR__.'/includes/header.php';
if(!can('map')) redirect('dashboard.php');
// Offline xarita: lokal OSM pleynalari (assets/map_tiles) + Leaflet
$regs = rows("SELECT * FROM regions ORDER BY id");
$objs = rows("SELECT o.*, r.name reg_name, r.lat rlat, r.lng rlng,
 (SELECT COUNT(*) FROM devices d WHERE d.object_id=o.id AND d.archived=0) dc,
 (SELECT COUNT(*) FROM devices d WHERE d.object_id=o.id AND d.archived=0 AND d.status_id=3) bad
 FROM objects o LEFT JOIN regions r ON r.id=o.region_id WHERE o.archived=0");
$f_region = (int)($_GET['region'] ?? 0);
?>
<link rel="stylesheet" href="assets/leaflet/leaflet.css">
<script src="assets/leaflet/leaflet.js"></script>
<h1>Xarita — Qashqadaryo viloyati</h1>
<p class="muted small">Haqiqiy xarita (OpenStreetMap), lokal pleynalar — internet talab qilinmaydi. Belgini bosing — obyekt statistikasi chiqadi.</p>
<form class="filters" method="get">
 <select name="region"><option value="">— Barcha hududlar —</option>
 <?php foreach($regs as $r): ?><option value="<?= $r['id'] ?>" <?= $f_region===(int)$r['id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select>
 <button class="btn">Filtr</button>
</form>
<div class="map-wrap panel" id="mapWrap"></div>
<script>
const REGIONS = <?= json_encode($regs, JSON_UNESCAPED_UNICODE) ?>;
const OBJECTS = <?= json_encode(array_values(array_filter($objs, function($o) use($f_region){ return !$f_region || (int)$o['region_id']===$f_region; })), JSON_UNESCAPED_UNICODE) ?>;
const PICK = <?= isset($_GET['pick']) ? 'true' : 'false' ?>;

const map = L.map('mapWrap', {minZoom: 9, maxZoom: 11, zoomControl: true}).setView([38.85, 66.05], 9);
L.tileLayer('assets/map_tiles/{z}/{x}/{y}.png', {
  minZoom: 9, maxZoom: 11, attribution: '&copy; OpenStreetMap (lokal nusxa)'
}).addTo(map);

// hudud yorliqlari
REGIONS.forEach(r=>{
  const lat = parseFloat(r.lat), lng = parseFloat(r.lng);
  if(!lat || !lng) return;
  L.marker([lat, lng], {
    icon: L.divIcon({className: 'm-region-l', html: '<span>'+r.name.replace(' tumani','').replace(' shahri','')+'</span>', iconSize: [0,0]}),
    interactive: false
  }).addTo(map);
});

// obyekt belgilari
OBJECTS.forEach(o=>{
  const lat = parseFloat(o.lat)||parseFloat(o.rlat), lng = parseFloat(o.lng)||parseFloat(o.rlng);
  if(!lat || !lng) return;
  const bad = parseInt(o.bad)>0;
  const m = L.marker([lat, lng], {
    icon: L.divIcon({className: 'm-obj-l'+(bad?' bad':''), html: '<span title="'+o.name+'"></span>', iconSize: [0,0]})
  }).addTo(map);
  m.bindPopup('<div class="obj-popup"><b>'+o.name+'</b><br>'+(o.reg_name||'')+'<br>Qurilmalar: '+o.dc+
    (bad ? ' · <span style="color:#ef4444">⚠ noto\'g\'ri: '+o.bad+'</span>' : '')+
    '<br><a href="objects.php?region='+o.region_id+'">Hudud obyektlari</a> · <a href="devices.php?object='+o.id+'">Qurilmalar</a></div>');
});

if(PICK){
  map.on('click', e=>{
    prompt('Koordinata (nusxalang):', e.latlng.lat.toFixed(6)+', '+e.latlng.lng.toFixed(6));
  });
}
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?>
