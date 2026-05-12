<?php
require 'auth.php';

$error = '';
$defaultColor = '#a8dadc';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $favorite_color = sanitizeColor($_POST['favorite_color'] ?? $defaultColor);
    $avatar = $_POST['avatar'] ?? '😃';

    if ($username === '' || $password === '' || $nickname === '') {
        $error = '帳號、密碼與暱稱為必填欄位。';
    } elseif (strlen($password) < 6) {
        $error = '密碼至少要 6 個字元。';
    } else {
        if (getUserByUsername($username)) {
            $error = '此帳號已存在，請換一組帳號名稱。';
        } else {
            if (!in_array($avatar, avatarOptions(), true)) {
                $avatar = '😃';
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $isAdmin = $username === 'member' ? 1 : 0;

            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, nickname, favorite_color, avatar, is_admin) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$username, $passwordHash, $nickname, $favorite_color, $avatar, $isAdmin]);

            $user = getUserByUsername($username);
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
    <title>註冊 - 討論區</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>會員註冊</h1>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <form method="post" action="register.php">
                <div class="form-group">
                    <label for="username">帳號</label>
                    <input type="text" id="username" name="username" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="password">密碼</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="nickname">暱稱</label>
                    <input type="text" id="nickname" name="nickname" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="favorite_color">喜歡顏色</label>
                    <input type="color" id="favorite_color" name="favorite_color" value="<?= htmlspecialchars($defaultColor, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="avatar">大頭貼</label>
                    <select id="avatar" name="avatar">
                        <?php foreach (avatarOptions() as $option): ?>
                            <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">註冊</button>
            </form>
            <a class="link" href="login.php">已經有帳號？立即登入</a>
            <a class="link" href="index.php">返回首頁</a>
        </div>
    </div>
</body>
</html>
