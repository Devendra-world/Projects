<?php
require_login();
$id = (int)($_GET['id'] ?? 0);
$q = $pdo->prepare('SELECT * FROM items WHERE id=? AND user_id=? LIMIT 1');
$q->execute([$id, $_SESSION['user_id']]);
$item = $q->fetch();
if (!$item) exit('Report not found or access denied.');
$pageTitle = 'Edit report';
require __DIR__ . '/../includes/header.php';
$categories = ['Electronics', 'ID / Cards', 'Keys', 'Clothing', 'Books', 'Bags', 'Accessories', 'Sports', 'Stationery', 'Other'];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    $title = sanitize_text($_POST['title'] ?? '', 160);
    $desc = sanitize_text($_POST['description'] ?? '', 3000);
    $location = sanitize_text($_POST['location'] ?? '', 180);
    $date = $_POST['event_date'] ?? '';
    $category = $_POST['category'] ?? '';
    $color = sanitize_text($_POST['color'] ?? '', 60);
    $details = sanitize_text($_POST['identifying_details'] ?? '', 500);
    if (strlen($title) < 4) $errors[] = 'Title is too short.';
    if (strlen($desc) < 15) $errors[] = 'Description is too short.';
    if (!$date || $date > date('Y-m-d')) $errors[] = 'Invalid date.';
    if (!in_array($category, $categories, true)) $errors[] = 'Invalid category.';
    $image = $item['image_path'];
    try {
        $new = handle_image_upload($_FILES['image'] ?? []);
        if ($new) $image = $new;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
    if (!$errors) {
        $q = $pdo->prepare('UPDATE items SET title=?,category=?,description=?,location=?,event_date=?,color=?,identifying_details=?,image_path=? WHERE id=? AND user_id=?');
        $q->execute([$title, $category, $desc, $location, $date, $color ?: null, $details ?: null, $image, $id, $_SESSION['user_id']]);
        set_flash('Report updated.');
        redirect('/items/view.php?id=' . $id);
    }
}
?><div class="page-shell">
    <div class="page-title">
        <div><span class="eyebrow">Edit report</span>
            <h1>Update item details</h1>
        </div>
    </div>
    <section class="panel"><?php if ($errors): ?><div class="notice" style="border-color:#703947;margin-bottom:18px"><?php foreach ($errors as $x): ?><div>• <?= e($x) ?></div><?php endforeach; ?></div><?php endif; ?><form method="post" enctype="multipart/form-data" data-validate><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="form-grid">
                <div class="form-group full"><label>Title</label><input class="input" name="title" value="<?= e($item['title']) ?>" required></div>
                <div class="form-group"><label>Category</label><select class="select" name="category" required><?php foreach ($categories as $c): ?><option <?= $item['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Date</label><input class="input" type="date" name="event_date" value="<?= e($item['event_date']) ?>" max="<?= date('Y-m-d') ?>" required></div>
                <div class="form-group"><label>Location</label><input class="input" name="location" value="<?= e($item['location']) ?>" required></div>
                <div class="form-group"><label>Color</label><input class="input" name="color" value="<?= e($item['color']) ?>"></div>
                <div class="form-group full"><label>Description</label><textarea class="textarea" name="description" required><?= e($item['description']) ?></textarea></div>
                <div class="form-group full"><label>Identifying details</label><textarea class="textarea" name="identifying_details"><?= e($item['identifying_details']) ?></textarea></div>
                <div class="form-group full"><label>Replace image</label><input class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input></div>
            </div><button class="btn btn-primary" style="margin-top:20px">Save changes</button>
        </form>
    </section>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>