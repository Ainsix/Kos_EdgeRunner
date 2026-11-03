<?php
require 'koneksi.php';

$h     = new Halaman();
$model = new Pegawai();
$aksi  = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->simpan($_POST, $_POST['id'] ?? '');
    $h->redirect('pegawai.php', 'Data berhasil disimpan');
}

if ($aksi === 'hapus') {
    if ($model->hapus($_GET['id'])) {
        $h->redirect('pegawai.php', 'Data dihapus');
    }
    $h->redirect('pegawai.php', 'Tidak bisa dihapus, pegawai masih memegang kamar', false);
}

$h->atas();

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ($aksi === 'ubah') ? $model->cari($_GET['id']) : ['nama_pegawai' => '', 'no_telp' => '', 'jabatan' => ''];
?>
<h2><?= ucfirst($aksi) ?> Pegawai</h2>

<form method="post">
  <input type="hidden" name="id" value="<?= $aksi === 'ubah' ? $h->e($_GET['id']) : '' ?>">
  <label>Nama Pegawai</label>

  <input type="text" name="nama_pegawai" maxlength="100" required value="<?= $h->e($d['nama_pegawai']) ?>">
  <label>No. Telepon</label>

  <input type="text" name="no_telp" maxlength="15" required value="<?= $h->e($d['no_telp']) ?>">
  <label>Jabatan</label>

  <input type="text" name="jabatan" maxlength="50" required value="<?= $h->e($d['jabatan']) ?>">
  <br><br>

  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="pegawai.php">Batal</a>
</form>

<?php } else { ?>
<h2>Data Pegawai</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr><th>No</th><th>Nama</th><th>No. Telp</th><th>Jabatan</th><th>Aksi</th></tr>
  <?php foreach ($model->semua() as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= $h->e($r['nama_pegawai']) ?></td>
    <td><?= $h->e($r['no_telp']) ?></td>
    <td><?= $h->e($r['jabatan']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_pegawai'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_pegawai'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
$h->bawah();