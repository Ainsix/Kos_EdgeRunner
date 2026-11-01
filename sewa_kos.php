<?php
require 'koneksi.php';
$aksi = $_GET['aksi'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_sewa'] ?? '';
    // input datetime-local memakai huruf "T", ubah jadi spasi untuk MySQL
    $mulai = str_replace('T', ' ', $_POST['tanggal_mulai']);
    $habis = str_replace('T', ' ', $_POST['tanggal_habis']);
    $v = [$_POST['nama_penyewa'], $_POST['no_telp_penyewa'], $_POST['id_kos'], $_POST['id_layanan'], $mulai, $habis, $_POST['status_pembayaran']];
    if ($id === '') {
        $pdo->prepare("INSERT INTO sewa_kos (nama_penyewa, no_telp_penyewa, id_kos, id_layanan, tanggal_mulai, tanggal_habis, status_pembayaran) VALUES (?,?,?,?,?,?,?)")->execute($v);
    } else {
        $v[] = $id;
        $pdo->prepare("UPDATE sewa_kos SET nama_penyewa=?, no_telp_penyewa=?, id_kos=?, id_layanan=?, tanggal_mulai=?, tanggal_habis=?, status_pembayaran=? WHERE id_sewa=?")->execute($v);
    }
    header('Location: sewa_kos.php?tipe=ok&pesan=' . urlencode('Data berhasil disimpan')); exit;
}

if ($aksi === 'hapus') {
    $pdo->prepare("DELETE FROM sewa_kos WHERE id_sewa=?")->execute([$_GET['id']]);
    header('Location: sewa_kos.php?tipe=ok&pesan=' . urlencode('Data dihapus')); exit;
}

include 'layout_atas.php';

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $d = ['id_sewa' => '', 'nama_penyewa' => '', 'no_telp_penyewa' => '', 'id_kos' => '', 'id_layanan' => '',
          'tanggal_mulai' => date('Y-m-d H:i:s'), 'tanggal_habis' => date('Y-m-d H:i:s', strtotime('+1 month')),
          'status_pembayaran' => 'belum_bayar'];
    if ($aksi === 'ubah') {
        $st = $pdo->prepare("SELECT * FROM sewa_kos WHERE id_sewa=?");
        $st->execute([$_GET['id']]);
        $d = $st->fetch();
    }
    $kos     = $pdo->query("SELECT id_kos, nomor_kamar, status_kamar FROM kos ORDER BY nomor_kamar")->fetchAll();
    $layanan = $pdo->query("SELECT id_layanan, nama_layanan, tarif_layanan FROM layanan ORDER BY nama_layanan")->fetchAll();
    $fmt = fn($t) => date('Y-m-d\TH:i', strtotime($t));
?>
<h2><?= ucfirst($aksi) ?> Sewa Kos</h2>
<form method="post">
  <input type="hidden" name="id_sewa" value="<?= e($d['id_sewa']) ?>">
  <label>Nama Penyewa</label>
  <input type="text" name="nama_penyewa" maxlength="100" required value="<?= e($d['nama_penyewa']) ?>">
  <label>No. Telepon Penyewa</label>
  <input type="text" name="no_telp_penyewa" maxlength="15" required value="<?= e($d['no_telp_penyewa']) ?>">
  <label>Kamar</label>
  <select name="id_kos" required>
    <option value="">-- pilih kamar --</option>
    <?php foreach ($kos as $k): ?>
      <option value="<?= $k['id_kos'] ?>" <?= $d['id_kos'] == $k['id_kos'] ? 'selected' : '' ?>>
        <?= e($k['nomor_kamar']) ?> (<?= e($k['status_kamar']) ?>)
      </option>
    <?php endforeach; ?>
  </select>
  <label>Layanan</label>
  <select name="id_layanan" required>
    <option value="">-- pilih layanan --</option>
    <?php foreach ($layanan as $l): ?>
      <option value="<?= $l['id_layanan'] ?>" <?= $d['id_layanan'] == $l['id_layanan'] ? 'selected' : '' ?>>
        <?= e($l['nama_layanan']) ?> - <?= rupiah($l['tarif_layanan']) ?>
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
<?php } else {
    $data = $pdo->query("
        SELECT s.*, k.nomor_kamar, k.harga_bulanan, l.nama_layanan, l.tarif_layanan
        FROM sewa_kos s
        JOIN kos k ON s.id_kos = k.id_kos
        JOIN layanan l ON s.id_layanan = l.id_layanan
        ORDER BY s.id_sewa DESC")->fetchAll();
?>
<h2>Data Sewa Kos</h2>
<a class="btn biru" href="?aksi=tambah">+ Tambah</a>
<table>
  <tr><th>No</th><th>Penyewa</th><th>Kamar</th><th>Layanan</th><th>Periode</th><th>Total/Bulan</th><th>Pembayaran</th><th>Aksi</th></tr>
  <?php foreach ($data as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($r['nama_penyewa']) ?><br><small><?= e($r['no_telp_penyewa']) ?></small></td>
    <td><?= e($r['nomor_kamar']) ?></td>
    <td><?= e($r['nama_layanan']) ?></td>
    <td><?= date('d/m/Y', strtotime($r['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($r['tanggal_habis'])) ?></td>
    <td><?= rupiah($r['harga_bulanan'] + $r['tarif_layanan']) ?></td>
    <td><?= $r['status_pembayaran'] === 'lunas' ? 'Lunas' : 'Belum bayar' ?></td>
    <td>
      <a class="btn kuning" href="?aksi=ubah&id=<?= $r['id_sewa'] ?>">Ubah</a>
      <a class="btn merah" href="?aksi=hapus&id=<?= $r['id_sewa'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php }
include 'layout_bawah.php';
