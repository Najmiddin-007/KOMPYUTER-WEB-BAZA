<?php
// ==== SOZLAMALAR (ozgartiring) ====
define('DB_HOST','localhost');
define('DB_NAME','inventar');
define('DB_USER','root');
define('DB_PASS','');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('BACKUP_DIR', __DIR__ . '/../backups/');

$DEVICE_TYPES = array(
 'kompyuter'  => array('name'=>'Kompyuter','fields'=>array('computer_serial'=>'Computer Serial','cpu'=>'CPU','ram'=>'RAM','hdd'=>'HDD/SSD','os'=>'OS','ip'=>'IP','mac'=>'MAC','monitor_serial'=>'Monitor Serial')),
 'laptop'     => array('name'=>'Laptop','fields'=>array('cpu'=>'CPU','ram'=>'RAM','hdd'=>'HDD/SSD','os'=>'OS','ip'=>'IP','mac'=>'MAC')),
 'monoblock'  => array('name'=>'Monoblock','fields'=>array('cpu'=>'CPU','ram'=>'RAM','hdd'=>'HDD/SSD','os'=>'OS','ip'=>'IP','mac'=>'MAC')),
 'server'     => array('name'=>'Server','fields'=>array('cpu'=>'CPU','ram'=>'RAM','disks'=>'Disklar soni','total_disk'=>'Umumiy disk hajmi','raid'=>'RAID','os'=>'OS','ip'=>'IP','mac'=>'MAC')),
 'radio'      => array('name'=>'Radio','fields'=>array('battery_serial'=>'Akkumulyator seriya','imei'=>'IMEI','sim'=>'SIM raqami','installed_place'=>"O'rnatilgan joy")),
 'tablet'     => array('name'=>'Tablet','fields'=>array('imei1'=>'IMEI1','imei2'=>'IMEI2','sim'=>'SIM','memory'=>'Xotira','ram'=>'RAM')),
 'ip_kamera'  => array('name'=>'IP kamera','fields'=>array('ip'=>'IP')),
 'switch'     => array('name'=>'Switch','fields'=>array('ports'=>'Portlar soni','ip'=>'IP')),
 'printer'    => array('name'=>'Printer','fields'=>array()),
 'nvr'        => array('name'=>'NVR','fields'=>array('ip'=>'IP','channels'=>'Kanal soni','hdd_count'=>'HDD soni','hdd_total'=>'Umumiy HDD')),
 'tv'         => array('name'=>'TV','fields'=>array('screen'=>"Ekran o'lchami")),
 'ups'        => array('name'=>'UPS','fields'=>array('power'=>'Quvvati (VA)')),
 'ip_telefon' => array('name'=>'IP telefon','fields'=>array('mac'=>'MAC','ip'=>'IP','internal_number'=>'Ichki raqam')),
 'telefon'    => array('name'=>'Oddiy telefon','fields'=>array()),
 'gpon'       => array('name'=>'GPON modem','fields'=>array('mac'=>'MAC','ip'=>'IP')),
 'boshqa'     => array('name'=>"Qo'shimcha qurilmalar",'fields'=>array('device_name'=>'Qurilma nomi')),
);
$PRINTER_TYPES = array('oddiy'=>'Oddiy','rangli'=>'Rangli','uch_bir'=>'3 tasi birda');
$OBJECT_TYPES  = array('Boshqarma','FVB','YOQQ','PYOQQ','OBYoXTTEB','Boshqa');
$PERM_KEYS = array('view'=>'Ko\'rish','add'=>'Qo\'shish','edit'=>'Tahrirlash','archive'=>'Arxivlash','restore'=>'Tiklash',
 'employees'=>'Xodimlar','objects'=>'Obyektlar','phones'=>'Telefonlar','import'=>'Excel import','export'=>'Excel export',
 'pdf'=>'PDF/Word hisobot','stats'=>'Statistika','map'=>'Xarita','audit'=>'Audit tarixi','backup'=>'Backup',
 'users'=>'Foydalanuvchilar','inventory'=>'Inventarizatsiya','chat'=>'Chat/Yordam','settings'=>'Sozlamalar');
