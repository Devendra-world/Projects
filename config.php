<?php

declare(strict_types=1);

const APP_NAME = 'CampusTrace';

// Automatically detect whether CampusTrace is installed directly in the web root
// (http://localhost/) or inside a subfolder (http://localhost/campus_lost_found/).
// This prevents CSS/JS/page links from breaking on WAMP/XAMPP.
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$appRoot = realpath(__DIR__ . '/..');
$documentRootNorm = $documentRoot ? str_replace('\\', '/', $documentRoot) : '';
$appRootNorm = $appRoot ? str_replace('\\', '/', $appRoot) : '';
$detectedBase = '';
if ($documentRootNorm !== '' && $appRootNorm !== '') {
    $rootPrefix = rtrim($documentRootNorm, '/');
    if ($appRootNorm === $rootPrefix) {
        $detectedBase = '';
    } elseif (str_starts_with($appRootNorm, $rootPrefix . '/')) {
        $detectedBase = substr($appRootNorm, strlen($rootPrefix));
    }
}
if ($detectedBase === '') {
    $detectedBase = trim((string)($_ENV['CAMPUS_BASE_URL'] ?? ''), '/');
    $detectedBase = $detectedBase === '' ? '' : '/' . $detectedBase;
}
define('BASE_URL', $detectedBase);
const UPLOAD_DIR = __DIR__ . '/../assets/img/uploads/';
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}
