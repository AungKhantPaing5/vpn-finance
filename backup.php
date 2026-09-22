<?php
require_once 'config.php';
requireLogin();

$message = '';
$messageType = 'success';

// ================= BACKUP (Download .sql) =================
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $conn = getConnection();
    $tables = [];
    $res = $conn->query("SHOW TABLES");
    while ($row = $res->fetch_row()) { $tables[] = $row[0]; }

    $sql = "-- VPN Finance Database Backup\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        // structure
        $createRes = $conn->query("SHOW CREATE TABLE `$table`");
        $createRow = $createRes->fetch_row();
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $createRow[1] . ";\n\n";

        // data
        $dataRes = $conn->query("SELECT * FROM `$table`");
        while ($row = $dataRes->fetch_assoc()) {
            $cols = array_map(fn($c) => "`$c`", array_keys($row));
            $vals = array_map(function($v) use ($conn) {
                if ($v === null) return 'NULL';
                return "'" . $conn->real_escape_string($v) . "'";
            }, array_values($row));
            $sql .= "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
        }
        $sql .= "\n";
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

    $filename = "vpn_finance_backup_" . date('Ymd_His') . ".sql";
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($sql));
    echo $sql;
    exit;
}

// ================= RESTORE (Upload .sql) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            $message = '.sql file ကိုသာ upload လုပ်ပါ။';
            $messageType = 'danger';
        } else {
            $sqlContent = file_get_contents($file['tmp_name']);
            $conn = getConnection();
            if ($conn->multi_query($sqlContent)) {
                // clear remaining results
                do { } while ($conn->more_results() && $conn->next_result());
                $message = 'Database ကို အောင်မြင်စွာ Restore လုပ်ပြီးပါပြီ။';
                $messageType = 'success';
            } else {
                $message = 'Restore လုပ်ရာတွင် Error ဖြစ်ပါသည်: ' . $conn->error;
                $messageType = 'danger';
            }
        }
    } else {
        $message = 'File upload မအောင်မြင်ပါ။';
        $messageType = 'danger';
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card table-card mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-download me-2"></i>Backup (Database ကို download ဆွဲမည်)</h5>
                <p class="text-muted">Database ရှိ Table အားလုံးနှင့် Data အားလုံးကို .sql file အနေဖြင့် download ဆွဲနိုင်ပါသည်။</p>
                <a href="backup.php?action=download" class="btn btn-primary-custom">
                    <i class="fa-solid fa-download me-1"></i> Backup Download လုပ်မည်
                </a>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-upload me-2"></i>Restore (Database ကို ပြန်တင်မည်)</h5>
                <p class="text-danger small"><i class="fa-solid fa-triangle-exclamation me-1"></i>
                    သတိပြုပါ - Restore လုပ်လိုက်ပါက လက်ရှိ Data အချို့ ပြောင်းလဲသွားနိုင်ပါသည်။ Backup ယူပြီးမှသာ Restore ပြန်လုပ်ပါ။
                </p>
                <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Restore လုပ်မှာ သေချာပါသလား?');">
                    <div class="mb-3">
                        <input type="file" name="sql_file" accept=".sql" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fa-solid fa-upload me-1"></i> Restore တင်မည်
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-3">
            <a href="index.php" class="btn btn-light"><i class="fa-solid fa-arrow-left me-1"></i>Dashboard သို့ပြန်သွားရန်</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
