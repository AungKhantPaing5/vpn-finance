<?php
require_once 'config.php';
requireLogin();
$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id       = intval($_POST['id'] ?? 0);
$username = trim($_POST['vpn_username'] ?? '');
$start    = trim($_POST['start_date'] ?? '');
$end      = trim($_POST['end_date'] ?? '');
$amount   = floatval($_POST['amount'] ?? 0);
$expense  = floatval($_POST['expense'] ?? 0);
$note     = trim($_POST['note'] ?? '');

if ($username === '' || $start === '' || $end === '') {
    die('လိုအပ်သော အချက်အလက်များ ချန်ထားခဲ့ပါသည်။ <a href="index.php">ပြန်သွားရန်</a>');
}

if ($id > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE vpn_accounts SET vpn_username=?, start_date=?, end_date=?, amount=?, expense=?, note=? WHERE id=?");
    $stmt->bind_param('sssddsi', $username, $start, $end, $amount, $expense, $note, $id);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO vpn_accounts (vpn_username, start_date, end_date, amount, expense, note) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('sssdds', $username, $start, $end, $amount, $expense, $note);
}

$stmt->execute();
$stmt->close();

// redirect back to the month of the record just saved
$month = substr($start, 0, 7);
header('Location: index.php?month=' . urlencode($month));
exit;
