<?php
require 'koneksi.php';

$h     = new Halaman();
$model = new Layanan();
$aksi  = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->simpan($_POST, $_POST['id'] ?? '');
    $h->redirect('layanan.php', 'Data berhasil disimpan');
}

if ($aksi === 'hapus') {
    if ($model->hapus($_GET['id'])) {
        $h->redirect('layanan.php', 'Data dihapus');
    }
    $h->redirect('layanan.php', 'Tidak bisa dihapus, layanan masih dipakai data sewa', false);
}

$h->atas();

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ($aksi === 'ubah') ? $model->cari($_GET['id']) : ['nama_layanan' => '', 'tarif_layanan' => ''];
?>
<h2><?= ucfirst($aksi) ?> Layanan</h2>
<form method="post">
  <input type="hidden" name="id" value="<?= $aksi === 'ubah' ? $h->e($_GET['id']) : '' ?>">
  <label>Nama Layanan</label>
  <input type="text" name="nama_layanan" maxlength="100" required value="<?= $h->e($d['nama_layanan']) ?>">
  <label>Tarif Layanan (Rp)</label>
  <input type="number" step="0.01" min="0" name="tarif_layanan" required value="<?= $h->e($d['tarif_layanan']) ?>">
  <br><br>
  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="layanan.php">Batal</a>
</form>
<?php } else { ?>
<h2>Data Layanan</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr><th>No</th><th>Nama Layanan</th><th>Tarif</th><th>Aksi</th></tr>
  <?php foreach ($model->semua() as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= $h->e($r['nama_layanan']) ?></td>
    <td><?= $h->rupiah($r['tarif_layanan']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_layanan'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_layanan'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
$h->bawah();