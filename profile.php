<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$pageTitle = 'My profile';
require __DIR__ . '/includes/header.php';
$u = current_user();
$q = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id=? AND status='returned'");
$q->execute([$u['id']]);
$returned = (int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM items WHERE user_id=?');
$q->execute([$u['id']]);
$reports = (int)$q->fetchColumn();
$avatar = $u['avatar'] ?: null;
?><div class="page-shell">
    <section class="panel profile-header">
        <div class="profile-avatar" style="display:grid;place-items:center;background:linear-gradient(135deg,#55e8e8,#7869ff);font-size:36px;font-weight:800"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></div>
        <div style="flex:1"><span class="eyebrow">Student profile</span>
            <h1 style="font-size:42px;margin:10px 0 5px"><?= e($u['name']) ?></h1>
            <p class="muted"><?= e($u['college']) ?> · <?= e($u['student_id']) ?></p>
        </div><a class="btn btn-secondary" href="<?= BASE_URL ?>/profile_edit.php">Edit profile</a>
    </section>
    <div style="height:18px"></div>
    <div class="admin-grid">
        <div class="metric"><strong><?= $reports ?></strong><span>Reports posted</span></div>
        <div class="metric"><strong><?= $returned ?></strong><span>Returned items</span></div>
        <div class="metric"><strong><?= e($u['student_id']) ?></strong><span>Student ID</span></div>
        <div class="metric"><strong><?= e(date('M Y', strtotime($u['created_at']))) ?></strong><span>Member since</span></div>
    </div>
    <div class="split">
        <section class="panel">
            <h2>Account details</h2>
            <div class="detail-list">
                <div class="detail-row"><span>Email</span><strong><?= e($u['email']) ?></strong></div>
                <div class="detail-row"><span>Phone</span><strong><?= e($u['phone'] ?: 'Not provided') ?></strong></div>
                <div class="detail-row"><span>College</span><strong><?= e($u['college']) ?></strong></div>
                <div class="detail-row"><span>Role</span><strong><?= e(ucfirst($u['role'])) ?></strong></div>
            </div>
        </section>
        <section class="panel">
            <h2>Safety reminder</h2>
            <p class="muted">Keep sensitive information private. Use item-specific details to verify ownership instead of posting passwords, bank details or government identifiers.</p><a class="btn btn-ghost" href="<?= BASE_URL ?>/claims/index.php">Open claims center</a>
        </section>
    </div>
</div><?php require __DIR__ . '/includes/footer.php'; ?>