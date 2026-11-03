<?php
require 'koneksi.php';

$h     = new Halaman();
$model = new TipeKos();
$aksi  = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->simpan($_POST, $_POST['id'] ?? '');
    $h->redirect('tipe_kos.php', 'Data berhasil disimpan');
}

if ($aksi === 'hapus') {
    if ($model->hapus($_GET['id'])) {
        $h->redirect('tipe_kos.php', 'Data dihapus');
    }
    $h->redirect('tipe_kos.php', 'Tidak bisa dihapus, masih dipakai data kos', false);
}

$h->atas();

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ($aksi === 'ubah') ? $model->cari($_GET['id']) : ['nama_tipe' => '', 'deskripsi' => ''];
?>
<h2><?= ucfirst($aksi) ?> Tipe Kos</h2>
<form method="post">

  <input type="hidden" name="id" value="<?= $aksi === 'ubah' ? $h->e($_GET['id']) : '' ?>">
  <label>Nama Tipe</label>

  <input type="text" name="nama_tipe" maxlength="50" required value="<?= $h->e($d['nama_tipe']) ?>">
  <label>Deskripsi</label>

  <textarea name="deskripsi" rows="4"><?= $h->e($d['deskripsi']) ?></textarea>
  <br><br>

  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="tipe_kos.php">Batal</a>

</form>

<?php } else { ?>
<h2>Data Tipe Kos</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>

  <tr>
    <th>No</th>
    <th>Nama Tipe</th>
    <th>Deskripsi</th>
    <th>Aksi</th>
   </tr>
   
  <?php foreach ($model->semua() as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= $h->e($r['nama_tipe']) ?></td>
    <td><?= $h->e($r['deskripsi']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_tipe'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_tipe'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
$h->bawah();