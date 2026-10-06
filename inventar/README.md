# Qashqadaryo viloyati FVV — Lokal IT-Inventar tizimi

Texnik shartga (versiya 1.0, 2026) asosan ishlab chiqilgan lokal web tizim.
Windows Server 2019 + XAMPP (Apache, PHP, MySQL/MariaDB, phpMyAdmin) muhitida, internetga
bog'lanmagan LAN tarmiqda ishlaydi.

## O'rnatish (XAMPP)

1. **XAMPP** o'rnating (PHP 8.x, MySQL/MariaDB bilan).
2. `C:\xampp\htdocs\` ichiga `inventar` papkasini ko'chiring (shu ZIP ichidagi barcha fayllar).
3. **Apache** va **MySQL** ni ishga tushiring.
4. Brauzerda `http://localhost/phpmyadmin` ochib, `install.sql` faylini import qiling
   (baza `inventar`, 16 hudud, 27 bo'lim, 43 obyekt, holatlar, admin foydalanuvchi yaratiladi).
5. `config/config.php` ichida DB_USER/DB_PASS ni kerak bo'lsa o'zgartiring (standart: root / bo'sh).
6. (Ixtiyoriy) Mahalliy domen uchun: `C:\Windows\System32\drivers\etc\hosts` fayliga qo'shing:
   `127.0.0.1 inventar.local` va XAMPP Apache configda VirtualHost o'rnating. Yoki shunchaki
   `http://localhost/inventar` dan foydalaning.

## Kirish

- Login: **admin** · Parol: **admin123** (kirgach darhol Sozlamalar orqali o'zgartiring!)

## Asosiy imkoniyatlar

- 16 hudud, 43 ta boshlang'ich qism/obyekt, 27 ta Boshqarma bo'limi (tayyor)
- 16 xil qurilma turi, turga xos maydonlar, ko'p rasm + asosiy rasm
- Avtomatik inventar raqamlash: INV-QQ.SSSSS va INV-B17.SSSSS, duplikatsiyaga yo'q
- QR-kod (Code128 Barcode bilan) — pasport sahifasi va A4 stikerlar chop etish
- Yillik inventarizatsiya kampaniyalari, skanerlash, dalolatnoma (PDF chop)
- Rollar: Administrator / Boshqarma / Shahar-tuman / Ko'rish + individual checkbox ruxsatlar
- Excel import (CSV, validation bilan) / export, rasmiy PDF hisobotlar (print orqali)
- Haqiqiy xarita (OpenStreetMap lokal pleynalari, 9–11 masshtabl, internetsiz), hudud filtri
- Audit tarix, arxivlash/tiklash, backup (baza+rasmlar ZIP), ichki chat/texnik yordam
- Dark/Light rejim, responsive (desktop/planshet/telefon), o'zbek tili

## Muhim eslatmalar

- **QR/Barcode JS kutubxonalar** (JsBarcode, qrcodejs) CDN orqali yuklanadi. To'liq offlinenda
  ishlatish uchun quyidagi fayllarni yuklab olib `assets/js/` ichiga qo'ying va
  `includes/header.php` dagi `<script src="https://...">` teglarini mahalliy yo'lga almashtiring:
  - https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js
  - https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js
- Kunlik backup uchun Windows Task Scheduler: `php C:\xampp\htdocs\inventar\cron_backup.php`
- Sessiya timeout (standart 30 daqiqa) Sozlamalar sahifasida o'zgaradi.
- O'chirish arxivlash orqali amalga oshiriladi; inventar raqami qayta ishlatilmaydi.
