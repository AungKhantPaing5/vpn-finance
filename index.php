<?php
require_once 'config.php';
requireLogin();
$conn = getConnection();

// ---- Month filter (default = current month) ----
$selectedMonth = is_string($_GET['month'] ?? null) ? $_GET['month'] : date('Y-m');
$selectedMonth = preg_match('/^\d{4}-\d{2}$/', $selectedMonth) ? $selectedMonth : date('Y-m');

// ---- Monthly summary totals (based on start_date's month) ----
$stmt = $conn->prepare("SELECT
        COALESCE(SUM(amount),0) AS total_income,
        COALESCE(SUM(expense),0) AS total_expense,
        COUNT(*) AS total_rows
    FROM vpn_accounts WHERE DATE_FORMAT(start_date, '%Y-%m') = ?");
$stmt->bind_param('s', $selectedMonth);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalIncome  = (float)$summary['total_income'];
$totalExpense = (float)$summary['total_expense'];
$totalProfit  = $totalIncome - $totalExpense;

// ---- All-time overall totals ----
$overallRes = $conn->query("SELECT COALESCE(SUM(amount),0) AS inc, COALESCE(SUM(expense),0) AS exp FROM vpn_accounts");
$overall = $overallRes->fetch_assoc();

// ---- Search filters (summary cards remain monthly totals) ----
$name = is_string($_GET['name'] ?? null) ? trim($_GET['name']) : '';
$dateField = ($_GET['date_field'] ?? '') === 'end_date' ? 'end_date' : 'start_date';
function validSearchDate($value) {
    if (!is_string($value)) return '';
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : '';
}
$dateFrom = validSearchDate($_GET['date_from'] ?? '');
$dateTo = validSearchDate($_GET['date_to'] ?? '');
$allMonths = isset($_GET['all_months']);
$where = [];
$params = [];
if (!$allMonths) {
    $where[] = "DATE_FORMAT(start_date, '%Y-%m') = ?";
    $params[] = $selectedMonth;
}
if ($name !== '') {
    $where[] = "LOCATE(LOWER(?), LOWER(vpn_username)) > 0";
    $params[] = $name;
}
if ($dateFrom !== '') { $where[] = "$dateField >= ?"; $params[] = $dateFrom; }
if ($dateTo !== '') { $where[] = "$dateField <= ?"; $params[] = $dateTo; }
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
if (empty($_SESSION['delete_csrf'])) $_SESSION['delete_csrf'] = bin2hex(random_bytes(32));
$returnQuery = http_build_query([
    'month' => $selectedMonth, 'name' => $name, 'date_field' => $dateField,
    'date_from' => $dateFrom, 'date_to' => $dateTo
] + ($allMonths ? ['all_months' => '1'] : []));
$deleteNotice = $_SESSION['delete_notice'] ?? '';
unset($_SESSION['delete_notice']);

// ---- Fetch matching records ----
$stmt = $conn->prepare("SELECT * FROM vpn_accounts" . $whereSql . " ORDER BY start_date DESC, id DESC");
if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$records = $stmt->get_result();
$stmt->close();

include 'includes/header.php';
?>

<!-- Month Filter -->
<form method="GET" class="row g-2 align-items-center mb-4">
    <div class="col-auto">
        <label class="col-form-label fw-bold">လ ရွေးပါ -</label>
    </div>
    <div class="col-auto">
        <input type="month" name="month" value="<?php echo htmlspecialchars($selectedMonth); ?>" class="form-control" >
    </div>
    <div class="col-auto">
        <label for="searchName" class="form-label">Name / VPN Username</label>
        <input id="searchName" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" placeholder="Search name">
    </div>
    <div class="col-auto">
        <label for="dateField" class="form-label">Date Type</label>
        <select id="dateField" name="date_field" class="form-select">
            <option value="start_date" <?php echo $dateField === 'start_date' ? 'selected' : ''; ?>>Start Date</option>
            <option value="end_date" <?php echo $dateField === 'end_date' ? 'selected' : ''; ?>>End Date</option>
        </select>
    </div>
    <div class="col-auto">
        <label for="dateFrom" class="form-label">From Date</label>
        <input id="dateFrom" type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
    </div>
    <div class="col-auto">
        <label for="dateTo" class="form-label">To Date</label>
        <input id="dateTo" type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
    </div>
    <div class="col-auto">
        <label><input type="checkbox" name="all_months" value="1" <?php echo $allMonths ? 'checked' : ''; ?>> All Months</label>
    </div>
    <div class="col-auto">
        <a class="btn btn-outline-secondary" href="index.php">Reset</a>
        <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-filter me-1"></i>Filter</button>
    </div>
</form>

<?php if ($deleteNotice !== ''): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($deleteNotice); ?></div>
<?php endif; ?>
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card card-income">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="small opacity-75">လစဉ် ဝင်ငွေ (Income)</div>
                    <div class="fs-4 fw-bold"><?php echo number_format($totalIncome, 2); ?> Ks</div>
                </div>
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card card-expense">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="small opacity-75">လစဉ် ကုန်ကျစရိတ် (Expense)</div>
                    <div class="fs-4 fw-bold"><?php echo number_format($totalExpense, 2); ?> Ks</div>
                </div>
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card card-profit">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="small opacity-75">လစဉ် အမြတ် (Profit)</div>
                    <div class="fs-4 fw-bold"><?php echo number_format($totalProfit, 2); ?> Ks</div>
                </div>
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
    </div>
</div>

<div class="text-muted mb-3 small">
    Overall (အားလုံး) — Income: <b><?php echo number_format($overall['inc'],2); ?> Ks</b> |
    Expense: <b><?php echo number_format($overall['exp'],2); ?> Ks</b> |
    Profit: <b><?php echo number_format($overall['inc']-$overall['exp'],2); ?> Ks</b>
</div>

<!-- Table Card -->
<div class="card table-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list me-2"></i>VPN Account List <span class="badge bg-secondary"><?php echo $records->num_rows; ?></span></h5>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-success btn-sm" onclick="exportExcel()"><i class="fa-solid fa-file-excel me-1"></i>Excel Export</button>
                <button class="btn btn-danger btn-sm" onclick="exportPDF()"><i class="fa-solid fa-file-pdf me-1"></i>PDF Export</button>
                <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#recordModal" onclick="openAddModal()">
                    <i class="fa-solid fa-plus me-1"></i>အသစ်ထည့်မည်
                </button>
            </div>
        </div>

        <form id="deleteForm" method="POST" action="delete.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['delete_csrf']); ?>">
            <input type="hidden" name="return_query" value="<?php echo htmlspecialchars($returnQuery); ?>">
            <button id="deleteSelected" type="submit" class="btn btn-outline-danger btn-sm mb-3" disabled>Delete Selected (0)</button>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="vpnTable">
                <thead>
                    <tr>
                        <th class="no-export"><input type="checkbox" id="selectAll" aria-label="Select all matching users"></th>
                        <th>#</th>
                        <th>VPN Username</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Amount (ဝင်ငွေ)</th>
                        <th>Expense (ကုန်ကျ)</th>
                        <th>Profit</th>
                        <th>Note</th>
                        <th class="no-export">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($records->num_rows === 0): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">ရှာဖွေမှုနှင့် ကိုက်ညီသော Record မရှိပါ</td></tr>
                <?php else: $i=1; while ($row = $records->fetch_assoc()):
                    $profit = $row['amount'] - $row['expense'];
                ?>
                    <tr>
                        <td class="no-export"><input type="checkbox" class="record-select" name="ids[]" value="<?php echo (int)$row['id']; ?>" aria-label="Select <?php echo htmlspecialchars($row['vpn_username']); ?>"></td>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['vpn_username']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td><?php echo number_format($row['amount'],2); ?></td>
                        <td><?php echo number_format($row['expense'],2); ?></td>
                        <td class="fw-bold <?php echo $profit>=0?'text-success':'text-danger'; ?>"><?php echo number_format($profit,2); ?></td>
                        <td><?php echo htmlspecialchars($row['note']); ?></td>
                        <td class="no-export">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick='openEditModal(<?php echo json_encode($row, JSON_HEX_APOS|JSON_HEX_QUOT); ?>)'>
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="submit" name="id" value="<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-danger" aria-label="Delete user">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
        </form>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="recordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="save.php">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">VPN Account အသစ်ထည့်မည်</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="f_id">
            <div class="mb-3">
                <label class="form-label">VPN Username</label>
                <input type="text" name="vpn_username" id="f_username" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="f_start" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" id="f_end" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Amount (ရငွေ)</label>
                    <input type="number" step="0.01" name="amount" id="f_amount" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Expense (ကုန်ကျ)</label>
                    <input type="number" step="0.01" name="expense" id="f_expense" class="form-control" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Note (မှတ်ချက် - ရွေးချယ်ခွင့်)</label>
                <input type="text" name="note" id="f_note" class="form-control">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-custom">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Export Libraries (SheetJS for Excel, jsPDF for PDF) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
const selections = Array.from(document.querySelectorAll('.record-select'));
const selectAll = document.getElementById('selectAll');
const deleteSelected = document.getElementById('deleteSelected');
function updateSelection() {
    const count = selections.filter(box => box.checked).length;
    deleteSelected.disabled = count === 0;
    deleteSelected.textContent = 'Delete Selected (' + count + ')';
    selectAll.checked = count > 0 && count === selections.length;
    selectAll.indeterminate = count > 0 && count < selections.length;
    selectAll.disabled = selections.length === 0;
}
selectAll.addEventListener('change', () => {
    selections.forEach(box => { box.checked = selectAll.checked; });
    updateSelection();
});
selections.forEach(box => box.addEventListener('change', updateSelection));
document.getElementById('deleteForm').addEventListener('submit', event => {
    const single = event.submitter && event.submitter.name === 'id';
    const count = single ? 1 : selections.filter(box => box.checked).length;
    if (!count || !confirm('ရွေးထားသော Record ' + count + ' ခုကို ဖျက်မှာ သေချာပါသလား?')) {
        event.preventDefault();
        return;
    }
    // Single delete must not also delete checked rows.
    if (single) selections.forEach(box => { box.disabled = true; });
});
updateSelection();

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'VPN Account အသစ်ထည့်မည်';
    document.getElementById('f_id').value = '';
    document.getElementById('f_username').value = '';
    document.getElementById('f_start').value = '';
    document.getElementById('f_end').value = '';
    document.getElementById('f_amount').value = '';
    document.getElementById('f_expense').value = '';
    document.getElementById('f_note').value = '';
}

