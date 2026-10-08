<?php
require_once 'config.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the account list to delete records.');
}
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || empty($_SESSION['delete_csrf']) || !hash_equals($_SESSION['delete_csrf'], $token)) {
    http_response_code(403);
    exit('Invalid request. Reload the account list and try again.');
}
$rawIds = isset($_POST['id']) ? [$_POST['id']] : ($_POST['ids'] ?? []);
if (!is_array($rawIds)) $rawIds = [];
$ids = [];
foreach ($rawIds as $value) {
    if (!is_scalar($value)) continue;
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id !== false) $ids[] = $id;
}
$ids = array_values(array_unique($ids));
if ($ids) {
    $conn = getConnection();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("DELETE FROM vpn_accounts WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $_SESSION['delete_notice'] = $stmt->affected_rows . ' record(s) deleted.';
    $stmt->close();
}
$query = [];
if (is_string($_POST['return_query'] ?? null)) parse_str($_POST['return_query'], $query);
$query = array_intersect_key($query, array_flip(['month', 'name', 'date_field', 'date_from', 'date_to', 'all_months']));
header('Location: index.php' . ($query ? '?' . http_build_query($query) : ''), true, 303);
exit;
