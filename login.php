<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');

    if ($u === ADMIN_USERNAME && $p === ADMIN_PASSWORD) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $u;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username (သို့) Password မှားနေပါတယ်။';
    }
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - VPN Finance</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', sans-serif;
    }
    .login-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        padding: 40px;
        width: 100%;
        max-width: 400px;
    }
    .login-icon {
        width: 70px; height: 70px;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        color: #fff; font-size: 28px;
    }
    .btn-login {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border: none;
    }
    .btn-login:hover { opacity: 0.9; color: #fff; }
</style>
</head>
<body>
    <div class="login-card">
        <div class="login-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h4 class="text-center mb-1 fw-bold">VPN Finance System</h4>
        <p class="text-center text-muted mb-4">Login ဝင်ရောက်ပါ</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login w-100 text-white py-2 fw-bold">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Login
            </button>
        </form>
    </div>
</body>
</html>
