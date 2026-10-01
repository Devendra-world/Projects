<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/items/browse.php');
verify_csrf($_POST['csrf'] ?? null);
$id = (int)($_POST['item_id'] ?? 0);
$action = $_POST['action'] ?? '';
$q = $pdo->prepare('SELECT * FROM items WHERE id=? AND user_id=? LIMIT 1');
$q->execute([$id, $_SESSION['user_id']]);
$item = $q->fetch();
if (!$item) {
    http_response_code(403);
    exit('Forbidden');
}
if ($action === 'archive') {
    $q = $pdo->prepare("UPDATE items SET status='archived' WHERE id=?");
    $q->execute([$id]);
    set_flash('Report archived.');
} elseif ($action === 'returned') {
    $q = $pdo->prepare("UPDATE items SET status='returned' WHERE id=?");
    $q->execute([$id]);
    set_flash('Item marked returned.');
}
redirect('/items/view.php?id=' . $id);
