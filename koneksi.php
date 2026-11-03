<?php
trait FormatTampilan {
    public function e($teks) {
        return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
    }

    public function rupiah($angka) {
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

trait PesanRedirect {
    public function redirect($halaman, $pesan, $berhasil = true) {
        $tipe = $berhasil ? 'ok' : 'gagal';
        header("Location: {$halaman}?tipe={$tipe}&pesan=" . urlencode($pesan));
        exit;
    }
}

class Database {
    private static $pdo = null;

    private function __construct() {}   

    public static function koneksi() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO('mysql:host=localhost;dbname=db_kos;charset=utf8mb4', 'root', '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die('Koneksi gagal: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}


abstract class Model {
    protected $db;
    protected $tabel;   
    protected $kunci;  
    protected $kolom;   

    public function __construct() {
        $this->db = Database::koneksi();
    }

    public function semua() {
        return $this->db->query("SELECT * FROM {$this->tabel} ORDER BY {$this->kunci} DESC")->fetchAll();
    }

    public function cari($id) {
        $st = $this->db->prepare("SELECT * FROM {$this->tabel} WHERE {$this->kunci} = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public function simpan(array $data, $id = '') {
        $nilai = [];
        foreach ($this->kolom as $k) {
            $nilai[] = $data[$k] ?? null;
        }

        if ($id === '') {
            $tanda = implode(',', array_fill(0, count($this->kolom), '?'));
            $sql = "INSERT INTO {$this->tabel} (" . implode(',', $this->kolom) . ") VALUES ($tanda)";
        } else {
            $set = implode('=?, ', $this->kolom) . '=?';
            $sql = "UPDATE {$this->tabel} SET $set WHERE {$this->kunci} = ?";
            $nilai[] = $id;
        }
        $this->db->prepare($sql)->execute($nilai);
    }

    public function hapus($id) {
        try {
            $this->db->prepare("DELETE FROM {$this->tabel} WHERE {$this->kunci} = ?")->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

class TipeKos extends Model {
    protected $tabel = 'tipe_kos';
    protected $kunci = 'id_tipe';
    protected $kolom = ['nama_tipe', 'deskripsi'];
}

class Pegawai extends Model {
    protected $tabel = 'pegawai';
    protected $kunci = 'id_pegawai';
    protected $kolom = ['nama_pegawai', 'no_telp', 'jabatan'];
}

class Layanan extends Model {
    protected $tabel = 'layanan';
    protected $kunci = 'id_layanan';
    protected $kolom = ['nama_layanan', 'tarif_layanan'];
}

class Kos extends Model {
    protected $tabel = 'kos';
    protected $kunci = 'id_kos';
    protected $kolom = ['nomor_kamar', 'harga_bulanan', 'status_kamar', 'id_tipe', 'id_pegawai'];

    public function semua() {
        return $this->db->query("
            SELECT k.*, t.nama_tipe, p.nama_pegawai
            FROM kos k
            JOIN tipe_kos t ON k.id_tipe = t.id_tipe
            JOIN pegawai p ON k.id_pegawai = p.id_pegawai
            ORDER BY k.id_kos DESC")->fetchAll();
    }

    public function jumlahKamar($status = null) {
        if ($status === null) {
            return $this->db->query("SELECT COUNT(*) FROM kos")->fetchColumn();
        }
        $st = $this->db->prepare("SELECT COUNT(*) FROM kos WHERE status_kamar = ?");
        $st->execute([$status]);
        return $st->fetchColumn();
    }
}

class SewaKos extends Model {
    protected $tabel = 'sewa_kos';
    protected $kunci = 'id_sewa';
    protected $kolom = ['nama_penyewa', 'no_telp_penyewa', 'id_kos', 'id_layanan', 'tanggal_mulai', 'tanggal_habis', 'status_pembayaran'];

    public function semua() {
        return $this->db->query("
            SELECT s.*, k.nomor_kamar, k.harga_bulanan, l.nama_layanan, l.tarif_layanan
            FROM sewa_kos s
            JOIN kos k ON s.id_kos = k.id_kos
            JOIN layanan l ON s.id_layanan = l.id_layanan
            ORDER BY s.id_sewa DESC")->fetchAll();
    }

    public function simpan(array $data, $id = '') {
        $data['tanggal_mulai'] = str_replace('T', ' ', $data['tanggal_mulai']);
        $data['tanggal_habis'] = str_replace('T', ' ', $data['tanggal_habis']);
        parent::simpan($data, $id);
    }

    public function jumlahBelumBayar() {
        return $this->db->query("SELECT COUNT(*) FROM sewa_kos WHERE status_pembayaran='belum_bayar'")->fetchColumn();
    }
}

class Halaman {
    use FormatTampilan, PesanRedirect;

    public function atas() { ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aplikasi Kos</title>
<style>
  body{font-family:Segoe UI,Arial,sans-serif;margin:0;background:#f4f6f9;color:#222}
  nav{background:#2c5282;padding:12px 24px}
  nav a{color:#fff;text-decoration:none;margin-right:18px;font-weight:600}
  nav a:hover{text-decoration:underline}
  main{max-width:1000px;margin:24px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 1px 4px #0002}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:14px}
  th{background:#edf2f7}
  .btn{display:inline-block;padding:6px 12px;border-radius:4px;color:#fff;text-decoration:none;border:0;cursor:pointer;font-size:14px}
  .biru{background:#3182ce}.kuning{background:#d69e2e}.merah{background:#e53e3e}.abu{background:#718096}
  label{display:block;margin-top:12px;font-weight:600}
  input,select,textarea{width:100%;padding:8px;margin-top:4px;box-sizing:border-box;border:1px solid #ccc;border-radius:4px}
  .pesan{padding:10px;background:#fed7d7;border-radius:4px;margin-bottom:12px}
  .ok{background:#c6f6d5}
</style>
</head>
<body>
<nav>
  <a href="kos.php">Kamar Kos</a>
  <a href="tipe_kos.php">Tipe Kos</a>
  <a href="sewa_kos.php">Sewa Kos</a>
  <a href="pegawai.php">Pegawai</a>
  <a href="layanan.php">Layanan</a>
</nav>
<main>
<?php if (!empty($_GET['pesan'])): ?>
  <div class="pesan <?= ($_GET['tipe'] ?? '') === 'ok' ? 'ok' : '' ?>"><?= $this->e($_GET['pesan']) ?></div>
<?php endif;
    }

    public function bawah() { ?>
</main>
</body>
</html>
<?php }
}