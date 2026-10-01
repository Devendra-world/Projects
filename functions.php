<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('Please sign in to continue.', 'warning');
        redirect('/auth/login.php');
    }
}

function current_user(): ?array
{
    static $user = false;
    global $pdo;
    if ($user !== false) return $user;
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare('SELECT id, name, email, student_id, college, phone, avatar, role, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

function is_admin(): bool
{
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('403 — Admin access required.');
    }
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function pull_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): void
{
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Security token expired. Please go back and try again.');
    }
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function validate_email(string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_password(string $password): bool
{
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/\d/', $password);
}

function safe_filename(string $name): string
{
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(12)) . ($ext ? '.' . $ext : '');
}

function handle_image_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed.');
    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) throw new RuntimeException('Image must be 5 MB or smaller.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = UPLOAD_DIR . $name;
    if (!move_uploaded_file($file['tmp_name'], $destination)) throw new RuntimeException('Could not save uploaded image.');
    return 'assets/img/uploads/' . $name;
}

function status_badge(string $status): string
{
    $map = [
        'lost' => ['Lost', 'danger'],
        'found' => ['Found', 'success'],
        'matched' => ['Matched', 'info'],
        'returned' => ['Returned', 'success'],
        'open' => ['Open', 'info'],
        'pending' => ['Pending', 'warning'],
        'approved' => ['Approved', 'success'],
        'rejected' => ['Rejected', 'danger']
    ];
    [$label, $class] = $map[$status] ?? [ucfirst($status), 'muted'];
    return '<span class="badge badge-' . $class . '">' . e($label) . '</span>';
}

function item_image(?string $path): string
{
    return $path ?: 'assets/img/item-placeholder.svg';
}

function time_ago(string $datetime): string
{
    $diff = max(0, time() - strtotime($datetime));
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('d M Y', strtotime($datetime));
}

function sanitize_text(string $value, int $max = 1000): string
{
    $value = trim(preg_replace('/\s+/', ' ', strip_tags($value)));
    return mb_substr($value, 0, $max);
}
