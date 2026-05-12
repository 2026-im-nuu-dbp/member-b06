<?php
session_start();
require_once 'db_config.php';

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function loginUser(array $user)
{
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
}

function logoutUser()
{
    session_unset();
    session_destroy();
}

function requireLogin()
{
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin()
{
    $user = currentUser();
    if (!$user || empty($user['is_admin'])) {
        die('需要管理員權限。<br><a href="index.php">返回</a>');
    }
}

function getUserByUsername($username)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, username, password_hash, nickname, favorite_color, avatar, is_admin FROM users WHERE username = ?');
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function getUserById($id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, username, nickname, favorite_color, avatar, is_admin FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function userCount()
{
    global $pdo;
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    return (int) $stmt->fetchColumn();
}

function avatarOptions()
{
    return ['😃', '😎', '🐱', '🌟', '🎯', '🍀', '🎵', '🚀'];
}

function sanitizeColor($color)
{
    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        return $color;
    }
    return '#f5f5f5';
}
