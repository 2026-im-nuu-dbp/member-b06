<?php
// Insert new discussion into database

header('Content-Type: text/html; charset=utf-8');
require 'auth.php';
requireLogin();

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if (empty($title) || empty($content)) {
    die('所有欄位都必須填寫。<br><a href="index.php">返回</a>');
}

$title = substr($title, 0, 200);
$content = substr($content, 0, 10000);

try {
    $stmt = $pdo->prepare('INSERT INTO news (title, content, user_id) VALUES (?, ?, ?)');
    $stmt->execute([$title, $content, $user['id']]);
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    die('發表討論失敗: ' . $e->getMessage() . '<br><a href="index.php">返回</a>');
}
