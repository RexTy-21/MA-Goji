<?php
// Koneksi database DEMO sekolah
date_default_timezone_set('Asia/Jakarta');
mysqli_report(MYSQLI_REPORT_OFF);
$koneksidb = mysqli_connect(
    getenv('SIMPLE_DB_HOST') ?: '127.0.0.1',
    getenv('SIMPLE_DB_USER') ?: 'root',
    getenv('SIMPLE_DB_PASS') ?: '',
    getenv('SIMPLE_DB_NAME') ?: 'goji_db'
) or die('Koneksi database gagal: ' . mysqli_connect_error());
mysqli_set_charset($koneksidb, 'utf8mb4');
if (session_status() === PHP_SESSION_NONE) session_start();
