<?php

declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
$u = current_user();
$q = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id=? AND status='open'");
$q->execute([$u['id']]);
$myOpen = (int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM claims WHERE claimant_id=? AND status=\'pending\'');
$q->execute([$u['id']]);
$pending = (int)$q->fetchColumn();
$q = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id=? AND status='returned'");
$q->execute([$u['id']]);
$returned = (int)$q->fetchColumn();
$items = $pdo->prepare('SELECT * FROM items WHERE user_id=? ORDER BY created_at DESC LIMIT 6');
$items->execute([$u['id']]);
$items = $items->fetchAll();
$claims = $pdo->prepare("SELECT c.*,i.title FROM claims c JOIN items i ON i.id=c.item_id WHERE c.claimant_id=? ORDER BY c.created_at DESC LIMIT 5");
$claims->execute([$u['id']]);
$claims = $claims->fetchAll();
?><div class="page-shell">
    <div class="page-title">
        <div><span class="eyebrow">Personal workspace</span>
            <h1>Good to see you, <?= e(explode(' ', $u['name'])[0]) ?>.</h1>
            <p>Manage your reports, claims and the items that are moving through campus.</p>
        </div><a class="btn btn-primary" href="<?= BASE_URL ?>/items/post.php">+ Post item</a>
    </div>
    <div class="admin-grid">
        <div class="metric"><strong><?= $myOpen ?></strong><span>Open reports</span></div>
        <div class="metric"><strong><?= $pending ?></strong><span>Pending claims</span></div>
        <div class="metric"><strong><?= $returned ?></strong><span>Returned by you</span></div>
        <div class="metric"><strong><?= e($u['student_id']) ?></strong><span>Student ID</span></div>
    </div>
    <div class="split">
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Your reports</h2>
                    <p>Recent items you posted.</p>
                </div><a href="items/browse.php?mine=1" class="btn btn-sm btn-ghost">View all</a>
            </div><?php if (!$items): ?><div class="empty">You have not posted anything yet.</div><?php else: ?><div style="display:grid;gap:10px"><?php foreach ($items as $item): ?><a class="claim-box" href="items/view.php?id=<?= (int)$item['id'] ?>">
                            <div><strong><?= e($item['title']) ?></strong>
                                <div class="muted">📍 <?= e($item['location']) ?> · <?= e(time_ago($item['created_at'])) ?></div>
                            </div><?= status_badge($item['status']) ?>
                        </a><?php endforeach; ?></div><?php endif; ?>
        </section>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Your claims</h2>
                    <p>Claims you have submitted.</p>
                </div><a href="claims/index.php" class="btn btn-sm btn-ghost">Manage</a>
            </div><?php if (!$claims): ?><div class="empty">No claims submitted yet.</div><?php else: ?><div style="display:grid;gap:10px"><?php foreach ($claims as $c): ?><div class="claim-box">
                            <div><strong><?= e($c['title']) ?></strong>
                                <div class="muted"><?= e(time_ago($c['created_at'])) ?></div>
                            </div><?= status_badge($c['status']) ?>
                        </div><?php endforeach; ?></div><?php endif; ?>
        </section>
    </div>
</div><?php require __DIR__ . '/includes/footer.php'; ?>