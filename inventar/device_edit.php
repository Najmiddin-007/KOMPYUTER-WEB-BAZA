<?php
require_once __DIR__.'/includes/header.php';
global $DEVICE_TYPES, $PRINTER_TYPES;
if(!can('add') && !can('edit')) redirect('devices.php');
$id = (int)($_GET['id'] ?? 0);
$dev = $id ? row("SELECT * FROM devices WHERE id=?", $id) : null;
if($id && !$dev) redirect('devices.php');
if($dev && scope_region() && (int)$dev['region_id'] !== scope_region()){ flash("Bu hududga ruxsat yo'q."); redirect('devices.php'); }
$extra = $dev && $dev['extra'] ? json_decode($dev['extra'], true) : array();

if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    $tc = $_POST['type_code'] ?? 'boshqa';
    $ext = array();
    foreach(($DEVICE_TYPES[$tc]['fields'] ?? array()) as $k=>$label) $ext[$k] = trim($_POST['ext'][$k] ?? '');
    $imgs = $dev ? json_decode($dev['images'] ?? '[]', true) : array();
    if(!is_array($imgs)) $imgs = array();
    // rasm yuklash
    if(!empty($_FILES['photo']['name'])){
        $fn = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/','_', $_FILES['photo']['name']);
        if(move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR.$fn)){ $imgs[] = $fn; }
    }
    $main = $_POST['main_image'] ?? ($imgs ? $imgs[0] : null);
    $data = array(
      'type_code'=>$tc,
      'manufacturer'=>trim($_POST['manufacturer'] ?? ''),
      'model'=>trim($_POST['model'] ?? ''),
      'serial'=>trim($_POST['serial'] ?? ''),
      'printer_type'=>$tc==='printer' ? ($_POST['printer_type'] ?? null) : null,
      'status_id'=>(int)($_POST['status_id'] ?? 1),
      'region_id'=>(int)($_POST['region_id'] ?? 0) ?: null,
      'object_id'=>(int)($_POST['object_id'] ?? 0) ?: null,
      'department_id'=>(int)($_POST['department_id'] ?? 0) ?: null,
      'employee_id'=>(int)($_POST['employee_id'] ?? 0) ?: null,
      'room'=>trim($_POST['room'] ?? ''),
      'acquired'=>($_POST['acquired'] ?? '') ?: null,
      'note'=>trim($_POST['note'] ?? ''),
      'extra'=>json_encode($ext, JSON_UNESCAPED_UNICODE),
      'images'=>json_encode($imgs, JSON_UNESCAPED_UNICODE),
      'main_image'=>$main,
    );
    if(!$data['region_id'] && $data['object_id']){
        $o = row("SELECT region_id FROM objects WHERE id=?", $data['object_id']);
        $data['region_id'] = $o['region_id'] ?? null;
    }
    if(scope_region()) $data['region_id'] = scope_region();
    // duplicate nazorat
    $dup = row("SELECT id,inventory_no FROM devices WHERE (serial<>'' AND serial=? ) AND id<>?", $data['serial'], $id);
    if($data['serial'] && $dup){ flash("Bunday Serial allaqachon mavjud: ".$dup['inventory_no']); redirect(($id?"device_edit.php?id=$id":"device_edit.php")); }

    if($id){
        $old = $dev;
        q("UPDATE devices SET type_code=?,manufacturer=?,model=?,serial=?,printer_type=?,status_id=?,region_id=?,object_id=?,department_id=?,employee_id=?,room=?,acquired=?,note=?,extra=?,images=?,main_image=? WHERE id=?",
          $data['type_code'],$data['manufacturer'],$data['model'],$data['serial'],$data['printer_type'],$data['status_id'],$data['region_id'],$data['object_id'],$data['department_id'],$data['employee_id'],$data['room'],$data['acquired'],$data['note'],$data['extra'],$data['images'],$data['main_image'],$id);
        $changes = array();
        foreach(array('status_id','object_id','department_id','employee_id','region_id') as $k)
            if((string)$old[$k] !== (string)$data[$k]) $changes[$k] = array('old'=>$old[$k],'new'=>$data[$k]);
        if($changes) dev_history($id,'tahrirlash', $changes);
        audit('device',$id,'update',null,$data);
        flash("Qurilma yangilandi.");
    } else {
        $inv = generate_inv_no($data['object_id'], $data['department_id'], trim($_POST['manual_inv'] ?? ''));
        if(row("SELECT id FROM devices WHERE inventory_no=?", $inv)){ flash("Inventar raqam band: $inv"); redirect('device_edit.php'); }
        q("INSERT INTO devices(inventory_no,type_code,manufacturer,model,serial,printer_type,status_id,region_id,object_id,department_id,employee_id,room,acquired,note,extra,images,main_image,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
          $inv,$data['type_code'],$data['manufacturer'],$data['model'],$data['serial'],$data['printer_type'],$data['status_id'],$data['region_id'],$data['object_id'],$data['department_id'],$data['employee_id'],$data['room'],$data['acquired'],$data['note'],$data['extra'],$data['images'],$data['main_image'],$u['id']);
        $nid = (int)db()->insert_id ?? (int)mysqli_insert_id(db());
        dev_history($nid,'qoshildi', null, $inv);
        audit('device',$nid,'create',null,$inv);
        flash("Qo'shildi. Inventar raqami: $inv");
        redirect("device_view.php?id=$nid");
    }
}
$regions = rows("SELECT * FROM regions ORDER BY id");
$objects = rows("SELECT * FROM objects WHERE archived=0 ORDER BY code");
$deps = rows("SELECT * FROM departments ORDER BY id");
$emps = rows("SELECT * FROM employees WHERE archived=0 ORDER BY fullname");
$statuses = rows("SELECT * FROM statuses WHERE active=1 ORDER BY ord");
?>
<h1><?= $id ? 'Qurilmani tahrirlash' : 'Yangi qurilma' ?></h1>
<form method="post" enctype="multipart/form-data" class="form panel">
 <?= csrf_field() ?>
 <div class="grid3">
  <label>Turi
   <select name="type_code" id="typeSelect">
    <?php foreach($DEVICE_TYPES as $k=>$t): ?><option value="<?= e($k) ?>" <?= ($dev['type_code'] ?? 'kompyuter')===$k?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
   </select></label>
  <label>Holat
   <select name="status_id"><?php foreach($statuses as $s): ?>
    <option value="<?= $s['id'] ?>" <?= ($dev['status_id'] ?? 1)===(int)$s['id']?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></label>
  <label>Olingan sana <input type="date" name="acquired" value="<?= e($dev['acquired'] ?? '') ?>"></label>
 </div>
 <div class="grid3">
  <label>Ishlab chiqaruvchi <input name="manufacturer" value="<?= e($dev['manufacturer'] ?? '') ?>"></label>
  <label>Model <input name="model" value="<?= e($dev['model'] ?? '') ?>"></label>
  <label>Serial Number <input name="serial" value="<?= e($dev['serial'] ?? '') ?>"></label>
 </div>
 <div class="grid3" id="printerRow" style="display:none">
  <label>Printer turi
   <select name="printer_type"><?php foreach($PRINTER_TYPES as $k=>$v): ?>
    <option value="<?= e($k) ?>" <?= ($dev['printer_type'] ?? '')===$k?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label>
 </div>
 <div id="extraFields"></div>
 <div class="grid3">
  <?php if(!scope_region()): ?>
  <label>Hudud
   <select name="region_id"><option value="">—</option>
    <?php foreach($regions as $r): ?><option value="<?= $r['id'] ?>" <?= ($dev['region_id'] ?? '')===(string)$r['id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></label>
  <?php else: ?><input type="hidden" name="region_id" value="<?= scope_region() ?>"><?php endif; ?>
  <label>Obyekt (qism)
   <select name="object_id"><option value="">—</option>
    <?php foreach($objects as $o): ?><option value="<?= $o['id'] ?>" <?= ($dev['object_id'] ?? '')===(string)$o['id']?'selected':'' ?>><?= e($o['code'].' — '.$o['name']) ?></option><?php endforeach; ?></select></label>
  <label>Boshqarma bo'limi
   <select name="department_id"><option value="">—</option>
    <?php foreach($deps as $d): ?><option value="<?= $d['id'] ?>" <?= ($dev['department_id'] ?? '')===(string)$d['id']?'selected':'' ?>><?= e($d['code'].' — '.$d['name']) ?></option><?php endforeach; ?></select></label>
 </div>
 <div class="grid3">
  <label>Mas'ul xodim
   <select name="employee_id"><option value="">—</option>
    <?php foreach($emps as $em): ?><option value="<?= $em['id'] ?>" <?= ($dev['employee_id'] ?? '')===(string)$em['id']?'selected':'' ?>><?= e($em['fullname']) ?></option><?php endforeach; ?></select></label>
  <label>Xona/joy <input name="room" value="<?= e($dev['room'] ?? '') ?>"></label>
  <?php if(!$id): ?>
  <label>Inventar raqami (bo'sh = avto)
   <input name="manual_inv" placeholder="INV-01.00001 ko'rinishida"></label>
  <?php endif; ?>
 </div>
 <label>Rasm yuklash <input type="file" name="photo" accept="image/*"></label>
 <?php
   $imgs = $dev && $dev['images'] ? json_decode($dev['images'], true) : array();
   if($imgs): ?>
   <div class="thumbs"><?php foreach($imgs as $im): ?>
    <label class="thumb"><img src="uploads/<?= e($im) ?>"><br>
    <input type="radio" name="main_image" value="<?= e($im) ?>" <?= ($dev['main_image'] ?? '')===$im?'checked':'' ?>> Asosiy</label>
   <?php endforeach; ?></div>
 <?php endif; ?>
 <label>Izoh <textarea name="note" rows="3"><?= e($dev['note'] ?? '') ?></textarea></label>
 <button class="btn primary">Saqlash</button>
 <a class="btn" href="devices.php">Bekor qilish</a>
</form>
<script>
const TYPE_FIELDS = <?= json_encode(array_map(function($t){return $t['fields'];}, $DEVICE_TYPES), JSON_UNESCAPED_UNICODE) ?>;
const EXTRA_VALS = <?= json_encode($extra, JSON_UNESCAPED_UNICODE) ?>;
function renderExtra(){
  const tc = document.getElementById('typeSelect').value;
  const box = document.getElementById('extraFields');
  const pf = document.getElementById('printerRow');
  pf.style.display = tc==='printer' ? '' : 'none';
  let html = '<div class="grid3">';
  for(const [k,label] of Object.entries(TYPE_FIELDS[tc] || {})){
    const v = EXTRA_VALS[k] || '';
    html += `<label>${label} <input name="ext[${k}]" value="${v.replace(/"/g,'&quot;')}"></label>`;
  }
  box.innerHTML = html + '</div>';
}
document.getElementById('typeSelect').addEventListener('change', renderExtra);
renderExtra();
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?>
