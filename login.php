<?php
require_once __DIR__ . '/config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $h = hash('sha256', $_POST['password'] ?? '');
    $st = mysqli_prepare($koneksidb, "SELECT user_id, user_name, user_fullname, user_role, guru_id FROM master_user WHERE user_name = ? AND user_password = ? AND user_status = 1 LIMIT 1");
    mysqli_stmt_bind_param($st, 'ss', $u, $h);
    mysqli_stmt_execute($st);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    if ($r) {
        @mysqli_query($koneksidb, "INSERT INTO login_log (user_id, ip_address, status) VALUES (" . (int)$r['user_id'] . ", '" . mysqli_real_escape_string($koneksidb, $_SERVER['REMOTE_ADDR'] ?? '') . "', 'Success')");
        session_regenerate_id(true);
        $_SESSION['SES_LOGIN'] = true;
        $_SESSION['SES_USERID'] = $r['user_id'];
        $_SESSION['SES_NAMA'] = $r['user_fullname'];
        $_SESSION['SES_USER_NAME'] = $r['user_name'];
        $_SESSION['SES_ROLE'] = $r['user_role'];
        $_SESSION['SES_GURU_ID'] = $r['guru_id'];
        header('Location: index.php?page=Dashboard'); exit;
    }
    $err = 'Username atau password salah.';
}
?><!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk | SIMPLE MA Ghoyatul Jihad</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<style>
  *{box-sizing:border-box} html,body{height:100%;margin:0}
  body{font-family:"Plus Jakarta Sans","Segoe UI",system-ui,sans-serif;color:#1f2a24;background:#fff}
  .wrap{display:grid;grid-template-columns:1.25fr 1fr;min-height:100vh}
  .photo{position:relative;background:linear-gradient(165deg,rgba(6,70,32,.78),rgba(6,70,32,.12) 70%),url(assets/img/gedung.webp) center/cover;display:flex;align-items:flex-end;padding:56px;color:#fff}
  .photo h2{margin:0 0 6px;font-size:34px;font-weight:800;line-height:1.15;text-shadow:0 2px 12px rgba(0,0,0,.35)}
  .photo p{margin:0;font-size:15px;opacity:.95;max-width:460px}
  .panel{display:grid;place-items:center;padding:32px;background:#fff}
  .box{width:100%;max-width:350px;display:flex;flex-direction:column;align-items:center;gap:6px}
  .box img{width:104px;height:104px;object-fit:contain}
  .box h1{margin:6px 0 0;font-size:20px;font-weight:800;text-align:center;color:#0f7a3d}
  .box small{color:#5f7468;text-align:center;font-size:12.5px;margin-bottom:14px}
  form{width:100%;display:flex;flex-direction:column;gap:14px}
  label{display:block;font-size:12px;font-weight:700;color:#3d5246;margin-bottom:5px;letter-spacing:.04em}
  input{width:100%;height:44px;border:1.5px solid #cfe0d5;border-radius:10px;padding:0 14px;font:inherit;background:#f7fbf8;color:#1f2a24}
  input:focus{outline:none;border-color:#16a34a;background:#fff;box-shadow:0 0 0 3px rgba(22,163,74,.15)}
  button{height:46px;border:0;border-radius:10px;background:linear-gradient(90deg,#16a34a,#0f7a3d);color:#fff;font:inherit;font-weight:700;font-size:15px;cursor:pointer}
  button:hover{filter:brightness(1.07)}
  .err{margin:0;min-height:18px;color:#d63b3b;font-size:12.5px;text-align:center}
  .note{margin:6px 0 0;font-size:12px;color:#7a8c82;text-align:center}
  @media(max-width:860px){.wrap{grid-template-columns:1fr}.photo{min-height:240px;padding:28px}.photo h2{font-size:24px}}
</style></head><body>
<div class="wrap">
  <div class="photo"><div><h2>MA Ghoyatul Jihad<br>Karawang</h2><p>Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif.</p></div></div>
  <div class="panel"><div class="box">
    <img src="assets/img/logo.png" alt="Logo">
    <h1>SIMPLE</h1>
    <small>Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif</small>
    <form method="POST" novalidate>
      <div><label for="u">USERNAME</label><input id="u" name="username" autocomplete="username" autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" ></div>
      <div><label for="p">PASSWORD</label><input id="p" name="password" type="password" autocomplete="current-password" ></div>
      <p class="err" role="alert"><?= htmlspecialchars($err) ?></p>
      <button type="submit">Masuk</button>
    </form>
    <p class="note">Akun demo: admin / demo1234. Semua data adalah dummy.</p>
  </div></div>
</div>
</body></html>
