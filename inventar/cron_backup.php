<?php
// Kunlik avtomatik backup: php cron_backup.php
require_once __DIR__.'/includes/db.php';
$c = db();
$tables = array_column(rows("SHOW TABLES"), 0);
$sql = "-- auto backup ".date('Y-m-d H:i:s')."\n\n";
foreach($tables as $t){
    $sql .= "DROP TABLE IF EXISTS `$t`;\n";
    $cr = row("SHOW CREATE TABLE `$t`");
    $sql .= $cr['Create Table'].";\n\n";
    $rs = q("SELECT * FROM `$t`");
    while($r = mysqli_fetch_assoc($rs)){
        $vals = array_map(function($v) use($c){ return $v===null?'NULL':"'".mysqli_real_escape_string($c,$v)."'"; }, array_values($r));
        $sql .= "INSERT INTO `$t` VALUES(".implode(',',$vals).");\n";
    }
    $sql .= "\n";
}
$dir = BACKUP_DIR.date('Y-m-d').'/';
if(!is_dir($dir)) mkdir($dir, 0777, true);
file_put_contents($dir.'database.sql', $sql);
echo "Backup OK: $dir\n";
