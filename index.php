<?php

declare(strict_types=1);

// CampusTrace root directory
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_login();
$pageTitle = 'Claims';
require __DIR__ . '/../includes/header.php';
$u = current_user();
$incoming = $pdo->prepare('SELECT c.*,i.title,i.type,u.name claimant_name,u.email claimant_email FROM claims c JOIN items i ON i.id=c.item_id JOIN users u ON u.id=c.claimant_id WHERE i.user_id=? ORDER BY c.created_at DESC');
$incoming->execute([$u['id']]);
$incoming = $incoming->fetchAll();
$out = $pdo->prepare('SELECT c.*,i.title,i.type,u.name poster_name FROM claims c JOIN items i ON i.id=c.item_id JOIN users u ON u.id=i.user_id WHERE c.claimant_id=? ORDER BY c.created_at DESC');
$out->execute([$u['id']]);
$out = $out->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    $claimId = (int)$_POST['claim_id'];
    $action = $_POST['action'] ?? '';
    $q = $pdo->prepare('SELECT c.*,i.user_id item_owner,i.title FROM claims c JOIN items i ON i.id=c.item_id WHERE c.id=? LIMIT 1');
    $q->execute([$claimId]);
    $c = $q->fetch();
    if (!$c) redirect('/claims/index.php');
    if ((int)$c['item_owner'] !== (int)$u['id']) {
        http_response_code(403);
        exit('Forbidden');
    }
    if (in_array($action, ['approved', 'rejected'], true)) {
        $pdo->beginTransaction();
        $q = $pdo->prepare('UPDATE claims SET status=? WHERE id=?');
        $q->execute([$action, $claimId]);
        if ($action === 'approved') {
            $q = $pdo->prepare("UPDATE items SET status='returned' WHERE id=?");
            $q->execute([$c['item_id']]);
            $q = $pdo->prepare('UPDATE claims SET status=\'rejected\' WHERE item_id=? AND id<>? AND status=\'pending\'');
            $q->execute([$c['item_id'], $claimId]);
        }
        $q = $pdo->prepare('INSERT INTO notifications(user_id,type,message,link) VALUES(?,?,?,?)');
        $q->execute([$c['claimant_id'], 'claim_update', 'Your claim for “' . $c['title'] . '” was ' . $action . '.', '/claims/index.php']);
        $pdo->commit();
        set_flash('Claim updated.');
    }
    redirect('/claims/index.php');
}
?><div class="page-shell">
    <div class="page-title">
        <div><span class="eyebrow">Verification workflow</span>
            <h1>Claims center</h1>
            <p>Review incoming claims on your reports and track the claims you have submitted.</p>
        </div>
    </div>
    <div class="split">
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Incoming</h2>
                    <p>People claiming items you posted.</p>
                </div>
            </div><?php if (!$incoming): ?><div class="empty">No incoming claims.</div><?php else: ?><div style="display:grid;gap:14px"><?php foreach ($incoming as $c): ?><div class="claim-box" style="display:block">
                            <div style="display:flex;justify-content:space-between;gap:10px">
                                <div><strong><?= e($c['title']) ?></strong>
                                    <div class="muted">Claim by <?= e($c['claimant_name']) ?> · <?= e(time_ago($c['created_at'])) ?></div>
                                </div><?= status_badge($c['status']) ?>
                            </div>
                            <p><?= nl2br(e($c['message'])) ?></p><?php if ($c['proof_details']): ?><div class="notice"><strong>Proof details</strong><br><?= nl2br(e($c['proof_details'])) ?></div><?php endif; ?><?php if ($c['status'] === 'pending'): ?><form method="post" style="display:flex;gap:8px;margin-top:12px"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="claim_id" value="<?= (int)$c['id'] ?>"><button class="btn btn-primary" name="action" value="approved">Approve & return</button><button class="btn btn-danger" name="action" value="rejected">Reject</button></form><?php endif; ?>
                        </div><?php endforeach; ?></div><?php endif; ?>
        </section>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Submitted</h2>
                    <p>Your claims on other reports.</p>
                </div>
            </div><?php if (!$out): ?><div class="empty">You have not submitted a claim.</div><?php else: ?><div style="display:grid;gap:10px"><?php foreach ($out as $c): ?><div class="claim-box">
                            <div><strong><?= e($c['title']) ?></strong>
                                <div class="muted">Poster: <?= e($c['poster_name']) ?></div>
                            </div><?= status_badge($c['status']) ?>
                        </div><?php endforeach; ?></div><?php endif; ?>
        </section>
    </div>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>