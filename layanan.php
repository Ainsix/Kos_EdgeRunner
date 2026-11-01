<?php
require 'koneksi.php';
$aksi = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_layanan'] ?? '';
    $v  = [$_POST['nama_layanan'], $_POST['tarif_layanan']];
    if ($id === '') {
        $pdo->prepare("INSERT INTO layanan (nama_layanan, tarif_layanan) VALUES (?,?)")->execute($v);
    } else {
        $v[] = $id;
        $pdo->prepare("UPDATE layanan SET nama_layanan=?, tarif_layanan=? WHERE id_layanan=?")->execute($v);
    }
    header('Location: layanan.php?tipe=ok&pesan=' . urlencode('Data berhasil disimpan')); exit;
}

if ($aksi === 'hapus') {
    try {
        $pdo->prepare("DELETE FROM layanan WHERE id_layanan=?")->execute([$_GET['id']]);
        $p = 'tipe=ok&pesan=' . urlencode('Data dihapus');
    } catch (PDOException $e) {
        $p = 'pesan=' . urlencode('Tidak bisa dihapus, layanan masih dipakai data sewa');
    }
    header("Location: layanan.php?$p"); exit;
}

include 'layout_atas.php';

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ['id_layanan' => '', 'nama_layanan' => '', 'tarif_layanan' => ''];
    if ($aksi === 'ubah') {
        $st = $pdo->prepare("SELECT * FROM layanan WHERE id_layanan=?");
        $st->execute([$_GET['id']]);
        $d = $st->fetch();
    }
?>
<h2><?= ucfirst($aksi) ?> Layanan</h2>
<form method="post">
  <input type="hidden" name="id_layanan" value="<?= e($d['id_layanan']) ?>">
  <label>Nama Layanan</label>
  <input type="text" name="nama_layanan" maxlength="100" required value="<?= e($d['nama_layanan']) ?>">
  <label>Tarif Layanan (Rp)</label>
  <input type="number" step="0.01" min="0" name="tarif_layanan" required value="<?= e($d['tarif_layanan']) ?>">
  <br><br>
  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="layanan.php">Batal</a>
</form>
<?php } else {
    $data = $pdo->query("SELECT * FROM layanan ORDER BY id_layanan DESC")->fetchAll();
?>
<h2>Data Layanan</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr><th>No</th><th>Nama Layanan</th><th>Tarif</th><th>Aksi</th></tr>
  <?php foreach ($data as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($r['nama_layanan']) ?></td>
    <td><?= rupiah($r['tarif_layanan']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_layanan'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_layanan'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
include 'layout_bawah.php';
