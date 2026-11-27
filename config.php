<?php
// Koneksi database (otomatis deteksi lokal vs hosting InfinityFree)
date_default_timezone_set('Asia/Jakarta');
mysqli_report(MYSQLI_REPORT_OFF);

$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
    || strpos($host, 'localhost:') === 0
    || strpos($host, '127.0.0.1:') === 0
    || substr($host, -5) === '.test';

if ($isLocal) {
    $dbHost = getenv('SIMPLE_DB_HOST') ?: '127.0.0.1';
    $dbUser = getenv('SIMPLE_DB_USER') ?: 'root';
    $dbPass = getenv('SIMPLE_DB_PASS') ?: '';
    $dbName = getenv('SIMPLE_DB_NAME') ?: 'goji_db';
} else {
    $localCfg = file_exists(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];
    $dbHost = getenv('SIMPLE_DB_HOST') ?: ($localCfg['host'] ?? 'sql103.infinityfree.com');
    $dbUser = getenv('SIMPLE_DB_USER') ?: ($localCfg['user'] ?? 'if0_42865449');
    $dbPass = getenv('SIMPLE_DB_PASS') ?: ($localCfg['pass'] ?? '');
    $dbName = getenv('SIMPLE_DB_NAME') ?: ($localCfg['name'] ?? 'if0_42865449_goji_db');
}

$koneksidb = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName)
    or die('Koneksi database gagal: ' . mysqli_connect_error());
mysqli_set_charset($koneksidb, 'utf8mb4');
if (session_status() === PHP_SESSION_NONE) session_start();
