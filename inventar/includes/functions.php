<?php
require_once __DIR__ . '/db.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function setting($k, $def=''){
    $r = row("SELECT svalue FROM settings WHERE skey=?", $k);
    return $r ? $r['svalue'] : $def;
}
function set_setting($k,$v){ q("INSERT INTO settings(skey,svalue) VALUES(?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)", $k, $v); }

if(session_status() === PHP_SESSION_NONE) session_start();

// --- sessiya timeout ---
$timeout = (int)setting('session_timeout', 30) * 60;
if(isset($_SESSION['user']) && isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout){
    session_unset(); session_destroy(); session_start();
    $_SESSION['flash'] = "Sessiya vaqti tugadi. Qayta kiring.";
}
$_SESSION['last_activity'] = time();

function current_user(){ return isset($_SESSION['user']) ? $_SESSION['user'] : null; }
function require_login(){
    if(!current_user()){ header('Location: index.php'); exit; }
}
function login_user($u){ $_SESSION['user'] = $u; q("UPDATE users SET last_login=NOW() WHERE id=?", $u['id']); }

function role_perms($role){
    switch($role){
        case 'admin': return array_keys($GLOBALS['PERM_KEYS']);
        case 'boshqarma': return array('view','add','edit','archive','restore','employees','objects','phones','import','export','pdf','stats','map','audit','inventory','chat','users');
        case 'hudud': return array('view','add','edit','archive','employees','objects','phones','export','pdf','stats','map','inventory','chat');
        default: return array('view','map','chat');
    }
}
function can($perm){
    $u = current_user();
    if(!$u) return false;
    $perms = role_perms($u['role']);
    if(!empty($u['permissions'])){
        $ov = json_decode($u['permissions'], true);
        if(is_array($ov)){
            if(!empty($ov['deny']) && in_array($perm, $ov['deny'])) return false;
            if(!empty($ov['allow'])) return in_array($perm, $ov['allow']);
        }
    }
    return in_array($perm, $perms);
}
// Hududiy cheklov: hudud foydalanuvchisi faqat o'z hududi
function scope_region(){ $u = current_user(); return ($u && $u['role']==='hudud') ? (int)$u['region_id'] : 0; }
function scoped_sql($col='d.region_id'){
    $r = scope_region();
    return $r ? " AND $col = $r " : "";
}

function csrf_token(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.csrf_token().'">'; }
function csrf_check(){
    if(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'){
        if(!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')){ http_response_code(403); die('CSRF himoyasi: ruxsat berilmagan so\'rov.'); }
    }
}
function flash($m=null){ if($m===null){ $x = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); return $x; } $_SESSION['flash']=$m; }
function redirect($u){ header("Location: $u"); exit; }

function audit($entity,$id,$action,$old=null,$new=null){
    $u = current_user(); $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $o = $old!==null ? (is_string($old)?$old:json_encode($old,JSON_UNESCAPED_UNICODE)) : null;
    $n = $new!==null ? (is_string($new)?$new:json_encode($new,JSON_UNESCAPED_UNICODE)) : null;
    q("INSERT INTO audit_log(entity,entity_id,action,old_value,new_value,user_id,ip) VALUES(?,?,?,?,?,?,?)",
      $entity,$id,$action,$o,$n,$u['id']??null,$ip);
}
function dev_history($device_id,$action,$old=null,$new=null){
    $u = current_user();
    q("INSERT INTO device_history(device_id,action,old_value,new_value,user_id) VALUES(?,?,?,?,?)",
      $device_id,$action,
      $old!==null?(is_string($old)?$old:json_encode($old,JSON_UNESCAPED_UNICODE)):null,
      $new!==null?(is_string($new)?$new:json_encode($new,JSON_UNESCAPED_UNICODE)):null,
      $u['id']??null);
}

// Inventar raqami generatsiya: INV-QQ.SSSSS yoki INV-B17.SSSSS
function generate_inv_no($object_id, $department_id, $manual=''){
    if($manual !== '') return $manual;
    if($department_id){
        $d = row("SELECT code FROM departments WHERE id=?", $department_id);
        $num = str_replace('-','', $d['code']); // B-17 -> B17
        $prefix = "INV-$num.";
    } else {
        $o = row("SELECT code FROM objects WHERE id=?", $object_id);
        $prefix = "INV-".($o['code']).".";
    }
    $cnt = (int)one("SELECT COUNT(*) FROM devices WHERE inventory_no LIKE ?", $prefix.'%');
    return $prefix . str_pad($cnt+1, 5, '0', STR_PAD_LEFT);
}

function type_name($code){ global $DEVICE_TYPES; return $DEVICE_TYPES[$code]['name'] ?? $code; }

// CSV (Excel uchun UTF-8 BOM)
function csv_out($filename, $header, $data){
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $out = fopen('php://output','w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, $header, ';');
    foreach($data as $r) fputcsv($out, $r, ';');
    fclose($out); exit;
}
