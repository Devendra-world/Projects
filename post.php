<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_login();
$pageTitle = 'Post an item';
$activePage = 'post';
require __DIR__ . '/../includes/header.php';
$errors = [];
$categories = ['Electronics', 'ID / Cards', 'Keys', 'Clothing', 'Books', 'Bags', 'Accessories', 'Sports', 'Stationery', 'Other'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    $type = $_POST['type'] ?? '';
    $title = sanitize_text($_POST['title'] ?? '', 160);
    $category = $_POST['category'] ?? '';
    $description = sanitize_text($_POST['description'] ?? '', 3000);
    $location = sanitize_text($_POST['location'] ?? '', 180);
    $eventDate = $_POST['event_date'] ?? '';
    $color = sanitize_text($_POST['color'] ?? '', 60);
    $details = sanitize_text($_POST['identifying_details'] ?? '', 500);
    if (!in_array($type, ['lost', 'found'], true)) $errors[] = 'Choose Lost or Found.';
    if (strlen($title) < 4) $errors[] = 'Title must be at least 4 characters.';
    if (!in_array($category, $categories, true)) $errors[] = 'Choose a valid category.';
    if (strlen($description) < 15) $errors[] = 'Description must be at least 15 characters.';
    if (strlen($location) < 3) $errors[] = 'Add a useful location.';
    if (!$eventDate || $eventDate > date('Y-m-d')) $errors[] = 'Choose a valid date that is not in the future.';
    $image = null;
    try {
        if (isset($_FILES['image'])) $image = handle_image_upload($_FILES['image']);
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO items(user_id,type,title,category,description,location,event_date,color,identifying_details,image_path) VALUES(?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$_SESSION['user_id'], $type, $title, $category, $description, $location, $eventDate, $color ?: null, $details ?: null, $image]);
        set_flash('Your item report is live.');
        redirect('/items/view.php?id=' . $pdo->lastInsertId());
    }
}
?><div class="page-shell">
    <div class="page-title">
        <div><span class="eyebrow">Create a report</span>
            <h1>Post a lost or found item</h1>
            <p>Be specific. Good locations and unique identifying details dramatically improve recovery.</p>
        </div>
    </div>
    <section class="panel">
        <form method="post" enctype="multipart/form-data" data-validate novalidate><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="form-grid">
                <div class="form-group"><label>Report type *</label><select class="select" name="type" required>
                        <option value="">Choose…</option>
                        <option value="lost" <?= old('type') === 'lost' ? 'selected' : '' ?>>I lost this</option>
                        <option value="found" <?= old('type') === 'found' ? 'selected' : '' ?>>I found this</option>
                    </select></div>
                <div class="form-group"><label>Category *</label><select class="select" name="category" required>
                        <option value="">Choose…</option><?php foreach ($categories as $c): ?><option <?= old('category') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="form-group full"><label>Item title *</label><input class="input" name="title" value="<?= old('title') ?>" maxlength="160" placeholder="e.g. Black Sony wireless earbuds" required></div>
                <div class="form-group"><label>Where? *</label><input class="input" name="location" value="<?= old('location') ?>" maxlength="180" placeholder="e.g. Central Library, 2nd floor" required></div>
                <div class="form-group"><label>When? *</label><input class="input" type="date" name="event_date" value="<?= old('event_date', date('Y-m-d')) ?>" max="<?= date('Y-m-d') ?>" required></div>
                <div class="form-group"><label>Color</label><input class="input" name="color" value="<?= old('color') ?>" maxlength="60" placeholder="Black / navy / silver"></div>
                <div class="form-group"><label>Photo</label><input class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input><small class="form-help">JPG, PNG or WEBP · max 5 MB</small></div>
                <div class="form-group full"><label>Description *</label><textarea class="textarea" name="description" maxlength="3000" placeholder="Describe the item and what happened…" required><?= old('description') ?></textarea></div>
                <div class="form-group full"><label>Private identifying details</label><textarea class="textarea" name="identifying_details" maxlength="500" placeholder="Details a genuine owner can use to prove ownership. Avoid posting sensitive information publicly."><?= old('identifying_details') ?></textarea><small class="form-help">These details are useful during verification. Do not include passwords, financial information or other secrets.</small></div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px"><a class="btn btn-ghost" href="browse.php">Cancel</a><button class="btn btn-primary" type="submit">Publish report →</button></div>
        </form>
    </section>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>