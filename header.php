<?php
// Layout utama: sidebar hijau + topbar. Dipakai semua halaman.
if (session_status() === PHP_SESSION_NONE)
  session_start();
$__page = $_GET['page'] ?? 'Dashboard';
$__alias = ['Cetak-Kartu' => 'Data-Siswa'];
$__page = $__alias[$__page] ?? $__page;
$__nama = $_SESSION['SES_NAMA'] ?? 'User';
$__ket = $_SESSION['SES_ROLE'] ?? '';
$__menu = menuRegistry();   // daftar menu ada di library/access.php
$__menu = array_values(array_filter(array_map(function ($m) {
  if ($m[3]) {
    $m[3] = array_values(array_filter($m[3], fn($k) => canAccess($k[1])));
    return $m[3] ? $m : null;
  }
  return canAccess($m[2]) ? $m : null;
}, $__menu)));
$__crumb = ['Beranda'];
foreach ($__menu as $__m) {
  if (!$__m[3] && $__m[2] === $__page) {
    $__crumb = [$__m[0]];
    break;
  }
  if ($__m[3]) {
    foreach ($__m[3] as $__k) {
      if ($__k[1] === $__page) {
        $__crumb = [$__m[0], $__k[0]];
        break 2;
      }
    }
  }
}
if ($__page === 'Profile') $__crumb = ['Akun', 'Profil Saya'];
elseif ($__page === 'User-Guide') $__crumb = ['Bantuan', 'User Guide'];
elseif ($__page === 'Help') $__crumb = ['Bantuan', 'Pusat Bantuan'];
elseif ($__page === 'Cetak-Rapor') $__crumb = ['Penilaian', 'Cetak Rapor'];
?><!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SIMPLE - MA Ghoyatul Jihad</title>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css">
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons-dt/1.7.1/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.8/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/simple.css?v=<?= @filemtime(__DIR__ . '/assets/css/simple.css') ?>">
  <link rel="stylesheet" href="assets/css/legacy.css?v=<?= @filemtime(__DIR__ . '/assets/css/legacy.css') ?>">
  <link rel="stylesheet" href="assets/css/theme.css?v=<?= @filemtime(__DIR__ . '/assets/css/theme.css') ?>">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/1.7.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/1.7.1/js/buttons.html5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/1.7.1/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.8/sweetalert2.all.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body>
  <section id="app">
    <aside class="side" id="side">
      <a class="side-brand" href="?page=Dashboard">
        <div class="side-brand-logo"><img src="assets/img/logo.png" alt="Logo"></div>
        <div class="side-brand-text">
          <b>SIMPLE</b>
          <span>MA Ghoyatul Jihad</span>
        </div>
      </a>
      <div class="side-scroll">
        <div class="side-search-wrap">
          <i class="fa fa-search side-search-ico"></i>
          <input id="q" class="side-search" type="search" placeholder="Cari menu..." aria-label="Cari menu"
            autocomplete="off">
          <kbd class="side-search-kbd">/</kbd>
        </div>
        <div class="menu-label">MENU UTAMA</div>
        <nav id="nav">
          <?php foreach ($__menu as $m):
            [$nm, $ic, $lnk, $kids] = $m;
            $open = $kids && in_array($__page, array_column($kids, 1), true); ?>
            <div class="nav-item<?= $open ? ' open' : '' ?>" data-open="<?= $open ? 1 : 0 ?>">
              <?php if (!$kids): ?>
                <a class="nav-btn<?= $__page === $lnk ? ' act' : '' ?>" href="?page=<?= $lnk ?>"><i
                    class="fa <?= $ic ?>"></i><span><?= e($nm) ?></span></a>
              <?php else: ?>
                <button type="button" class="nav-btn" data-toggle aria-expanded="<?= $open ? 'true' : 'false' ?>"><i
                    class="fa <?= $ic ?>"></i><span><?= e($nm) ?></span>
                  <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6" />
                  </svg></button>
                <div class="sub"><?php foreach ($kids as $k): ?>
                    <a class="sub-btn<?= $__page === $k[1] ? ' act' : '' ?>" href="?page=<?= $k[1] ?>"><?= e($k[0]) ?></a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <div class="nav-empty" id="navEmpty" hidden>Menu tidak ditemukan</div>
        </nav>
      </div>
    </aside>
    <div class="backdrop" id="backdrop" hidden></div>
    <div class="main-col">
      <header class="top">
        <div class="top-left">
          <button class="burger" id="burger" type="button" aria-label="Buka atau tutup menu"><svg viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
              <path d="M4 7h16M4 12h16M4 17h16" />
            </svg></button>
          <div class="top-crumb" aria-label="Breadcrumb">
            <?php foreach ($__crumb as $idx => $c): ?>
              <?php if ($idx > 0): ?><i class="fa fa-angle-right"></i><?php endif; ?>
              <span class="<?= $idx === count($__crumb) - 1 ? 'is-active' : '' ?>"><?= e($c) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="top-right">
          <span class="top-clock" title="Waktu saat ini"><i class="fa fa-clock-o"></i> <span id="clock"></span></span>
          <?php if (canAccess('Absen-Scan') && $__page !== 'Absen-Scan'): ?>
            <a href="?page=Absen-Scan" class="top-scan-btn" title="Buka Scanner QR Absensi"><i class="fa fa-qrcode"></i> <span>Scan QR</span></a>
          <?php endif; ?>
          <div class="userbox">
          <button class="user-btn" id="userBtn" type="button" aria-haspopup="true" aria-expanded="false">
            <span class="avatar"><?= e(initials($__nama)) ?></span><span
              class="uname"><?= e(strtoupper($__nama)) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
              stroke-linejoin="round">
              <path d="m6 9 6 6 6-6" />
            </svg></button>
          <div class="user-menu" id="userMenu" hidden>
            <div class="um-info"><b><?= e($__nama) ?></b><small><?= e($__ket) ?></small></div>
            <?php if (isset($_SESSION['MASTER_LOGIN_SES'])): ?><a href="?page=Revert"><i class="fa fa-undo fa-fw"></i>
                Kembali ke akun master</a><?php endif; ?>
            <a href="?page=Profile"><i class="fa fa-user fa-fw"></i> Profile</a>
            <a href="?page=User-Guide"><i class="fa fa-book fa-fw"></i> User Guide</a>
            <a href="?page=Help"><i class="fa fa-question-circle fa-fw"></i> Help</a>
            <a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
          </div>
          </div>
        </div>
      </header>
      <main class="content">