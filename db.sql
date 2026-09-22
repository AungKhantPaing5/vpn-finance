-- VPN Finance Database Schema
-- ဒီ file ကို phpMyAdmin (သို့) mysql command line မှာ import လုပ်ပါ

CREATE DATABASE IF NOT EXISTS vpn_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vpn_finance;

CREATE TABLE IF NOT EXISTS vpn_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vpn_username VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,   -- ရငွေ (income/amount ရလာတာ)
    expense DECIMAL(14,2) NOT NULL DEFAULT 0.00,  -- ကုန်ကျစရိတ် (expense)
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample data (optional - လိုချင်ရင်ထားပါ၊ မလိုရင် ဖျက်ပစ်လို့ရပါတယ်)
-- INSERT INTO vpn_accounts (vpn_username, start_date, end_date, amount, expense, note)
-- VALUES ('user001', '2026-09-01', '2026-09-30', 15000, 5000, 'Sample record');
