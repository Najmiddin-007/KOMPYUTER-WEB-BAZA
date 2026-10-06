<?php
require_once __DIR__ . '/../config/config.php';
function db(){
    static $c = null;
    if($c === null){
        $c = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if(!$c) die('Baza ulanishi xatosi: ' . mysqli_connect_error());
        mysqli_set_charset($c,'utf8mb4');
    }
    return $c;
}
function q($sql, ...$params){
    $c = db();
    if($params){
        $stmt = mysqli_prepare($c, $sql);
        if(!$stmt) die('SQL xato: '.mysqli_error($c));
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if($res) return $res;
        return mysqli_stmt_affected_rows($stmt) >= 0;
    }
    $res = mysqli_query($c, $sql);
    if(!$res) die('SQL xato: '.mysqli_error($c).' | '.$sql);
    return $res;
}
function row($sql, ...$p){ $r = q($sql,...$p); $x = mysqli_fetch_assoc($r); return $x ?: null; }
function rows($sql, ...$p){ $r = q($sql,...$p); $a = array(); while($x = mysqli_fetch_assoc($r)) $a[] = $x; return $a; }
function one($sql, ...$p){ $r = q($sql,...$p); $x = mysqli_fetch_row($r); return $x ? $x[0] : null; }
