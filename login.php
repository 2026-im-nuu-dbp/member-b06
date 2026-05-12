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
    <link rel="stylesheet" href="style.css">
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
