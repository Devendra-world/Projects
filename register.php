<?php
$pageTitle = 'Create account';
require __DIR__ . '/../includes/header.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    $name = sanitize_text($_POST['name'] ?? '', 100);
    $email = trim(strtolower($_POST['email'] ?? ''));
    $student = sanitize_text($_POST['student_id'] ?? '', 50);
    $college = sanitize_text($_POST['college'] ?? '', 150);
    $phone = sanitize_text($_POST['phone'] ?? '', 30);
    $pw = $_POST['password'] ?? '';
    $cp = $_POST['password_confirm'] ?? '';
    if (strlen($name) < 2) $errors[] = 'Enter your full name.';
    if (!validate_email($email)) $errors[] = 'Enter a valid email address.';
    if (strlen($student) < 3) $errors[] = 'Enter a valid student ID.';
    if (strlen($college) < 2) $errors[] = 'Enter your college name.';
    if (!validate_password($pw)) $errors[] = 'Password must be at least 8 characters and include a letter and number.';
    if ($pw !== $cp) $errors[] = 'Passwords do not match.';
    $q = $pdo->prepare('SELECT id FROM users WHERE email=? OR student_id=? LIMIT 1');
    $q->execute([$email, $student]);
    if ($q->fetch()) $errors[] = 'That email or student ID is already registered.';
    if (!$errors) {
        $q = $pdo->prepare('INSERT INTO users(name,email,password_hash,student_id,college,phone) VALUES(?,?,?,?,?,?)');
        $q->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT), $student, $college, $phone ?: null]);
        $_SESSION['user_id'] = (int)$pdo->lastInsertId();
        set_flash('Welcome to CampusTrace, ' . $name . '!');
        redirect('/dashboard.php');
    }
}
?><div class="auth-wrap">
    <section class="auth-card"><span class="eyebrow">Student onboarding</span>
        <h1>Create account</h1>
        <p class="muted">Use your campus identity details so reports stay accountable.</p><?php if ($errors): ?><div class="notice" style="border-color:#703947;margin-bottom:15px"><?php foreach ($errors as $x): ?><div>• <?= e($x) ?></div><?php endforeach; ?></div><?php endif; ?><form method="post" data-validate novalidate><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="form-grid">
                <div class="form-group"><label>Full name</label><input class="input" name="name" value="<?= old('name') ?>" required></div>
                <div class="form-group"><label>Student ID</label><input class="input" name="student_id" value="<?= old('student_id') ?>" required></div>
                <div class="form-group"><label>College / institute</label><input class="input" name="college" value="<?= old('college') ?>" required></div>
                <div class="form-group"><label>Phone (optional)</label><input class="input" name="phone" value="<?= old('phone') ?>"></div>
                <div class="form-group full"><label>Email</label><input class="input" type="email" name="email" value="<?= old('email') ?>" required></div>
                <div class="form-group"><label>Password</label><input class="input" type="password" name="password" required minlength="8"></div>
                <div class="form-group"><label>Confirm password</label><input class="input" type="password" name="password_confirm" required></div>
            </div><button class="btn btn-primary" type="submit">Create account</button>
        </form>
        <div class="auth-switch">Already registered? <a href="login.php">Sign in</a></div>
    </section>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>