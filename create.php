<?php

declare(strict_types=1);

// CampusTrace root directory
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/items/browse.php');
verify_csrf($_POST['csrf'] ?? null);
$itemId = (int)($_POST['item_id'] ?? 0);
$message = sanitize_text($_POST['message'] ?? '', 1500);
$proof = sanitize_text($_POST['proof_details'] ?? '', 1000);
$q = $pdo->prepare("SELECT * FROM items WHERE id=? AND status='open' LIMIT 1");
$q->execute([$itemId]);
$item = $q->fetch();
if (!$item) {
    set_flash('That item is no longer available for claims.', 'warning');
    redirect('/items/browse.php');
}
if ((int)$item['user_id'] === (int)$_SESSION['user_id']) {
    set_flash('You cannot claim your own report.', 'warning');
    redirect('/items/view.php?id=' . $itemId);
}
if (strlen($message) < 15) {
    set_flash('Please provide more verification detail.', 'warning');
    redirect('/items/view.php?id=' . $itemId);
}
try {
    $q = $pdo->prepare('INSERT INTO claims(item_id,claimant_id,message,proof_details) VALUES(?,?,?,?)');
    $q->execute([$itemId, $_SESSION['user_id'], $message, $proof ?: null]);
    $q = $pdo->prepare('INSERT INTO notifications(user_id,type,message,link) VALUES(?,?,?,?)');
    $q->execute([$item['user_id'], 'claim', 'New claim received for “' . $item['title'] . '”.', '/claims/index.php']);
    set_flash('Claim submitted. The poster can now review your verification details.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') set_flash('You already have a claim on this item.', 'warning');
    else throw $e;
}
redirect('/items/view.php?id=' . $itemId);
