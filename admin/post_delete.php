<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /blog/admin/dashboard.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id > 0) {
    // fetch cover_image path first
    $q = $pdo->prepare('SELECT cover_image FROM posts WHERE id = ?');
    $q->execute([$id]);
    $row = $q->fetch();
    if ($row && !empty($row['cover_image'])) {
        $filePath = dirname(__DIR__) . '/' . $row['cover_image'];
        if (is_file($filePath)) { @unlink($filePath); }
    }
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post deleted successfully.'];
} else {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid post id.'];
}

header('Location: /blog/admin/dashboard.php');
exit;
