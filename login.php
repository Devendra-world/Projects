<?php
$pageTitle = 'Sign in';
require __DIR__ . '/../includes/header.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    $email = trim(strtolower($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? '';
    $q = $pdo->prepare('SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1');
    $q->execute([$email]);
    $u = $q->fetch();
    if ($u && password_verify($pw, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        set_flash('Welcome back, ' . $u['name'] . '!');
        redirect('/dashboard.php');
    }
    $error = 'Email or password is incorrect.';
}
?><div class="auth-wrap">
    <section class="auth-card"><span class="eyebrow">Welcome back</span>
        <h1>Sign in</h1>
        <p class="muted">Continue to your CampusTrace workspace.</p><?php if ($error): ?><div class="notice" style="border-color:#703947;margin-bottom:15px">⚠ <?= e($error) ?></div><?php endif; ?><form method="post" data-validate><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="form-group"><label>Email</label><input class="input" type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input class="input" type="password" name="password" required></div><button class="btn btn-primary" type="submit">Sign in →</button>
        </form>
        <div class="auth-switch">New here? <a href="register.php">Create a student account</a></div>
    </section>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>