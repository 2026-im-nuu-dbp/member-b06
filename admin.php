<?php
require 'auth.php';
requireAdmin();

$error = '';
$success = '';
$editUser = null;
$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $favorite_color = sanitizeColor($_POST['favorite_color'] ?? '#f5f5f5');
        $avatar = $_POST['avatar'] ?? '😃';
        $isAdmin = $username === 'member' ? 1 : 0;

        if ($username === '' || $password === '' || $nickname === '') {
            $error = '帳號、密碼與暱稱為必填欄位。';
        } elseif (getUserByUsername($username)) {
            $error = '此帳號已存在，請使用其他帳號。';
        } else {
            if (!in_array($avatar, avatarOptions(), true)) {
                $avatar = '😃';
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, nickname, favorite_color, avatar, is_admin) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$username, $passwordHash, $nickname, $favorite_color, $avatar, $isAdmin]);
            $success = '會員已新增。';
        }
    }

    if ($action === 'update') {
        $userId = intval($_POST['id'] ?? 0);
        $nickname = trim($_POST['nickname'] ?? '');
        $favorite_color = sanitizeColor($_POST['favorite_color'] ?? '#f5f5f5');
        $avatar = $_POST['avatar'] ?? '😃';
        $password = trim($_POST['password'] ?? '');

        if ($userId <= 0 || $nickname === '') {
            $error = '更新資料不完整。';
        } else {
            $user = getUserById($userId);
            if (!$user) {
                $error = '找不到該會員。';
            } else {
                if (!in_array($avatar, avatarOptions(), true)) {
                    $avatar = '😃';
                }
                $isAdmin = isAdminUser($user) ? 1 : 0;
                $passwordHash = null;
                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql = 'UPDATE users SET nickname = ?, favorite_color = ?, avatar = ?, is_admin = ?';
                $params = [$nickname, $favorite_color, $avatar, $isAdmin];
                if ($passwordHash !== null) {
                    $sql .= ', password_hash = ?';
                    $params[] = $passwordHash;
                }
                $sql .= ' WHERE id = ?';
                $params[] = $userId;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = '會員資料已更新。';
            }
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $deleteId = intval($_GET['id']);
    if ($deleteId === currentUser()['id']) {
        $error = '無法刪除目前登入的管理員。';
    } else {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$deleteId]);
        $success = '會員已刪除。';
    }
}

if ($action === 'edit' && isset($_GET['id'])) {
    $editId = intval($_GET['id']);
    $editUser = getUserById($editId);
}

$users = $pdo->query('SELECT id, username, nickname, favorite_color, avatar, is_admin, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title>會員管理 - 討論區</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <h1>會員管理</h1>
            <a class="link" href="index.php">返回首頁</a>
        </div>

        <div class="card">
            <?php if ($error): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

            <?php if ($editUser): ?>
                <h2>編輯會員：<?= htmlspecialchars($editUser['username'], ENT_QUOTES, 'UTF-8') ?></h2>
                <form method="post" action="admin.php?action=update">
                    <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                    <div class="form-group">
                        <label>帳號</label>
                        <input type="text" value="<?= htmlspecialchars($editUser['username'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="nickname">暱稱</label>
                        <input type="text" id="nickname" name="nickname" value="<?= htmlspecialchars($editUser['nickname'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="favorite_color">喜歡顏色</label>
                        <input type="color" id="favorite_color" name="favorite_color" value="<?= htmlspecialchars($editUser['favorite_color'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="avatar">大頭貼</label>
                        <select id="avatar" name="avatar">
                            <?php foreach (avatarOptions() as $option): ?>
                                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= $option === $editUser['avatar'] ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">新密碼（留空表示不變）</label>
                        <input type="password" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label>管理員狀態</label>
                        <input type="text" value="<?= isAdminUser($editUser) ? '是' : '否' ?>" disabled>
                    </div>
                    <button type="submit">儲存變更</button>
                </form>
            <?php else: ?>
                <h2>新增會員</h2>
                <form method="post" action="admin.php?action=create">
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
                        <input type="color" id="favorite_color" name="favorite_color" value="#a8dadc">
                    </div>
                    <div class="form-group">
                        <label for="avatar">大頭貼</label>
                        <select id="avatar" name="avatar">
                            <?php foreach (avatarOptions() as $option): ?>
                                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>管理員規則</label>
                        <input type="text" value="只有帳號 member 會是管理員" disabled>
                    </div>
                    <button type="submit">新增會員</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>會員列表</h2>
            <table>
                <thead>
                    <tr>
                        <th>帳號</th>
                        <th>暱稱</th>
                        <th>大頭貼</th>
                        <th>顏色</th>
                        <th>管理員</th>
                        <th>建立時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['nickname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:<?= htmlspecialchars($user['favorite_color'], ENT_QUOTES, 'UTF-8') ?>;"></span></td>
                            <td><?= isAdminUser($user) ? '是' : '否' ?></td>
                            <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="actions">
                                <a href="admin.php?action=edit&id=<?= $user['id'] ?>">編輯</a>
                                <?php if ($user['id'] !== currentUser()['id']): ?>
                                    <a href="admin.php?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('確定要刪除此會員？');">刪除</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
