<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: text/html; charset=utf-8');
$checks = [];
$checks[] = ['PHP 8.1+', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION];
$checks[] = ['PDO MySQL', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing'];
$checks[] = ['Database connection', isset($pdo), isset($pdo) ? 'Connected to ' . $pdo->query('SELECT DATABASE()')->fetchColumn() : 'Failed'];
$checks[] = ['Detected BASE_URL', true, BASE_URL === '' ? '/' : BASE_URL . '/'];
$checks[] = ['CSS file', is_file(__DIR__ . '/assets/css/style.css'), 'assets/css/style.css'];
$checks[] = ['JavaScript file', is_file(__DIR__ . '/assets/js/app.js'), 'assets/js/app.js'];
$checks[] = ['Upload directory writable', is_dir(UPLOAD_DIR) && is_writable(UPLOAD_DIR), UPLOAD_DIR];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CampusTrace System Check</title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>

<body>
    <main class="page-shell">
        <section class="panel"><span class="eyebrow">WAMP diagnostic</span>
            <h1>CampusTrace system check</h1>
            <p class="muted">Use this page to verify the local installation. Delete or protect this file before production.</p>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Check</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($checks as [$label, $ok, $detail]): ?><tr>
                                <td><?= e($label) ?></td>
                                <td><?= $ok ? '✅ OK' : '❌ ERROR' ?></td>
                                <td><?= e($detail) ?></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
            <div style="margin-top:20px"><a class="btn btn-primary" href="<?= e(BASE_URL) ?>/">Open CampusTrace</a></div>
        </section>
    </main>
</body>

</html>