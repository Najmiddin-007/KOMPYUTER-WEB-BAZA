<?php
require_once __DIR__.'/includes/header.php';
if(!can('users')) redirect('dashboard.php');
global $PERM_KEYS;
$err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    $act = $_POST['act'] ?? '';
    if($act==='save'){
        $id = (int)($_POST['id'] ?? 0);
        $login = trim($_POST['login']);
        $role = $_POST['role'];
        $perms = array('allow'=>array_values($_POST['perms'] ?? array()));
        if($role==='admin') $perms = null;
        $data = array($login, trim($_POST['fullname']), $role,
            ($role==='hudud') ? ((int)$_POST['region_id'] ?: null) : null,
            $perms ? json_encode($perms, JSON_UNESCAPED_UNICODE) : null);
        if($id){
            // oxirgi adminni pasaytirib yubormaslik
            if($role!=='admin'){
                $cnt = (int)one("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1 AND id<>?", $id);
                if(!$cnt){ $err = "Oxirgi Administrator huquhi o'zgartirilmasligi kerak!"; }
            }
            if(!$err){
                q("UPDATE users SET login=?,fullname=?,role=?,region_id=?,permissions=? WHERE id=?", ...[...$data,$id]);
                if(trim($_POST['password']) !== '') q("UPDATE users SET password=? WHERE id=?", password_hash($_POST['password'],PASSWORD_DEFAULT), $id);
                audit('user',$id,'update');
                flash('Yangilandi.');
            }
        } else {
            if(row("SELECT id FROM users WHERE login=?", $login)){ $err = "Bu login band."; }
            else {
                q("INSERT INTO users(login,fullname,role,region_id,permissions,password) VALUES(?,?,?,?,?,?)",
                  ...[...$data, password_hash($_POST['password'] ?: '12345678', PASSWORD_DEFAULT)]);
                audit('user',(int)mysqli_insert_id(db()),'create');
                flash("Yaratildi. Parol: ".($_POST['password'] ?: '12345678'));
            }
        }
        if(!$err) redirect('users.php');
    }
    if($act==='toggle' && $u['role']==='admin'){
        $id = (int)$_POST['id'];
        $t = row("SELECT * FROM users WHERE id=?", $id);
        if($t['role']==='admin' && $t['active']){
            $cnt = (int)one("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1 AND id<>?", $id);
            if(!$cnt){ flash("Oxirgi Administrator o'chirilmaydi!"); redirect('users.php'); }
        }
        q("UPDATE users SET active=1-active WHERE id=?", $id);
        audit('user',$id,'toggle_active');
        flash('Holati o\'zgartirildi.'); redirect('users.php');
    }
}
$edit = isset($_GET['edit']) ? row("SELECT * FROM users WHERE id=?", (int)$_GET['edit']) : null;
$users = rows("SELECT x.*, r.name reg_name FROM users x LEFT JOIN regions r ON r.id=x.region_id ORDER BY x.id");
$regs = rows("SELECT * FROM regions ORDER BY id");
$rolePerms = $edit ? role_perms($edit['role']) : role_perms('hudud');
$selPerms = $edit && $edit['permissions'] ? (json_decode($edit['permissions'],true)['allow'] ?? $rolePerms) : $rolePerms;
?>
<h1>Foydalanuvchilar</h1>
<?php if($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
<?php if(can('add')): ?>
<form method="post" class="form panel"><?= csrf_field() ?>
 <input type="hidden" name="act" value="save">
 <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
 <div class="grid3">
  <label>Login <input name="login" value="<?= e($edit['login'] ?? '') ?>" required></label>
  <label>F.I.Sh. <input name="fullname" value="<?= e($edit['fullname'] ?? '') ?>"></label>
  <label>Parol <?= $edit ? '(bo\'sh = o\'zgarmaydi)' : '(kamida 8 belgi)' ?>
   <input type="password" name="password" <?= $edit?'':'required' ?> minlength="8"></label>
 </div>
 <div class="grid3">
  <label>Rol
   <select name="role" id="roleSel">
    <?php foreach(array('admin'=>'Administrator','boshqarma'=>'Boshqarma','hudud'=>'Shahar/tuman','korish'=>"Ko'rish") as $k=>$v): ?>
     <option value="<?= $k ?>" <?= ($edit['role'] ?? 'hudud')===$k?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
   </select></label>
  <label id="regionRow" style="display:none">Hudud
   <select name="region_id"><?php foreach($regs as $r): ?>
    <option value="<?= $r['id'] ?>" <?= ($edit['region_id'] ?? '')===(string)$r['id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></label>
 </div>
 <div id="permBox"><label style="font-weight:600">Individual ruxsatlar (Rol: Administrator bo'lsa bekor qilinadi)</label>
  <div class="chips"><?php foreach($PERM_KEYS as $k=>$label): ?>
   <label class="chip" style="cursor:pointer"><input type="checkbox" name="perms[]" value="<?= e($k) ?>" <?= in_array($k,$selPerms)?'checked':'' ?>> <?= e($label) ?></label>
  <?php endforeach; ?></div>
 </div>
 <button class="btn primary">Saqlash</button> <a class="btn" href="users.php">Bekor</a>
</form>
<script>
function updRole(){
  const r = document.getElementById('roleSel').value;
  document.getElementById('regionRow').style.display = r==='hudud' ? '' : 'none';
  document.getElementById('permBox').style.display = r==='admin' ? 'none' : '';
}
document.getElementById('roleSel').addEventListener('change', updRole); updRole();
</script>
<?php endif; ?>
<table class="tbl responsive">
<tr><th>Login</th><th>F.I.Sh.</th><th>Rol</th><th>Hudud</th><th>Holat</th><th>So'nggi kirish</th><th></th></tr>
<?php foreach($users as $x): ?>
<tr class="<?= $x['active']?'':'muted' ?>">
 <td><?= e($x['login']) ?></td><td><?= e($x['fullname']) ?></td><td><?= e($x['role']) ?></td>
 <td><?= e($x['reg_name']) ?></td><td><?= $x['active']?'✅ Faol':'⛔ Bloklangan' ?></td><td><?= e($x['last_login']) ?></td>
 <td class="nowrap">
  <?php if(can('edit')): ?><a class="btn tiny" href="users.php?edit=<?= $x['id'] ?>">Tahrir</a><?php endif; ?>
  <?php if($u['role']==='admin'): ?>
  <form method="post" style="display:inline"><?= csrf_field() ?>
   <input type="hidden" name="act" value="toggle"><input type="hidden" name="id" value="<?= $x['id'] ?>">
   <button class="btn tiny danger"><?= $x['active']?'Bloklash':'Faollashtirish' ?></button></form>
  <?php endif; ?>
 </td></tr>
<?php endforeach; ?>
</table>
<p class="muted small">Eslatma: Boshqarma roli foydalanuvchi yarata oladi, lekin o'chira olmaydi. O'chirish o'rniga bloklash ishlatiladi.</p>
<?php require_once __DIR__.'/includes/footer.php'; ?>
