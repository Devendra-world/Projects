<?php
require_once __DIR__ . '/../includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT i.*,u.name owner_name,u.email owner_email,u.student_id FROM items i JOIN users u ON u.id=i.user_id WHERE i.id=? LIMIT 1');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) {
    http_response_code(404);
    exit('Item not found.');
}
$pageTitle = $item['title'];
require __DIR__ . '/../includes/header.php';
$already = false;
if (is_logged_in()) {
    $q = $pdo->prepare('SELECT id,status FROM claims WHERE item_id=? AND claimant_id=?');
    $q->execute([$id, $_SESSION['user_id']]);
    $already = $q->fetch();
}
?><div class="page-shell">
    <div class="item-detail">
        <div class="detail-image"><img src="<?= BASE_URL ?>/<?= e(item_image($item['image_path'])) ?>" alt="<?= e($item['title']) ?>"></div>
        <section class="detail-copy">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"><?= status_badge($item['type']) ?> <?= status_badge($item['status']) ?></div>
            <h1><?= e($item['title']) ?></h1>
            <p class="muted">Reported by <?= e($item['owner_name']) ?> · <?= e(time_ago($item['created_at'])) ?></p>
            <p><?= nl2br(e($item['description'])) ?></p>
            <div class="detail-list">
                <div class="detail-row"><span>Category</span><strong><?= e($item['category']) ?></strong></div>
                <div class="detail-row"><span>Location</span><strong><?= e($item['location']) ?></strong></div>
                <div class="detail-row"><span>Date</span><strong><?= date('d M Y', strtotime($item['event_date'])) ?></strong></div><?php if ($item['color']): ?><div class="detail-row"><span>Color</span><strong><?= e($item['color']) ?></strong></div><?php endif; ?>
            </div><?php if ($item['identifying_details']): ?><div class="notice"><strong>Verification note</strong><br><span class="muted">The poster has additional identifying details that may be requested during a claim.</span></div><?php endif; ?><?php if ($item['status'] === 'open' && is_logged_in() && (int)$item['user_id'] !== (int)$_SESSION['user_id']): ?><?php if ($already): ?><div class="notice" style="margin-top:18px">Your claim is <?= e($already['status']) ?>. You can manage it from <a style="color:var(--cyan)" href="<?= BASE_URL ?>/claims/index.php">Claims</a>.</div><?php else: ?><form method="post" action="../claims/create.php" class="panel" style="margin-top:18px"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="item_id" value="<?= $id ?>">
                    <h3>Claim this item</h3>
                    <div class="form-group"><label>Why do you believe this is yours / why are you the right recipient? *</label><textarea class="textarea" name="message" minlength="15" maxlength="1500" required placeholder="Mention specific details that can verify the claim."></textarea></div>
                    <div class="form-group" style="margin-top:12px"><label>Additional proof details</label><textarea class="textarea" name="proof_details" maxlength="1000" placeholder="Optional: purchase detail, unique mark, case color, etc."></textarea></div><button class="btn btn-primary" type="submit" style="margin-top:12px">Submit secure claim</button>
                </form><?php endif; ?><?php elseif (!is_logged_in()): ?><div class="notice" style="margin-top:18px">Sign in to submit a claim.</div><?php elseif ((int)$item['user_id'] === (int)$_SESSION['user_id']): ?><div style="display:flex;gap:10px;margin-top:18px"><a class="btn btn-secondary" href="edit.php?id=<?= $id ?>">Edit report</a>
                    <form method="post" action="../api/item_action.php"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="archive"><input type="hidden" name="item_id" value="<?= $id ?>"><button class="btn btn-danger" data-confirm="Archive this report?">Archive</button></form>
                </div><?php endif; ?>
        </section>
    </div>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>