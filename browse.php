<?php
$pageTitle = 'Browse reports';
$activePage = 'browse';
require __DIR__ . '/../includes/header.php';
$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? '';
$location = trim($_GET['location'] ?? '');
$date = $_GET['date'] ?? '';
$mine = isset($_GET['mine']) && is_logged_in();
$sql = "SELECT i.*,u.name owner_name FROM items i JOIN users u ON u.id=i.user_id WHERE i.status IN ('open','matched')";
$params = [];
if ($q !== '') {
    $sql .= ' AND (i.title LIKE ? OR i.description LIKE ? OR i.identifying_details LIKE ?)';
    $like = "%$q%";
    $params = array_merge($params, [$like, $like, $like]);
}
if (in_array($type, ['lost', 'found'], true)) {
    $sql .= ' AND i.type=?';
    $params[] = $type;
}
if ($category !== '') {
    $sql .= ' AND i.category=?';
    $params[] = $category;
}
if ($location !== '') {
    $sql .= ' AND i.location LIKE ?';
    $params[] = "%$location%";
}
if ($date !== '') {
    $sql .= ' AND i.event_date=?';
    $params[] = $date;
}
if ($mine) {
    $sql .= ' AND i.user_id=?';
    $params[] = (int)$_SESSION['user_id'];
}
$sql .= ' ORDER BY i.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
$categories = $pdo->query('SELECT DISTINCT category FROM items ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
?><div class="page-shell">
    <div class="page-title">
        <div><span class="eyebrow">Campus marketplace of clues</span>
            <h1>Browse reports</h1>
            <p>Search active lost and found reports. Open an item to view details and submit a verified claim.</p>
        </div>
    </div>
    <section class="panel">
        <form class="toolbar" method="get"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Search item, details, keyword…"><select class="select" name="type">
                <option value="">All types</option>
                <option value="lost" <?= $type === 'lost' ? 'selected' : '' ?>>Lost</option>
                <option value="found" <?= $type === 'found' ? 'selected' : '' ?>>Found</option>
            </select><select class="select" name="category">
                <option value="">All categories</option><?php foreach ($categories as $c): ?><option <?= $category === $c ? 'selected' : '' ?> value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?>
            </select><input class="input" name="location" value="<?= e($location) ?>" placeholder="Location"><input class="input" type="date" name="date" value="<?= e($date) ?>"><button class="btn btn-primary" type="submit">Search</button></form>
    </section>
    <div style="height:20px"></div><?php if (!$items): ?><section class="panel search-empty">
            <h2>No matching reports</h2>
            <p class="muted">Try a broader keyword or fewer filters.</p><a class="btn btn-secondary" href="browse.php">Reset filters</a>
        </section><?php else: ?><div class="item-grid"><?php foreach ($items as $item): ?><a class="item-card" href="view.php?id=<?= (int)$item['id'] ?>">
                    <div class="item-image"><img src="<?= BASE_URL ?>/<?= e(item_image($item['image_path'])) ?>" alt="<?= e($item['title']) ?>"><span style="position:absolute;top:12px;left:12px"><?= status_badge($item['type']) ?></span></div>
                    <div class="item-body">
                        <div class="item-meta"><span class="muted"><?= e($item['category']) ?></span><span class="muted"><?= date('d M Y', strtotime($item['event_date'])) ?></span></div>
                        <h3><?= e($item['title']) ?></h3>
                        <p>📍 <?= e($item['location']) ?></p>
                        <p><?= e(mb_strimwidth($item['description'], 0, 100, '…')) ?></p>
                    </div>
                </a><?php endforeach; ?></div><?php endif; ?>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>