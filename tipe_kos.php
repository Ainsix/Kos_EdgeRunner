<?php
require 'koneksi.php';
$aksi = $_GET['aksi'] ?? 'list';

// Simpan (tambah / ubah)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_tipe'] ?? '';
    if ($id === '') {
        $st = $pdo->prepare("INSERT INTO tipe_kos (nama_tipe, deskripsi) VALUES (?, ?)");
        $st->execute([$_POST['nama_tipe'], $_POST['deskripsi']]);
    } else {
        $st = $pdo->prepare("UPDATE tipe_kos SET nama_tipe=?, deskripsi=? WHERE id_tipe=?");
        $st->execute([$_POST['nama_tipe'], $_POST['deskripsi'], $id]);
    }
    header('Location: tipe_kos.php?tipe=ok&pesan=' . urlencode('Data berhasil disimpan')); exit;
}

// Hapus
if ($aksi === 'hapus') {
    try {
        $pdo->prepare("DELETE FROM tipe_kos WHERE id_tipe=?")->execute([$_GET['id']]);
        $p = 'tipe=ok&pesan=' . urlencode('Data dihapus');
    } catch (PDOException $e) {
        $p = 'pesan=' . urlencode('Tidak bisa dihapus, masih dipakai data kos');
    }
    header("Location: tipe_kos.php?$p"); exit;
}

include 'layout_atas.php';

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ['id_tipe' => '', 'nama_tipe' => '', 'deskripsi' => ''];
    if ($aksi === 'ubah') {
        $st = $pdo->prepare("SELECT * FROM tipe_kos WHERE id_tipe=?");
        $st->execute([$_GET['id']]);
        $d = $st->fetch();
    }
?>
<h2><?= ucfirst($aksi) ?> Tipe Kos</h2>
<form method="post">
  <input type="hidden" name="id_tipe" value="<?= e($d['id_tipe']) ?>">
  <label>Nama Tipe</label>
  <input type="text" name="nama_tipe" maxlength="50" required value="<?= e($d['nama_tipe']) ?>">
  <label>Deskripsi</label>
  <textarea name="deskripsi" rows="4"><?= e($d['deskripsi']) ?></textarea>
  <br><br>
  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="tipe_kos.php">Batal</a>
</form>
<?php } else {
    $data = $pdo->query("SELECT * FROM tipe_kos ORDER BY id_tipe DESC")->fetchAll();
?>
<h2>Data Tipe Kos</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr><th>No</th><th>Nama Tipe</th><th>Deskripsi</th><th>Aksi</th></tr>
  <?php foreach ($data as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($r['nama_tipe']) ?></td>
    <td><?= e($r['deskripsi']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_tipe'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_tipe'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
include 'layout_bawah.php';
