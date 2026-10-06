<?php
require_once __DIR__.'/includes/header.php';
if(!can('chat')) redirect('dashboard.php');
// Yangi murojaat
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act'] ?? '')==='new'){
    csrf_check();
    $dev = trim($_POST['device_inv'] ?? '');
    $did = null;
    if($dev !== ''){ $d = row("SELECT id FROM devices WHERE inventory_no=?", $dev); if($d) $did = $d['id']; }
    q("INSERT INTO tickets(user_id,region_id,device_id,subject) VALUES(?,?,?,?)",
      $u['id'], scope_region() ?: ($u['region_id'] ?? null), $did, trim($_POST['subject']));
    $tid = (int)mysqli_insert_id(db());
    if(trim($_POST['message']) !== '' || !empty($_FILES['file']['name'])){
        $file = null;
        if(!empty($_FILES['file']['name'])){
            $file = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/','_', $_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR.$file);
        }
        q("INSERT INTO ticket_messages(ticket_id,user_id,message,file) VALUES(?,?,?,?)", $tid, $u['id'], trim($_POST['message']), $file);
    }
    audit('ticket',$tid,'create');
    flash('Murojaat yuborildi.');
    redirect('chat.php?ticket='.$tid);
}
// Javob
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act'] ?? '')==='reply'){
    csrf_check();
    $tid = (int)$_POST['ticket_id'];
    $t = row("SELECT * FROM tickets WHERE id=?", $tid);
    $file = null;
    if(!empty($_FILES['file']['name'])){
        $file = time().'_'.preg_replace('/[^A-Za-z0-9_.-]/','_', $_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR.$file);
    }
    q("INSERT INTO ticket_messages(ticket_id,user_id,message,file) VALUES(?,?,?,?)", $tid, $u['id'], trim($_POST['message']), $file);
    if(!empty($_POST['status']) && can('edit')){ q("UPDATE tickets SET status=? WHERE id=?", $_POST['status'], $tid); }
    audit('ticket',$tid,'reply');
    redirect('chat.php?ticket='.$tid);
}
$tid = (int)($_GET['ticket'] ?? 0);
$prefillDev = (int)($_GET['device_id'] ?? 0);
$prefillInv = '';
if($prefillDev) $prefillInv = (string)(one("SELECT inventory_no FROM devices WHERE id=?", $prefillDev) ?? '');
// ko'rish huquqi
$tw = "1=1"; $tp = array();
if($u['role']==='hudud'){ $tw = "(t.user_id=? OR t.region_id=?)"; $tp = array($u['id'], $u['region_id']); }
$tickets = rows("SELECT t.*, u.login, d.inventory_no FROM tickets t LEFT JOIN users u ON u.id=t.user_id
 LEFT JOIN devices d ON d.id=t.device_id WHERE $tw ORDER BY (t.status='Yangi') DESC, t.id DESC", ...$tp);
?>
<div class="page-head"><h1>Ichki chat / Texnik yordam</h1></div>
<?php if(isset($_GET['new'])): ?>
<form method="post" enctype="multipart/form-data" class="form panel"><?= csrf_field() ?>
 <input type="hidden" name="act" value="new">
 <label>Mavzu <input name="subject" required></label>
 <label>Qurilma inventar raqami (ixtiyoriy) <input name="device_inv" value="<?= e($prefillInv) ?>" placeholder="INV-01.00001"></label>
 <label>Xabar <textarea name="message" rows="4"></textarea></label>
 <label>Fayl/rasm <input type="file" name="file"></label>
 <button class="btn primary">Yuborish</button> <a class="btn" href="chat.php">Bekor</a>
</form>
<?php else: ?>
<div class="chat-layout">
 <div>
  <a class="btn primary full" href="chat.php?new=1">+ Yangi murojaat</a>
  <div class="chat-list" style="margin-top:10px">
  <?php foreach($tickets as $t): ?>
   <a href="chat.php?ticket=<?= $t['id'] ?>" class="<?= $tid===(int)$t['id']?'active':'' ?>">
    <b><?= e($t['subject']) ?></b> <?= $t['status']==='Yangi'?'<span class="badge-n">Yangi</span>':'' ?><br>
    <small class="muted"><?= e($t['login']) ?> · <?= e($t['status']) ?><?= $t['inventory_no'] ? ' · '.e($t['inventory_no']) : '' ?></small>
   </a>
  <?php endforeach; if(!$tickets): ?><p class="muted">Murojaatlar yo'q</p><?php endif; ?>
  </div>
 </div>
 <div class="panel">
 <?php
 if($tid){
   $t = row("SELECT t.*, u.login, d.inventory_no FROM tickets t LEFT JOIN users u ON u.id=t.user_id LEFT JOIN devices d ON d.id=t.device_id WHERE t.id=?", $tid);
   $msgs = rows("SELECT m.*, u.login FROM ticket_messages m LEFT JOIN users u ON u.id=m.user_id WHERE m.ticket_id=? ORDER BY m.id", $tid);
 ?>
  <h3><?= e($t['subject']) ?> <span class="muted small">· <?= e($t['status']) ?><?= $t['inventory_no']?' · '.e($t['inventory_no']):'' ?></span></h3>
  <div class="chat-box">
  <?php foreach($msgs as $m): ?>
   <div class="msg <?= $m['user_id']===$u['id']?'me':'them' ?>">
    <?= nl2br(e($m['message'])) ?>
    <?php if($m['file']): $ext = strtolower(pathinfo($m['file'], PATHINFO_EXTENSION)); ?>
     <?php if(in_array($ext, array('jpg','jpeg','png','gif','webp'))): ?>
      <a href="uploads/<?= e($m['file']) ?>" target="_blank"><img src="uploads/<?= e($m['file']) ?>"></a>
     <?php else: ?><br><a href="uploads/<?= e($m['file']) ?>" download>📎 <?= e($m['file']) ?></a><?php endif; ?>
    <?php endif; ?>
    <small><?= e($m['login']) ?> · <?= e($m['created_at']) ?></small>
   </div>
  <?php endforeach; if(!$msgs): ?><p class="muted">Xabarlar yo'q</p><?php endif; ?>
  </div>
  <?php if(can('add')): ?>
  <form method="post" enctype="multipart/form-data" style="margin-top:10px"><?= csrf_field() ?>
   <input type="hidden" name="act" value="reply"><input type="hidden" name="ticket_id" value="<?= $tid ?>">
   <textarea name="message" rows="2" style="width:100%;padding:9px;border-radius:10px;border:1px solid var(--border);background:var(--panel-solid);color:var(--text)"></textarea>
   <div style="display:flex;gap:8px;margin-top:6px;align-items:center;flex-wrap:wrap">
    <input type="file" name="file">
    <?php if(can('edit')): ?>
    <select name="status" style="padding:7px;border-radius:8px">
     <?php foreach(array('Yangi','Korib_chiqilmoqda','Hal_qilindi','Yopildi') as $st): ?>
      <option <?= $t['status']===$st?'selected':'' ?>><?= e(str_replace('_',' ',$st)) ?></option><?php endforeach; ?>
    </select><?php endif; ?>
    <button class="btn primary">Javob yozish</button>
   </div>
  </form>
  <?php endif; ?>
 <?php } else { ?>
  <p class="muted">Murojaatni tanlang yoki yangisini yarating.</p>
 <?php } ?>
 </div>
</div>
<?php endif; ?>
<?php require_once __DIR__.'/includes/footer.php'; ?>
