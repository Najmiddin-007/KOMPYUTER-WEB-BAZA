<?php
require_once __DIR__ . '/includes/functions.php';
if(current_user()) redirect('dashboard.php');
$err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    $login = trim($_POST['login'] ?? ''); $pass = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $la = row("SELECT * FROM login_attempts WHERE login=?", $login);
    if($la && $la['blocked_until'] && strtotime($la['blocked_until']) > time()){
        $err = "Ko'p noto'g'ri urinish. " . $la['blocked_until'] . " gacha bloklangan.";
    } else {
        $u = row("SELECT * FROM users WHERE login=? AND active=1", $login);
        if($u && password_verify($pass, $u['password'])){
            q("DELETE FROM login_attempts WHERE login=?", $login);
            login_user($u);
            audit('user', $u['id'], 'login');
            redirect('dashboard.php');
        } else {
            $att = ($la['attempts'] ?? 0) + 1;
            $blocked = null;
            if($att >= 5){ $blocked = date('Y-m-d H:i:s', time()+900); $att = 0; }
            q("INSERT INTO login_attempts(login,attempts,blocked_until) VALUES(?,?,?) ON DUPLICATE KEY UPDATE attempts=?, blocked_until=?",
              $login,$att,$blocked,$att,$blocked);
            audit('user', $u['id'] ?? 0, 'login_failed');
            $err = "Login yoki parol noto'g'ri.";
        }
    }
}
?><!DOCTYPE html><html lang="uz"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kirish — <?= e(setting('app_name')) ?></title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="login-page">
<div class="login-box">
 <img src="assets/img/logo.png" alt="FVV" style="height:90px;display:block;margin:0 auto 6px">
 <h2><?= e(setting('org_name')) ?></h2>
 <p class="muted"><?= e(setting('app_name')) ?></p>
 <?php if($f = flash()): ?><div class="flash"><?= e($f) ?></div><?php endif; ?>
 <?php if($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
 <form method="post">
  <?= csrf_field() ?>
  <label>Login</label><input name="login" required autofocus>
  <label>Parol</label><input type="password" name="password" required>
  <button class="btn primary full">Kirish</button>
 </form>
 <p class="muted small">Boshlang'ich: admin / admin123 (kirgach o'zgartiring!)</p>
</div>
</body></html>
