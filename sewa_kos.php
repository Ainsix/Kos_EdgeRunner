<?php
require 'koneksi.php';

$h     = new Halaman();
$model = new SewaKos();
$aksi  = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model->simpan($_POST, $_POST['id'] ?? '');
    $h->redirect('sewa_kos.php', 'Data berhasil disimpan');
}

if ($aksi === 'hapus') {
    $model->hapus($_GET['id']);
    $h->redirect('sewa_kos.php', 'Data dihapus');
}

$h->atas();

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ($aksi === 'ubah') ? $model->cari($_GET['id']) : [
        'nama_penyewa' => '', 'no_telp_penyewa' => '', 'id_kos' => '', 'id_layanan' => '',
        'tanggal_mulai' => date('Y-m-d H:i:s'), 'tanggal_habis' => date('Y-m-d H:i:s', strtotime('+1 month')),
        'status_pembayaran' => 'belum_bayar',
    ];
    $kos     = (new Kos())->semua();
    $layanan = (new Layanan())->semua();
    $fmt     = fn($t) => date('Y-m-d\TH:i', strtotime($t));
?>
<h2><?= ucfirst($aksi) ?> Sewa Kos</h2>
<form method="post">

  <input type="hidden" name="id" value="<?= $aksi === 'ubah' ? $h->e($_GET['id']) : '' ?>">
  <label>Nama Penyewa</label>

  <input type="text" name="nama_penyewa" maxlength="100" required value="<?= $h->e($d['nama_penyewa']) ?>">
  <label>No. Telepon Penyewa</label>

  <input type="text" name="no_telp_penyewa" maxlength="15" required value="<?= $h->e($d['no_telp_penyewa']) ?>">
  <label>Kamar</label>
  <select name="id_kos" required>
    <option value="">-- pilih kamar --</option>
    <?php foreach ($kos as $k): ?>
      <option value="<?= $k['id_kos'] ?>" <?= $d['id_kos'] == $k['id_kos'] ? 'selected' : '' ?>>
        <?= $h->e($k['nomor_kamar']) ?> (<?= $h->e($k['status_kamar']) ?>)
      </option>
    <?php endforeach; ?>
  </select>

  <label>Layanan</label>
  <select name="id_layanan" required>
    <option value="">-- pilih layanan --</option>
    <?php foreach ($layanan as $l): ?>
      <option value="<?= $l['id_layanan'] ?>" <?= $d['id_layanan'] == $l['id_layanan'] ? 'selected' : '' ?>>
        <?= $h->e($l['nama_layanan']) ?> - <?= $h->rupiah($l['tarif_layanan']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Tanggal Mulai</label>
  <input type="datetime-local" name="tanggal_mulai" required value="<?= $fmt($d['tanggal_mulai']) ?>">

  <label>Tanggal Habis</label>
  <input type="datetime-local" name="tanggal_habis" required value="<?= $fmt($d['tanggal_habis']) ?>">

  <label>Status Pembayaran</label>
  <select name="status_pembayaran">
    <option value="belum_bayar" <?= $d['status_pembayaran'] === 'belum_bayar' ? 'selected' : '' ?>>Belum bayar</option>
    <option value="lunas" <?= $d['status_pembayaran'] === 'lunas' ? 'selected' : '' ?>>Lunas</option>
  </select>

  <br><br>
  <button class="btn biru">Simpan</button>
  <a class="btn abu" href="sewa_kos.php">Batal</a>

</form>

<?php } else { ?>
<h2>Data Sewa Kos</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>

<table>
  <tr><th>No</th><th>Penyewa</th><th>Kamar</th><th>Layanan</th><th>Periode</th><th>Total/Bulan</th><th>Pembayaran</th><th>Aksi</th></tr>
  <?php foreach ($model->semua() as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>

    <td><?= $h->e($r['nama_penyewa']) ?><br><small><?= $h->e($r['no_telp_penyewa']) ?></small></td>

    <td><?= $h->e($r['nomor_kamar']) ?></td>

    <td><?= $h->e($r['nama_layanan']) ?></td>

    <td><?= date('d/m/Y', strtotime($r['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($r['tanggal_habis'])) ?></td>

    <td><?= $h->rupiah($r['harga_bulanan'] + $r['tarif_layanan']) ?></td>

    <td><?= $r['status_pembayaran'] === 'lunas' ? 'Lunas' : 'Belum bayar' ?></td>

    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_sewa'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_sewa'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
    
  </tr>
  <?php endforeach; ?>
</table>
<?php }
$h->bawah();