function openEditModal(row) {
    document.getElementById('modalTitle').innerText = 'VPN Account ပြင်ဆင်မည်';
    document.getElementById('f_id').value = row.id;
    document.getElementById('f_username').value = row.vpn_username;
    document.getElementById('f_start').value = row.start_date;
    document.getElementById('f_end').value = row.end_date;
    document.getElementById('f_amount').value = row.amount;
    document.getElementById('f_expense').value = row.expense;
    document.getElementById('f_note').value = row.note ?? '';
    var modal = new bootstrap.Modal(document.getElementById('recordModal'));
    modal.show();
}

// ---- Export to Excel ----
function exportExcel() {
    const table = document.getElementById('vpnTable').cloneNode(true);
    // remove action column
    table.querySelectorAll('tr').forEach(tr => {
        const cells = tr.querySelectorAll('th.no-export, td.no-export');
        cells.forEach(c => c.remove());
    });
    const wb = XLSX.utils.table_to_book(table, { sheet: "VPN Finance" });
    XLSX.writeFile(wb, "vpn_finance_<?php echo $selectedMonth; ?>.xlsx");
}

// ---- Export to PDF ----
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape' });
    doc.setFontSize(14);
    doc.text("VPN Finance Report - <?php echo $selectedMonth; ?>", 14, 15);

    const rows = [];
    document.querySelectorAll('#vpnTable tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td:not(.no-export)');
        if (cells.length) {
            const rowData = [];
            cells.forEach(td => rowData.push(td.innerText));
            rows.push(rowData);
        }
    });

    doc.autoTable({
        head: [['#','VPN Username','Start Date','End Date','Amount','Expense','Profit','Note']],
        body: rows,
        startY: 22,
        styles: { fontSize: 9 }
    });

    doc.text("Monthly Income: <?php echo number_format($totalIncome,2); ?> Ks   |   Expense: <?php echo number_format($totalExpense,2); ?> Ks   |   Profit: <?php echo number_format($totalProfit,2); ?> Ks",
        14, doc.lastAutoTable.finalY + 10);

    doc.save("vpn_finance_<?php echo $selectedMonth; ?>.pdf");
}
</script>

<?php include 'includes/footer.php'; ?>
