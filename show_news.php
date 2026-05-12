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
    <style>
        body {
            font-family: system-ui, -apple-system, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        .top-bar a { color: #007bff; text-decoration: none; margin-left: 12px; }
        .top-bar a:hover { text-decoration: underline; }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .news-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .news-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .news-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        .news-body {
            line-height: 1.8;
            color: #333;
            white-space: pre-wrap;
        }
        .reply-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .reply-item {
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .reply-author {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .reply-time {
            font-size: 12px;
            color: #999;
        }
        .reply-content {
            margin-top: 10px;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
        }
        .form-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button {
            background: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        .empty {
            text-align: center;
            color: #999;
            padding: 20px;
            font-style: italic;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .info {
            margin-bottom: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <a class="back-link" href="index.php">← 返回討論列表</a>
            <div>
                <?php if ($user): ?>
                    <span>您好，<?= escape($user['nickname']) ?> <?= escape($user['avatar']) ?></span>
                    <?php if (!empty($user['is_admin'])): ?>
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
