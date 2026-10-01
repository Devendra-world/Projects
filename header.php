<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
$activePage = $activePage ?? '';
$user = current_user();
$flashes = pull_flashes();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="CampusTrace — a secure, modern college lost and found platform.">
  <title><?= e($pageTitle) ?> · <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <script>
    window.APP_BASE_URL = <?= json_encode(BASE_URL) ?>;
  </script>
</head>

<body>
  <div class="site-noise"></div>
  <header class="site-header">
    <a class="brand" href="<?= BASE_URL ?>/index.php"><span class="brand-mark">✦</span><span>Campus<span>Trace</span></span></a>
    <button class="mobile-menu" aria-label="Toggle menu" data-mobile-menu>☰</button>
    <nav class="nav-links" data-nav>
      <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">Home</a>
      <a class="<?= $activePage === 'browse' ? 'active' : '' ?>" href="<?= BASE_URL ?>/items/browse.php">Browse</a>
      <?php if ($user): ?>
        <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
        <a class="<?= $activePage === 'post' ? 'active' : '' ?>" href="<?= BASE_URL ?>/items/post.php">Post Item</a>
        <?php if (is_admin()): ?><a href="<?= BASE_URL ?>/admin/index.php">Admin</a><?php endif; ?>
        <a class="avatar-link" href="<?= BASE_URL ?>/profile.php"><span class="mini-avatar"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span><?= e($user['name']) ?></a>
        <a class="btn btn-sm btn-ghost" href="<?= BASE_URL ?>/auth/logout.php">Sign out</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/auth/login.php">Sign in</a>
        <a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>/auth/register.php">Create account</a>
      <?php endif; ?>
    </nav>
  </header>
  <main>
    <?php foreach ($flashes as $flash): ?>
      <div class="flash flash-<?= e($flash['type']) ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
    <?php endforeach; ?>