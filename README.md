# VPN Finance Management System

VPN account တွေရဲ့ ဝင်ငွေ (Income) နဲ့ ကုန်ကျစရိတ် (Expense) ကို မှတ်တမ်းတင်ပြီး
လစဉ် အမြတ်/အရှုံး တွက်ချက်ဖို့အတွက် PHP + MySQL နဲ့ ရေးထားတဲ့ website ဖြစ်ပါတယ်။

## Features
- Login (Username: `admin`, Password: `admin` — hardcode ထားသည်)
- VPN Account CRUD (Username, Start Date, End Date, Amount, Expense, Note)
- လစဉ် Income / Expense / Profit အလိုအလျောက် တွက်ချက်ပေးခြင်း (Month filter ဖြင့် ရွေးနိုင်သည်)
- Name / VPN Username ဖြင့် ရှာဖွေခြင်း (စာလုံးတစ်စိတ်တစ်ပိုင်းဖြင့်လည်း ရှာနိုင်သည်)
- Start Date / End Date ရက်အပိုင်းအခြားဖြင့် ရှာဖွေခြင်း (တစ်ရက်တည်းရှာရန် From/To နှစ်ခုလုံးကို တူညီသောရက်ထည့်ပါ)
- All Months ကိုရွေးပြီး လအားလုံးမှ ရှာဖွေနိုင်သည်။ Name နှင့် Date filter တွဲသုံးနိုင်သည်။
- Checkbox / Select All ဖြင့် ရွေးချယ်ပြီး Delete Selected ဖြင့် ဖျက်ခြင်း၊ confirmation နှင့် CSRF protection ပါသည်
- Summary cards သည် ရွေးထားသောလ၏ totals ဖြစ်သည်။ Export သည် filter နှင့်ကိုက်ညီသောစာရင်းကို ထုတ်ပေးသည်။
- Excel (.xlsx) Export
- PDF Export
- Database Backup (.sql download) & Restore (.sql upload)

## လိုအပ်သောအရာများ (Requirements)
- PHP 7.4 ဒါမှမဟုတ် အထက် (mysqli extension ပါရမည်)
- MySQL / MariaDB
- Web server (Apache/Nginx) — ဥပမာ XAMPP, WAMP, Laragon, သို့မဟုတ် hosting server

## Setup လုပ်ရမည့် အဆင့်များ

### 1. Files များကို ကူးထည့်ခြင်း
ဒီ folder တစ်ခုလုံးကို သင့် web server ရဲ့ document root (ဥပမာ XAMPP ဆိုရင် `htdocs/vpn_finance`) ထဲကို ကူးထည့်ပါ။

### 2. Database ဖန်တီးခြင်း
1. phpMyAdmin ဖွင့်ပါ (သို့) mysql command line ဝင်ပါ
2. `db.sql` file ကို import လုပ်ပါ — ဒါဆို `vpn_finance` database နှင့် `vpn_accounts` table အလိုအလျောက် ဖန်တီးပေးပါလိမ့်မယ်

```
mysql -u root -p < db.sql
```

### 3. config.php ကို ပြင်ဆင်ခြင်း
`config.php` file ကိုဖွင့်ပြီး သင့် MySQL setting အတိုင်း ပြောင်းပါ -

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // သင့် MySQL password
define('DB_NAME', 'vpn_finance');
```

Login username/password ပြောင်းလိုပါက အောက်ပါနေရာမှာ ပြောင်းနိုင်ပါတယ် -
```php
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin');
```

### 4. Browser ထဲမှာ ဖွင့်ခြင်း
```
http://localhost/vpn_finance/login.php
```
Username: `admin`
Password: `admin`

## Folder Structure
```
vpn_finance/
├── config.php          -> Database + Login setting
├── db.sql               -> Database schema (import လုပ်ရန်)
├── login.php            -> Login page
├── logout.php           -> Logout
├── index.php            -> Main Dashboard (List, Add, Edit, Export)
├── save.php              -> Add/Edit form handler
├── delete.php            -> Delete handler
├── backup.php             -> Backup download + Restore upload
└── includes/
    ├── header.php
    └── footer.php
```

## သတိပြုရန်
- Production server (Live site) မှာ တင်မယ်ဆိုရင် `ADMIN_PASSWORD` ကို ပိုခက်ခဲအောင် ပြောင်းပါ၊
  ပြီးရင် HTTPS သုံးဖို့ အကြံပြုပါတယ်။
- Restore feature က database ကို overwrite (drop/insert) ဖြစ်စေနိုင်တာမို့ backup ယူပြီးမှသာ restore ပြန်လုပ်ပါ။
- Excel/PDF Export များကို browser ထဲမှာပဲ (client-side) generate လုပ်တာမို့ server မှာ extra library (Composer စတာ) install လုပ်ဖို့ မလိုအပ်ပါ။
