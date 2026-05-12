<?php
require 'auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = '請填寫帳號與密碼。';
    } else {
        $user = getUserByUsername($username);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = '帳號或密碼錯誤。';
        } else {
            loginUser($user);
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title>登入 - 討論區</title>
    <style>
        body { font-family: system-ui, -apple-system, Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 420px; margin: 0 auto; }
        .card { background: #fff; padding: 24px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; color: #444; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #dcdcdc; border-radius: 6px; }
        button { width: 100%; background: #007bff; color: #fff; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .error { color: #b71c1c; margin-bottom: 16px; }
        .link { margin-top: 12px; display: block; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>會員登入</h1>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="username">帳號</label>
                    <input type="text" id="username" name="username" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="password">密碼</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">登入</button>
            </form>
            <a class="link" href="register.php">還沒有帳號？立即註冊</a>
            <a class="link" href="index.php">返回首頁</a>
        </div>
    </div>
</body>
</html>
