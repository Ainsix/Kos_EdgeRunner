<?php
require 'koneksi.php';
include 'layout_atas.php';

$total   = $pdo->query("SELECT COUNT(*) FROM kos")->fetchColumn();
$kosong  = $pdo->query("SELECT COUNT(*) FROM kos WHERE status_kamar='kosong'")->fetchColumn();
$terisi  = $pdo->query("SELECT COUNT(*) FROM kos WHERE status_kamar='terisi'")->fetchColumn();
$belumBayar = $pdo->query("SELECT COUNT(*) FROM sewa_kos WHERE status_pembayaran='belum_bayar'")->fetchColumn();
?>
<h2>Dashboard Kos</h2>
<table>
  <tr><th>Total Kamar</th><td><?= $total ?></td></tr>
  <tr><th>Kamar Kosong</th><td><?= $kosong ?></td></tr>
  <tr><th>Kamar Terisi</th><td><?= $terisi ?></td></tr>
  <tr><th>Sewa Belum Bayar</th><td><?= $belumBayar ?></td></tr>
</table>
<?php include 'layout_bawah.php'; ?>
