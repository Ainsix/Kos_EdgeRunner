<?php
require 'koneksi.php';

$h     = new Halaman();
$model = new Kos();
$aksi  = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->simpan($_POST, $_POST['id'] ?? '');
    $h->redirect('kos.php', 'Data berhasil disimpan');
}

if ($aksi === 'hapus') {
    if ($model->hapus($_GET['id'])) {
        $h->redirect('kos.php', 'Data dihapus');
    }
    $h->redirect('kos.php', 'Tidak bisa dihapus, kamar masih punya riwayat sewa', false);
}

$h->atas();

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ($aksi === 'ubah')
        ? $model->cari($_GET['id'])
        : ['nomor_kamar' => '', 'harga_bulanan' => '', 'status_kamar' => 'kosong', 'id_tipe' => '', 'id_pegawai' => ''];
    $tipe    = (new TipeKos())->semua();
    $pegawai = (new pegawai())->semua();
?>
<h2><?= ucfirst($aksi) ?> Kamar Kos</h2>
<form method="post">

  <input type="hidden" name="id" value="<?= $aksi === 'ubah' ? $h->e($_GET['id']) : '' ?>">
  <label>Nomor Kamar</label>

  <input type="text" name="nomor_kamar" maxlength="10" required value="<?= $h->e($d['nomor_kamar']) ?>">
  <label>Harga Bulanan (Rp)</label>

  <input type="number" step="0.01" min="0" name="harga_bulanan" required value="<?= $h->e($d['harga_bulanan']) ?>">
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
      <option value="<?= $t['id_tipe'] ?>" <?= $d['id_tipe'] == $t['id_tipe'] ? 'selected' : '' ?>><?= $h->e($t['nama_tipe']) ?></option>
    <?php endforeach; ?>
  </select>

  <label>Pegawai Penanggung Jawab</label>
  <select name="id_pegawai" required>
    <option value="">-- pilih pegawai --</option>
    <?php foreach ($pegawai as $p): ?>
      <option value="<?= $p['id_pegawai'] ?>" <?= $d['id_pegawai'] == $p['id_pegawai'] ? 'selected' : '' ?>><?= $h->e($p['nama_pegawai']) ?></option>
    <?php endforeach; ?>
  </select>

  <br><br>
  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="kos.php">Batal</a>

</form>

<?php } else { ?>
<h2>Data Kamar Kos</h2>
<p>
  Total kamar: <b><?= $model->jumlahKamar() ?></b> |
  Kosong: <b><?= $model->jumlahKamar('kosong') ?></b> |
  Sewa belum bayar: <b><?= (new SewaKos())->jumlahBelumBayar() ?></b>
</p>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr>
    <th>No</th>
    <th>Kamar</th>
    <th>Tipe</th>
    <th>Harga/Bulan</th>
    <th>Status</th>
    <th>Pegawai</th>
    <th>Aksi</th>
  </tr>
  <?php foreach ($model->semua() as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= $h->e($r['nomor_kamar']) ?></td>
    <td><?= $h->e($r['nama_tipe']) ?></td>
    <td><?= $h->rupiah($r['harga_bulanan']) ?></td>
    <td><?= $h->e(ucfirst($r['status_kamar'])) ?></td>
    <td><?= $h->e($r['nama_pegawai']) ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_kos'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_kos'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
$h->bawah();