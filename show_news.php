<?php
// Display discussion content and replies

header('Content-Type: text/html; charset=utf-8');
require 'auth.php';
$user = currentUser();

$newsId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($newsId <= 0) {
    die('無效的討論 ID。<br><a href="index.php">返回首頁</a>');
}

try {
    $stmt = $pdo->prepare(
        'SELECT n.id, n.title, n.content, n.created_at, u.nickname, u.avatar, u.favorite_color
         FROM news n
         LEFT JOIN users u ON n.user_id = u.id
         WHERE n.id = ?'
    );
    $stmt->execute([$newsId]);
    $news = $stmt->fetch();

    if (!$news) {
        die('找不到此討論。<br><a href="index.php">返回首頁</a>');
    }
} catch (PDOException $e) {
    die('讀取討論失敗: ' . $e->getMessage());
}

try {
    $stmt = $pdo->prepare(
        'SELECT r.id, r.content, r.created_at, u.nickname, u.avatar, u.favorite_color
         FROM replies r
         LEFT JOIN users u ON r.user_id = u.id
         WHERE r.news_id = ?
         ORDER BY r.created_at ASC'
    );
    $stmt->execute([$newsId]);
    $replies = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = '讀取回應失敗: ' . $e->getMessage();
    $replies = [];
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title><?= escape($news['title']) ?> - 討論區</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <a class="back-link" href="index.php">← 返回討論列表</a>
            <div>
                <?php if ($user): ?>
                    <span>您好，<?= escape($user['nickname']) ?> <?= escape($user['avatar']) ?></span>
                    <?php if (isAdminUser($user)): ?>
                        <a href="admin.php">管理員介面</a>
                    <?php endif; ?>
                    <a href="logout.php">登出</a>
                <?php else: ?>
                    <a href="login.php">登入</a>
                    <a href="register.php">註冊</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="news-content" style="border-color: <?= escape($news['favorite_color'] ?: '#007bff') ?>;">
            <div class="news-title"><?= escape($news['title']) ?></div>
            <div class="news-meta">
                由 <strong><?= escape($news['nickname'] ?: '已刪除會員') ?> <?= escape($news['avatar'] ?: '👤') ?></strong> 發表於
                <?= escape($news['created_at']) ?>
            </div>
            <div class="news-body"><?= escape($news['content']) ?></div>
        </div>

        <div class="reply-section">
            <h2>回應 (<?= count($replies) ?>)</h2>
            <?php if (empty($replies)): ?>
                <p class="empty">目前沒有回應。</p>
            <?php else: ?>
                <?php foreach ($replies as $reply): ?>
                    <?php
                        $replyColor = $reply['favorite_color'] ?: '#f9f9f9';
                        $replyAuthor = $reply['nickname'] ?: '已刪除會員';
                        $replyAvatar = $reply['avatar'] ?: '👤';
                    ?>
                    <div class="reply-item" style="background: <?= escape($replyColor) ?>22; border-left-color: <?= escape($replyColor) ?>;">
                        <div class="reply-author">
                            <?= escape($replyAuthor) ?> <?= escape($replyAvatar) ?>
                            <span class="reply-time">- <?= escape($reply['created_at']) ?></span>
                        </div>
                        <div class="reply-content">
                            <?= escape($reply['content']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-box">
            <h2>發表回應</h2>
            <?php if ($user): ?>
                <form action="post_reply.php" method="post">
                    <input type="hidden" name="news_id" value="<?= $newsId ?>">
                    <div class="form-group">
                        <label>作者</label>
                        <input type="text" value="<?= escape($user['nickname']) ?> <?= escape($user['avatar']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="content">回應內容：</label>
                        <textarea id="content" name="content" required></textarea>
                    </div>
                    <button type="submit">送出回應</button>
                </form>
            <?php else: ?>
                <p class="info">請先 <a href="login.php">登入</a> 或 <a href="register.php">註冊</a>，才能發表回應。</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
