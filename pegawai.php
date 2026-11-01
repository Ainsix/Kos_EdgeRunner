<?php
require 'koneksi.php';
$aksi = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_pegawai'] ?? '';
    $v  = [$_POST['nama_pegawai'], $_POST['no_telp'], $_POST['jabatan']];
    if ($id === '') {
        $pdo->prepare("INSERT INTO pegawai (nama_pegawai, no_telp, jabatan) VALUES (?,?,?)")->execute($v);
    } else {
        $v[] = $id;
        $pdo->prepare("UPDATE pegawai SET nama_pegawai=?, no_telp=?, jabatan=? WHERE id_pegawai=?")->execute($v);
    }
    header('Location: pegawai.php?tipe=ok&pesan=' . urlencode('Data berhasil disimpan')); exit;
}

if ($aksi === 'hapus') {
    try {
        $pdo->prepare("DELETE FROM pegawai WHERE id_pegawai=?")->execute([$_GET['id']]);
        $p = 'tipe=ok&pesan=' . urlencode('Data dihapus');
    } catch (PDOException $e) {
        $p = 'pesan=' . urlencode('Tidak bisa dihapus, pegawai masih memegang kamar');
    }
    header("Location: pegawai.php?$p"); exit;
}

include 'layout_atas.php';

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ['id_pegawai' => '', 'nama_pegawai' => '', 'no_telp' => '', 'jabatan' => ''];
    if ($aksi === 'ubah') {
        $st = $pdo->prepare("SELECT * FROM pegawai WHERE id_pegawai=?");
        $st->execute([$_GET['id']]);
        $d = $st->fetch();
    }
?>
<h2><?= ucfirst($aksi) ?> Pegawai</h2>
<form method="post">
  <input type="hidden" name="id_pegawai" value="<?= e($d['id_pegawai']) ?>">
  <label>Nama Pegawai</label>
  <input type="text" name="nama_pegawai" maxlength="100" required value="<?= e($d['nama_pegawai']) ?>">
  <label>No. Telepon</label>
  <input type="text" name="no_telp" maxlength="15" required value="<?= e($d['no_telp']) ?>">
  <label>Jabatan</label>
  <input type="text" name="jabatan" maxlength="50" required value="<?= e($d['jabatan']) ?>">
  <br><br>
  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="pegawai.php">Batal</a>
</form>
<?php } else {
    $data = $pdo->query("SELECT * FROM pegawai ORDER BY id_pegawai DESC")->fetchAll();
?>
<h2>Data Pegawai</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr><th>No</th><th>Nama</th><th>No. Telp</th><th>Jabatan</th><th>Aksi</th></tr>
  <?php foreach ($data as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($r['nama_pegawai']) ?></td>
    <td><?= e($r['no_telp']) ?></td>
    <td><?= e($r['jabatan']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_pegawai'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_pegawai'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
include 'layout_bawah.php';
