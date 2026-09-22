<?php
// ==========================================
// CONFIG FILE - Database နဲ့ Admin Login Setting
// ==========================================
session_start();

// ---- Database Settings (သင့် server setting အတိုင်း ပြင်ပါ) ----
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'rootpassword');
define('DB_NAME', 'vpn_finance');

// ---- Admin Login (Username/Password ကို ဒီမှာပဲ အသေထားထားပါတယ်) ----
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '@dmin123');

// ---- Timezone ----
date_default_timezone_set('Asia/Yangon');

// ---- DB Connection Function ----
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('Database connection ချိတ်ဆက်မရပါ - ' . $conn->connect_error .
            ' (config.php ထဲက DB setting များကို စစ်ဆေးပါ)');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// ---- Login Check Function ----
function requireLogin() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: login.php');
        exit;
    }
}
