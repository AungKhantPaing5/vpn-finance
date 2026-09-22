<!DOCTYPE html>
<html lang="my">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VPN Finance Management System</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<style>
    body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .navbar-brand { font-weight: 700; }
    .navbar-custom {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    }
    .stat-card {
        border-radius: 14px;
        border: none;
        color: #fff;
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }
    .stat-card .card-body { padding: 22px; }
    .stat-card i { font-size: 28px; opacity: 0.85; }
    .card-income { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .card-expense { background: linear-gradient(135deg, #eb3349, #f45c43); }
    .card-profit { background: linear-gradient(135deg, #2193b0, #6dd5ed); }
    .table-card {
        border-radius: 14px;
        border: none;
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .btn-primary-custom {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border: none; color: #fff;
    }
    .btn-primary-custom:hover { opacity: 0.9; color: #fff; }
    thead th { background: #eef1f7; }
</style>
</head>
<body>
<nav class="navbar navbar-dark navbar-custom mb-4 shadow">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="fa-solid fa-shield-halved me-2"></i>VPN Finance System</span>
        <div class="d-flex align-items-center gap-3">
            <a href="backup.php" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-database me-1"></i>Backup / Restore</a>
            <span class="text-white"><i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
            <a href="logout.php" class="btn btn-sm btn-light"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</a>
        </div>
    </div>
</nav>
<div class="container-fluid px-4 pb-5">
