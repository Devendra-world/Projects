<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$pageTitle = 'Edit profile';
require __DIR__ . '/includes/header.php';
$u = current_user();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    $name = sanitize_text($_POST['name'] ?? '', 100);
    $college = sanitize_text($_POST['college'] ?? '', 150);
    $phone = sanitize_text($_POST['phone'] ?? '', 30);
    if (strlen($name) < 2) $errors[] = 'Name is required.';
    if (strlen($college) < 2) $errors[] = 'College is required.';
    $avatar = $u['avatar'];
    try {
        $new = handle_image_upload($_FILES['avatar'] ?? []);
        if ($new) $avatar = $new;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
    if (!$errors) {
        $q = $pdo->prepare('UPDATE users SET name=?,college=?,phone=?,avatar=? WHERE id=?');
        $q->execute([$name, $college, $phone ?: null, $avatar, $_SESSION['user_id']]);
        set_flash('Profile updated successfully.');
        redirect('/profile.php');
    }
}
?><div class="page-shell">
    <div class="page-title">
        <div><span class="eyebrow">Account settings</span>
            <h1>Edit profile</h1>
            <p>Keep your campus identity information current.</p>
        </div>
    </div>
    <section class="panel">
        <form method="post" enctype="multipart/form-data" data-validate><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><?php if ($errors): ?><div class="notice" style="border-color:#703947;margin-bottom:18px"><?php foreach ($errors as $x): ?><div>• <?= e($x) ?></div><?php endforeach; ?></div><?php endif; ?><div class="form-grid">
                <div class="form-group"><label>Full name</label><input class="input" name="name" value="<?= e($u['name']) ?>" required></div>
                <div class="form-group"><label>College</label><input class="input" name="college" value="<?= e($u['college']) ?>" required></div>
                <div class="form-group"><label>Email</label><input class="input" value="<?= e($u['email']) ?>" disabled></div>
                <div class="form-group"><label>Student ID</label><input class="input" value="<?= e($u['student_id']) ?>" disabled></div>
                <div class="form-group"><label>Phone</label><input class="input" name="phone" value="<?= e($u['phone']) ?>"></div>
                <div class="form-group"><label>Avatar</label><input class="input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" data-image-input></div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px"><a class="btn btn-ghost" href="profile.php">Cancel</a><button class="btn btn-primary">Save profile</button></div>
        </form>
    </section>
</div><?php require __DIR__ . '/includes/footer.php'; ?>