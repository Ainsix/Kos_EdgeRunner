<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aplikasi Kos</title>
<style>
  body{font-family:Segoe UI,Arial,sans-serif;margin:0;background:#f4f6f9;color:#222}
  nav{background:#2c5282;padding:12px 24px}
  nav a{color:#fff;text-decoration:none;margin-right:18px;font-weight:600}
  nav a:hover{text-decoration:underline}
  main{max-width:1000px;margin:24px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 1px 4px #0002}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:14px}
  th{background:#edf2f7}
  .btn{display:inline-block;padding:6px 12px;border-radius:4px;color:#fff;text-decoration:none;border:0;cursor:pointer;font-size:14px}
  .biru{background:#3182ce}.kuning{background:#d69e2e}.merah{background:#e53e3e}.abu{background:#718096}
  label{display:block;margin-top:12px;font-weight:600}
  input,select,textarea{width:100%;padding:8px;margin-top:4px;box-sizing:border-box;border:1px solid #ccc;border-radius:4px}
  .pesan{padding:10px;background:#fed7d7;border-radius:4px;margin-bottom:12px}
  .ok{background:#c6f6d5}
</style>
</head>
<body>
<nav>
  <a href="index.php">Beranda</a>
  <a href="kos.php">Kamar Kos</a>
  <a href="tipe_kos.php">Tipe Kos</a>
  <a href="sewa_kos.php">Sewa Kos</a>
  <a href="pegawai.php">Pegawai</a>
  <a href="layanan.php">Layanan</a>
</nav>
<main>
<?php if (!empty($_GET['pesan'])): ?>
  <div class="pesan <?= ($_GET['tipe'] ?? '') === 'ok' ? 'ok' : '' ?>"><?= e($_GET['pesan']) ?></div>
<?php endif; ?>
