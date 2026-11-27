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
  * { box-sizing: border-box; }
  html, body { height: 100%; margin: 0; }
  body {
    font-family: "Plus Jakarta Sans", "Segoe UI", system-ui, sans-serif;
    color: #16261d;
    background:
      linear-gradient(155deg, rgba(5, 48, 22, 0.78) 0%, rgba(11, 88, 42, 0.56) 50%, rgba(4, 38, 16, 0.86) 100%),
      url(assets/img/gedung.webp) center/cover no-repeat fixed;
    -webkit-font-smoothing: antialiased;
  }

  .wrap {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 28px 16px;
    position: relative;
  }
  .wrap::before {
    content: "";
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at 50% 25%, rgba(74, 222, 128, 0.14), transparent 60%);
    pointer-events: none;
  }

  .box {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 410px;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.75);
    border-radius: 24px;
    padding: 36px 32px 28px;
    box-shadow: 0 28px 64px -16px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(13, 107, 51, 0.08);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
  }
  .school-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 5px 12px;
    border-radius: 999px;
    background: #eaf7ee;
    border: 1px solid #cce7d5;
    color: #0d6b33;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    margin-bottom: 10px;
  }
  .school-pill::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #16a34a;
  }
  .logo-wrap {
    width: 92px;
    height: 92px;
    border-radius: 22px;
    background: linear-gradient(145deg, #f3fbf5, #e6f6eb);
    border: 1px solid #d2ead9;
    display: grid;
    place-items: center;
    padding: 10px;
    margin-bottom: 4px;
    box-shadow: 0 8px 20px -8px rgba(22, 163, 74, 0.24);
  }
  .logo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .box h1 {
    margin: 4px 0 0;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: .04em;
    text-align: center;
    color: #0d6b33;
  }
  .box small {
    color: #5b7264;
    text-align: center;
    font-size: 12.5px;
    line-height: 1.45;
    margin-bottom: 16px;
  }

  form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }
  label {
    display: block;
    font-size: 11.5px;
    font-weight: 700;
    color: #365041;
    margin-bottom: 6px;
    letter-spacing: .06em;
  }
  .input-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }
  input {
    width: 100%;
    height: 46px;
    border: 1.5px solid #d2e4d8;
    border-radius: 12px;
    padding: 0 14px;
    font: inherit;
    font-size: 14px;
    background: #f8fcf9;
    color: #16261d;
    transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease;
  }
  input.has-toggle {
    padding-right: 44px;
  }
  input:hover {
    border-color: #b2d4be;
  }
  input:focus {
    outline: none;
    border-color: #16a34a;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(22, 163, 74, .14);
  }
  .pw-toggle {
    position: absolute;
    right: 8px;
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #647d6e;
    display: grid;
    place-items: center;
    cursor: pointer;
    padding: 0;
  }
  .pw-toggle:hover {
    background: #eaf5ee;
    color: #0d6b33;
  }
  .pw-toggle svg {
    width: 18px;
    height: 18px;
  }

  button[type="submit"] {
    height: 48px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #16a34a 0%, #0d7a3a 100%);
    color: #fff;
    font: inherit;
    font-weight: 700;
    font-size: 15px;
    letter-spacing: .02em;
    cursor: pointer;
    box-shadow: 0 10px 22px -8px rgba(22, 163, 74, 0.55);
    transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
  }
  button[type="submit"]:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
    box-shadow: 0 14px 26px -8px rgba(22, 163, 74, 0.6);
  }
  button[type="submit"]:active {
    transform: translateY(0);
  }

  .err {
    margin: 0;
    min-height: 20px;
    color: #c82828;
    font-size: 12.5px;
    font-weight: 600;
    text-align: center;
    border-radius: 8px;
  }
  .err:not(:empty) {
    background: #fef2f2;
    border: 1px solid #fecaca;
    padding: 8px 12px;
  }

  .note {
    margin: 14px 0 0;
    padding: 8px 12px;
    border-radius: 10px;
    background: #f3faf5;
    border: 1px dashed #cde3d4;
    font-size: 11.5px;
    color: #5b7264;
    text-align: center;
    width: 100%;
  }

  @media (max-width: 480px) {
    .box { padding: 28px 20px 22px; border-radius: 20px; }
  }
</style></head><body>
<div class="wrap">
  <div class="box">
    <div class="school-pill">MA Ghoyatul Jihad Karawang</div>
    <div class="logo-wrap"><img src="assets/img/logo.png" alt="Logo"></div>
    <h1>SIMPLE</h1>
    <small>Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif</small>
    <form method="POST" novalidate>
      <div>
        <label for="u">USERNAME</label>
        <div class="input-wrap">
          <input id="u" name="username" autocomplete="username" placeholder="Masukkan username" autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
      </div>
      <div>
        <label for="p">PASSWORD</label>
        <div class="input-wrap">
          <input id="p" class="has-toggle" name="password" type="password" autocomplete="current-password" placeholder="Masukkan password">
          <button type="button" class="pw-toggle" id="pwToggle" aria-label="Tampilkan atau sembunyikan password" title="Tampilkan password">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>
      <p class="err" role="alert"><?= htmlspecialchars($err) ?></p>
      <button type="submit">Masuk ke Sistem</button>
    </form>
    <p class="note">Akun demo: <b>admin</b> / <b>demo1234</b> &middot; Semua data adalah dummy.</p>
  </div>
</div>
<script>
  (function () {
    var btn = document.getElementById('pwToggle');
    var input = document.getElementById('p');
    if (btn && input) {
      btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      });
    }
  })();
</script>
</body></html>
