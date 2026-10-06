<?php
require_once __DIR__ . '/functions.php';
require_login();
$u = current_user();
$flash = flash();
$nav = array(
 'dashboard.php'=>'Bosh sahifa','regions.php'=>'Hududlar va obyektlar','devices.php'=>'Qurilmalar',
 'phones.php'=>'Telefon raqamlari','employees.php'=>'Xodimlar','map.php'=>'Xarita','inventory.php'=>'Inventarizatsiya',
 'stats.php'=>'Statistika','reports.php'=>'Hisobotlar','import.php'=>'Excel import','export.php'=>'Excel export',
 'audit.php'=>'Audit tarixi','archived.php'=>'Arxiv','backup.php'=>'Backup','chat.php'=>'Chat/Yordam','users.php'=>'Foydalanuvchilar','settings.php'=>'Sozlamalar');
$navperm = array('dashboard.php'=>'view','regions.php'=>'objects','devices.php'=>'view','phones.php'=>'phones',
 'employees.php'=>'employees','map.php'=>'map','inventory.php'=>'inventory','stats.php'=>'stats','reports.php'=>'pdf',
 'import.php'=>'import','export.php'=>'export','audit.php'=>'audit','archived.php'=>'restore','backup.php'=>'backup','chat.php'=>'chat',
 'users.php'=>'users','settings.php'=>'settings');
?>
<!DOCTYPE html><html lang="uz"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(setting('app_name')) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head><body>
<header class="topbar">
 <div class="brand"><img src="assets/img/logo.png" class="brand-logo" alt="FVV"> <span><?= e(setting('org_name')) ?> <span class="muted">| <?= e(setting('app_name')) ?></span></div>
 <div class="top-right">
  <button id="darkToggle" class="btn small" title="Dark/Light">🌙</button>
  <span class="user-badge"><?= e($u['fullname'] ?: $u['login']) ?> (<?= e($u['role']) ?>)</span>
  <a class="btn small danger" href="logout.php">Chiqish</a>
 </div>
</header>
<div class="layout">
<nav class="sidebar">
<?php foreach($nav as $href=>$label): if(!can($navperm[$href])) continue; ?>
 <a href="<?= $href ?>" class="<?= basename($_SERVER['PHP_SELF'])===$href?'active':'' ?>"><?= e($label) ?></a>
<?php endforeach; ?>
</nav>
<main class="content">
<?php if($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
