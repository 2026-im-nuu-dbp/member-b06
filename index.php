<?php
// Read and display discussion topics

header('Content-Type: text/html; charset=utf-8');
require 'auth.php';
$user = currentUser();

try {
    $stmt = $pdo->query(
        'SELECT n.id, n.title, n.created_at, u.nickname, u.avatar, u.favorite_color, COUNT(r.id) AS reply_count
         FROM news n
         LEFT JOIN replies r ON n.id = r.news_id
         LEFT JOIN users u ON n.user_id = u.id
         GROUP BY n.id, n.title, n.created_at, u.nickname, u.avatar, u.favorite_color
         ORDER BY n.created_at DESC'
    );
    $news = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = '讀取討論失敗: ' . $e->getMessage();
    $news = [];
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title>討論區</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <h1>📋 討論區</h1>
            <div>
                <?php if ($user): ?>
                    <span>歡迎，<?= escape($user['nickname']) ?> <?= escape($user['avatar']) ?></span>
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

        <?php if (isset($error)): ?>
            <div class="error"><?= escape($error) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h2>發表新討論</h2>
            <?php if ($user): ?>
                <form action="post.php" method="post">
                    <div class="form-group">
                        <label>作者</label>
                        <input type="text" value="<?= escape($user['nickname']) ?> <?= escape($user['avatar']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="title">標題：</label>
                        <input type="text" id="title" name="title" maxlength="200" required>
                    </div>
                    <div class="form-group">
                        <label for="content">內容：</label>
                        <textarea id="content" name="content" required></textarea>
                    </div>
                    <button type="submit">發表討論</button>
                </form>
            <?php else: ?>
                <p>請先 <a href="login.php">登入</a> 或 <a href="register.php">註冊</a>，才能發表討論。</p>
            <?php endif; ?>
        </div>

        <h2>討論列表</h2>

        <?php if (empty($news)): ?>
            <div class="news-list">
                <p class="empty">目前沒有討論。</p>
            </div>
        <?php else: ?>
            <div class="news-list">
                <?php foreach ($news as $item): ?>
                    <?php
                        $authorName = $item['nickname'] ? $item['nickname'] : '已刪除會員';
                        $authorAvatar = $item['avatar'] ? $item['avatar'] : '👤';
                        $authorColor = $item['favorite_color'] ? $item['favorite_color'] : '#007bff';
                    ?>
                    <div class="news-item" style="border-left: 5px solid <?= escape($authorColor) ?>;">
                        <div class="news-title">
                            <a href="show_news.php?id=<?= $item['id'] ?>">
                                <?= escape($item['title']) ?>
                            </a>
                            <?php if ($item['reply_count'] > 0): ?>
                                <span class="reply-count">
                                    <?= $item['reply_count'] ?> 則回應
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="news-meta">
                            由 <strong><?= escape($authorName) ?> <?= escape($authorAvatar) ?></strong> 發表於
                            <?= escape($item['created_at']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
