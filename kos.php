<?php
require 'koneksi.php';
$aksi = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_kos'] ?? '';
    $v  = [$_POST['nomor_kamar'], $_POST['harga_bulanan'], $_POST['status_kamar'], $_POST['id_tipe'], $_POST['id_pegawai']];
    if ($id === '') {
        $pdo->prepare("INSERT INTO kos (nomor_kamar, harga_bulanan, status_kamar, id_tipe, id_pegawai) VALUES (?,?,?,?,?)")->execute($v);
    } else {
        $v[] = $id;
        $pdo->prepare("UPDATE kos SET nomor_kamar=?, harga_bulanan=?, status_kamar=?, id_tipe=?, id_pegawai=? WHERE id_kos=?")->execute($v);
    }
    header('Location: kos.php?tipe=ok&pesan=' . urlencode('Data berhasil disimpan')); exit;
}

if ($aksi === 'hapus') {
    try {
        $pdo->prepare("DELETE FROM kos WHERE id_kos=?")->execute([$_GET['id']]);
        $p = 'tipe=ok&pesan=' . urlencode('Data dihapus');
    } catch (PDOException $e) {
        $p = 'pesan=' . urlencode('Tidak bisa dihapus, kamar masih punya riwayat sewa');
    }
    header("Location: kos.php?$p"); exit;
}

include 'layout_atas.php';

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ['id_kos' => '', 'nomor_kamar' => '', 'harga_bulanan' => '', 'status_kamar' => 'kosong', 'id_tipe' => '', 'id_pegawai' => ''];
    if ($aksi === 'ubah') {
        $st = $pdo->prepare("SELECT * FROM kos WHERE id_kos=?");
        $st->execute([$_GET['id']]);
        $d = $st->fetch();
    }
    $tipe    = $pdo->query("SELECT id_tipe, nama_tipe FROM tipe_kos ORDER BY nama_tipe")->fetchAll();
    $pegawai = $pdo->query("SELECT id_pegawai, nama_pegawai FROM pegawai ORDER BY nama_pegawai")->fetchAll();
?>
<h2><?= ucfirst($aksi) ?> Kamar Kos</h2>
<form method="post">
  <input type="hidden" name="id_kos" value="<?= e($d['id_kos']) ?>">
  <label>Nomor Kamar</label>
  <input type="text" name="nomor_kamar" maxlength="10" required value="<?= e($d['nomor_kamar']) ?>">
  <label>Harga Bulanan (Rp)</label>
  <input type="number" step="0.01" min="0" name="harga_bulanan" required value="<?= e($d['harga_bulanan']) ?>">
  <label>Status Kamar</label>
  <select name="status_kamar">
    <?php foreach (['kosong', 'terisi', 'perbaikan'] as $s): ?>
      <option value="<?= $s ?>" <?= $d['status_kamar'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <label>Tipe Kos</label>
  <select name="id_tipe" required>
    <option value="">-- pilih tipe --</option>
    <?php foreach ($tipe as $t): ?>
      <option value="<?= $t['id_tipe'] ?>" <?= $d['id_tipe'] == $t['id_tipe'] ? 'selected' : '' ?>><?= e($t['nama_tipe']) ?></option>
    <?php endforeach; ?>
  </select>
  <label>Pegawai Penanggung Jawab</label>
  <select name="id_pegawai" required>
    <option value="">-- pilih pegawai --</option>
    <?php foreach ($pegawai as $p): ?>
      <option value="<?= $p['id_pegawai'] ?>" <?= $d['id_pegawai'] == $p['id_pegawai'] ? 'selected' : '' ?>><?= e($p['nama_pegawai']) ?></option>
    <?php endforeach; ?>
  </select>
  <br><br>
  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="kos.php">Batal</a>
</form>
<?php } else {
    $data = $pdo->query("
        SELECT k.*, t.nama_tipe, p.nama_pegawai
        FROM kos k
        JOIN tipe_kos t ON k.id_tipe = t.id_tipe
        JOIN pegawai p ON k.id_pegawai = p.id_pegawai
        ORDER BY k.id_kos DESC")->fetchAll();
?>
<h2>Data Kamar Kos</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr><th>No</th><th>Kamar</th><th>Tipe</th><th>Harga/Bulan</th><th>Status</th><th>Pegawai</th><th>Aksi</th></tr>
  <?php foreach ($data as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($r['nomor_kamar']) ?></td>
    <td><?= e($r['nama_tipe']) ?></td>
    <td><?= rupiah($r['harga_bulanan']) ?></td>
    <td><?= e(ucfirst($r['status_kamar'])) ?></td>
    <td><?= e($r['nama_pegawai']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_kos'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_kos'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
include 'layout_bawah.php';
