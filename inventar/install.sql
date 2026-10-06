CREATE DATABASE IF NOT EXISTS inventar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventar;

CREATE TABLE IF NOT EXISTS settings (skey VARCHAR(50) PRIMARY KEY, svalue TEXT) ENGINE=InnoDB;
INSERT INTO settings (skey,svalue) VALUES ('org_name','Qashqadaryo viloyati Favqulodda vaziyatlar boshqarmasi'),
 ('session_timeout','30'),('app_name','Lokal IT-Inventar va qurilmalar hisobi tizimi');

CREATE TABLE IF NOT EXISTS regions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 lat DECIMAL(10,7) DEFAULT NULL,
 lng DECIMAL(10,7) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS departments (
 id INT AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(10) NOT NULL UNIQUE,
 name VARCHAR(200) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS objects (
 id INT AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(10) NOT NULL UNIQUE,
 name VARCHAR(200) NOT NULL,
 region_id INT NOT NULL,
 type VARCHAR(50) DEFAULT 'Boshqa',
 lat DECIMAL(10,7) DEFAULT NULL,
 lng DECIMAL(10,7) DEFAULT NULL,
 address VARCHAR(255) DEFAULT NULL,
 phone VARCHAR(50) DEFAULT NULL,
 employee_id INT DEFAULT NULL,
 note TEXT,
 archived TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employees (
 id INT AUTO_INCREMENT PRIMARY KEY,
 fullname VARCHAR(120) NOT NULL,
 position VARCHAR(120) DEFAULT NULL,
 phone VARCHAR(50) DEFAULT NULL,
 phone2 VARCHAR(50) DEFAULT NULL,
 region_id INT DEFAULT NULL,
 department_id INT DEFAULT NULL,
 object_id INT DEFAULT NULL,
 note TEXT,
 archived TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS statuses (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(50) NOT NULL,
 color VARCHAR(7) DEFAULT '#22c55e',
 ord INT DEFAULT 0,
 active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;
INSERT INTO statuses (name,color,ord) VALUES
 ('Ishlamoqda','#22c55e',1),('Tamirlashda','#eab308',2),('Ishlamayapti','#ef4444',3),
 ('Omborda','#3b82f6',4),('Hisobdan chiqarilgan','#6b7280',5);

CREATE TABLE IF NOT EXISTS devices (
 id INT AUTO_INCREMENT PRIMARY KEY,
 inventory_no VARCHAR(40) NOT NULL UNIQUE,
 type_code VARCHAR(30) NOT NULL,
 manufacturer VARCHAR(100) DEFAULT NULL,
 model VARCHAR(100) DEFAULT NULL,
 serial VARCHAR(100) DEFAULT NULL,
 printer_type VARCHAR(30) DEFAULT NULL,
 status_id INT DEFAULT 1,
 region_id INT DEFAULT NULL,
 object_id INT DEFAULT NULL,
 department_id INT DEFAULT NULL,
 employee_id INT DEFAULT NULL,
 room VARCHAR(100) DEFAULT NULL,
 acquired DATE DEFAULT NULL,
 note TEXT,
 extra TEXT,
 images TEXT,
 main_image VARCHAR(255) DEFAULT NULL,
 archived TINYINT(1) DEFAULT 0,
 created_by INT DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_type (type_code), INDEX idx_serial (serial), INDEX idx_region (region_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS device_history (
 id INT AUTO_INCREMENT PRIMARY KEY,
 device_id INT NOT NULL,
 action VARCHAR(50) NOT NULL,
 old_value TEXT,
 new_value TEXT,
 user_id INT DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_log (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 entity VARCHAR(50) NOT NULL,
 entity_id INT DEFAULT NULL,
 action VARCHAR(50) NOT NULL,
 old_value TEXT,
 new_value TEXT,
 user_id INT DEFAULT NULL,
 ip VARCHAR(45) DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory_campaigns (
 id INT AUTO_INCREMENT PRIMARY KEY,
 year INT NOT NULL UNIQUE,
 status VARCHAR(20) DEFAULT 'ochiq',
 note VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory_results (
 id INT AUTO_INCREMENT PRIMARY KEY,
 campaign_id INT NOT NULL,
 device_id INT NOT NULL,
 result VARCHAR(30) DEFAULT 'topildi',
 note VARCHAR(255) DEFAULT NULL,
 scanned_by INT DEFAULT NULL,
 scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_camp_dev (campaign_id, device_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 login VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 fullname VARCHAR(100) DEFAULT NULL,
 role ENUM('admin','boshqarma','hudud','korish') DEFAULT 'korish',
 region_id INT DEFAULT NULL,
 permissions TEXT,
 active TINYINT(1) DEFAULT 1,
 last_login DATETIME DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- parol: admin123 (ozgartirish shart!)
INSERT INTO users (login,password,fullname,role) VALUES
 ('admin','$2y$10$3fCzvblrGOgTQYo9hV9qVOFQiy15QSghbynGLvdGPYFJL6aVOBi22','Administrator','admin');

CREATE TABLE IF NOT EXISTS login_attempts (
 login VARCHAR(50) PRIMARY KEY,
 attempts INT DEFAULT 0,
 blocked_until DATETIME DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tickets (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 region_id INT DEFAULT NULL,
 device_id INT DEFAULT NULL,
 subject VARCHAR(200) NOT NULL,
 status VARCHAR(20) DEFAULT 'Yangi',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ticket_messages (
 id INT AUTO_INCREMENT PRIMARY KEY,
 ticket_id INT NOT NULL,
 user_id INT NOT NULL,
 message TEXT,
 file VARCHAR(255) DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO regions (id,name,lat,lng) VALUES
 (1,'Qarshi shahri',38.8600,65.7900),(2,'Nishon tumani',39.6200,65.6900),
 (3,'Qarshi tumani',38.8000,65.9000),(4,'Koson tumani',39.0400,65.5800),
 (5,'Mirishkor tumani',39.2300,65.3300),(6,'Kasbi tumani',38.8200,65.4500),
 (7,'Muborak tumani',39.2600,65.1500),(8,'Dehqonobod tumani',38.3300,66.0300),
 (9,'Chiroqchi tumani',39.0300,66.0100),(10,'Qamashi tumani',38.8200,66.4600),
 (11,"G'uzor tumani",38.6200,66.2500),(12,'Shahrisabz shahri',39.0600,66.8300),
 (13,'Shahrisabz tumani',39.0000,66.9500),(14,'Kitob tumani',39.1300,66.8800),
 (15,"Yakkabog' tumani",38.9800,66.7000),(16,"Ko'kdala tumani",38.4600,65.5300);

INSERT INTO departments (code,name) VALUES
 ('B-01','Boshqarma boshligi''i'),('B-02','Boshqarma boshligi''ining birinchi o''rinbosari'),
 ('B-03','Profilaktika bo''yicha o''rinbosari'),('B-04','Kadrlar bo''yicha o''rinbosari'),
 ('B-05','Shaxsiy xavfsizlik bo''limi boshligi''i'),('B-06','Xizmat va jangovar tayyorgarlik bo''limi boshligi''i'),
 ('B-07','Tezkor va safarbarlik bo''lim boshligi''i'),('B-08','Maxsus qutqaruv kuchlari bo''limi boshligi''i'),
 ('B-09','Favqulodda vaziyatlarda boshqaruv markaz boshligi''i'),('B-10','Favqulodda vaziyatlar profilaktikasi bo''limi boshligi''i'),
 ('B-11','Shaharsozlik sohasida profilaktikasi bo''lim boshligi''i'),('B-12','RK va TBM bo''limi boshligi''i'),
 ('B-13','MA va TQT bo''linmasi katta inspektori'),('B-14','Ma''naviy ma''rifiy va tarbiyaviy ishlar boshligi''i'),
 ('B-15','Kadrlar bilan ta''minlash bo''limi boshligi''i'),('B-16','Tibbiyot xizmati boshligi''i'),
 ('B-17','Raqamlashtirish va xabar berish bo''limi boshligi''i'),('B-18','Tahlil bo''limi boshligi''i'),
 ('B-19','Ijro intizomi bo''limi boshligi''i'),('B-20','Axborot va jamoatchilik bilan aloqalar xizmati boshligi''i'),
 ('B-21','Moliya-iqtisod bo''limi boshligi''i'),('B-22','Texnik-ekspertiza markazi boshligi''i'),
 ('B-23','Maxsus qutqaruv otryadi komandiri'),('B-24','O''ta muhim va TOFVB bo''limi boshligi''i'),
 ('B-25','Maxfiy bo''linmasi boshligi''i'),('B-26','Bosh yuristkonsult'),('B-27','Faxriylar kengashi raisi');

INSERT INTO objects (code,name,region_id,type) VALUES
 ('01','Qarshi shahar FVB 1-YOQQ',1,'YOQQ'),('02','Qarshi shahar FVB 2-YOQQ',1,'YOQQ'),
 ('03','Qarshi shahar FVB 3-PYOQQ',1,'PYOQQ'),('04','Shahrisabz shahar FVB 4-YOQQ',12,'YOQQ'),
 ('05','G''uzor tumani FVB 5-PYOQQ',11,'PYOQQ'),('06','Dehqonobod tumani FVB 6-PYOQQ',8,'PYOQQ'),
 ('07','Qamashi tumani FVB 7-PYOQQ',10,'PYOQQ'),('08','Qarshi tumani 8-PYOQQ',3,'PYOQQ'),
 ('09','Koson tumani 9-YOQQ',4,'YOQQ'),('10','Koson tumani 10-PYOQQ',4,'PYOQQ'),
 ('11','Kitob tumani FVB 11-PYOQQ',14,'PYOQQ'),('12','Mirishkor tumani FVB 12-PYOQQ',5,'PYOQQ'),
 ('13','Mirishkor tumani 13-PYOQQ',5,'PYOQQ'),('14','Muborak tumani FVB 14-PYOQQ',7,'PYOQQ'),
 ('15','Nishon tumani FVB 15-PYOQQ',2,'PYOQQ'),('16','Nishon tumani 16-PYOQQ',2,'PYOQQ'),
 ('17','Kasbi tumani FVB 17-PYOQQ',6,'PYOQQ'),('18','Chiroqchi tumani 18-PYOQQ',9,'PYOQQ'),
 ('19','Ko''kdala tumani 19-PYOQQ',16,'PYOQQ'),('20','Shahrisabz tumani FVB 20-PYOQQ',13,'PYOQQ'),
 ('21','Shahrisabz tumani FVB 21-PYOQQ',13,'PYOQQ'),('22','Yakkabog'' tumani FVB 22-PYOQQ',15,'PYOQQ'),
 ('23','Yakkabog'' tumani 23-PYOQQ',15,'PYOQQ'),('24','MGQIZ OBYoXTTEB 24-qism',7,'OBYoXTTEB'),
 ('25','MGQIZ OBYoXTTEB 25-qism',7,'OBYoXTTEB'),('26','MGQIZ OBYoXTTEB 26-qism',7,'OBYoXTTEB'),
 ('27','MGQIZ OBYoXTTEB 27-qism',7,'OBYoXTTEB'),('28','Muborakneftgaz OBYoXTTEB 28-qism',7,'OBYoXTTEB'),
 ('29','Muborakneftgaz OBYoXTTEB 29-qism',7,'OBYoXTTEB'),('30','Muborakneftgaz OBYoXTTEB 30-qism',7,'OBYoXTTEB'),
 ('31','Muborakneftgaz OBYoXTTEB 31-qism',7,'OBYoXTTEB'),('32','Muborakneftgaz OBYoXTTEB 32-qism',7,'OBYoXTTEB'),
 ('33','Muborakneftgaz OBYoXTTEB 33-qism',7,'OBYoXTTEB'),('34','Talimarjon IES OBYoXTTEB 34-qism',2,'OBYoXTTEB'),
 ('35','Talimarjon IES OBYoXTTEB 35-qism',2,'OBYoXTTEB'),('36','Shurtaneftgaz OBYoXTTEB 36-qism',11,'OBYoXTTEB'),
 ('37','Shurtaneftgaz OBYoXTTEB 37-qism',11,'OBYoXTTEB'),('38','Shurtaneftgaz OBYoXTTEB 38-qism',11,'OBYoXTTEB'),
 ('39','Shurtangazkimyo majmuasi OBYoXTTEB 39-qism',11,'OBYoXTTEB'),
 ('40','Shurtangazkimyo majmuasi OBYoXTTEB 40-qism',11,'OBYoXTTEB'),
 ('41','Shurtangazkimyo majmuasi OBYoXTTEB 41-qism',11,'OBYoXTTEB'),
 ('42','DKO''Z OBYoXTTEB 42-qism',8,'OBYoXTTEB'),('43','GTL OBYoXTTEB 43-qism',2,'OBYoXTTEB');
