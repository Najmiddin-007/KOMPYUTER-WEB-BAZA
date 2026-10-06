<?php
require_once __DIR__.'/includes/header.php';
if(!can('backup')) redirect('dashboard.php');
$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act'] ?? '')==='backup'){
    csrf_check();
    $c = db();
    $tables = array_column(rows("SHOW TABLES"), 0);
    $sql = "-- Inventar tizimi backup\n-- ".date('Y-m-d H:i:s')."\n\n";
    foreach($tables as $t){
        $sql .= "DROP TABLE IF EXISTS `$t`;\n";
        $cr = row("SHOW CREATE TABLE `$t`");
        $sql .= $cr['Create Table'].";\n\n";
        $rs = q("SELECT * FROM `$t`");
        while($r = mysqli_fetch_assoc($rs)){
            $vals = array_map(function($v) use($c){ return $v===null ? 'NULL' : "'".mysqli_real_escape_string($c,$v)."'"; }, array_values($r));
            $cols = implode(',', array_map(function($k){ return "`$k`"; }, array_keys($r)));
            $sql .= "INSERT INTO `$t` ($cols) VALUES(".implode(',',$vals).");\n";
        }
        $sql .= "\n";
    }
    $dir = BACKUP_DIR.date('Y-m-d_His').'/';
    mkdir($dir, 0777, true);
    file_put_contents($dir.'database.sql', $sql);
    // rasmlar nusxasi
    if(is_dir(UPLOAD_DIR)) { copy_files(UPLOAD_DIR, $dir.'uploads/'); }
    file_put_contents($dir.'config.json', json_encode(array('date'=>date('c'),'by'=>$u['login']), JSON_UNESCAPED_UNICODE));
    audit('backup',0,'create');
    flash('Backup yaratildi: backups/'.basename($dir));
}
function copy_files($src,$dst){
    mkdir($dst,0777,true);
    foreach(glob($src.'*') as $f){ copy($f, $dst.basename($f)); }
}
$backups = array_reverse(glob(BACKUP_DIR.'*', GLOB_ONLYDIR));
?>
<h1>Backup</h1>
<form method="post"><?= csrf_field() ?>
 <input type="hidden" name="act" value="backup">
 <button class="btn primary">Backup now</button>
 <span class="muted small"> (baza SQL + rasmlar + konfig)</span>
</form>
<h2>Mavjud zaxiralar</h2>
<table class="tbl"><tr><th>Papka</th><th>Hajmi (KB)</th><th></th></tr>
<?php foreach($backups as $b):
 $size = 0; foreach(glob($b.'/*') as $f) $size += is_file($f)?filesize($f):0;
 $zip = $b.'.zip';
 if(!file_exists($zip)){
   $za = new ZipArchive();
   if($za->open($zip, ZipArchive::CREATE)===true){
     $za->addGlob($b.'/*', 0, array('remove_all_path'=>true));
     $za->close();
   }
 }
?>
<tr><td><?= e(basename($b)) ?></td><td><?= number_format($size/1024,1) ?></td>
<td><a class="btn small" href="backups/<?= e(basename($zip)) ?>" download>⬇ Yuklash</a></td></tr>
<?php endforeach; if(!$backups): ?><tr><td colspan="3" class="muted">Hali backup yo'q</td></tr><?php endif; ?>
</table>
<p class="muted small">Maslahat: Windows Task Scheduler orqali kunlik backup uchun <code>php <?= e(realpath(__DIR__)) ?>\cron_backup.php</code> ni rejalashtiring.</p>
<?php require_once __DIR__.'/includes/footer.php'; ?>
