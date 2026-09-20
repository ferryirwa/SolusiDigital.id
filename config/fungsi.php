<?php
/**
 * Helper Functions
 * 
 * @package Website Jasa Programmer
 */

/**
 * Sanitasi input
 * 
 * @param mysqli $koneksi Koneksi database
 * @param string $data Data yang akan disanitasi
 * @return string
 */
function clean(mysqli $koneksi, string $data): string {
    $data = trim($data);
    $data = stripslashes($data);
    // Catatan: jangan pakai htmlspecialchars di sini supaya data tidak
    // ter-encode dua kali. Escaping untuk tampilan dilakukan saat output.
    return $koneksi->real_escape_string($data);
}

/**
 * Buat slug dari string
 * 
 * @param string $string
 * @return string
 */
function buat_slug(string $string): string {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]+/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Format Rupiah
 * 
 * @param int|float $angka
 * @return string
 */
function rupiah($angka): string {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 * 
 * @param string $tanggal Format Y-m-d atau datetime
 * @return string
 */
function tanggal_indo(string $tanggal): string {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecah = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

/**
 * Generate kode pesanan
 * 
 * @param mysqli $koneksi
 * @return string
 */
function generate_kode_pesanan(mysqli $koneksi): string {
    $tgl = date('Ymd');
    $prefix = 'ORD-' . $tgl . '-';

    // Ambil urutan terakhir hari ini (bukan COUNT, supaya tidak bentrok
    // kalau ada data pesanan yang dihapus)
    $urut = 1;
    $q = $koneksi->query("SELECT kode_pesanan FROM pesanan WHERE kode_pesanan LIKE '$prefix%' ORDER BY kode_pesanan DESC LIMIT 1");
    if ($q && $q->num_rows > 0) {
        $urut = (int)substr($q->fetch_assoc()['kode_pesanan'], -3) + 1;
    }

    // Pastikan kode benar-benar belum terpakai
    do {
        $kode = $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
        $cek = $koneksi->query("SELECT id FROM pesanan WHERE kode_pesanan = '" . $koneksi->real_escape_string($kode) . "' LIMIT 1");
        if (!$cek || $cek->num_rows === 0) {
            return $kode;
        }
        $urut++;
    } while ($urut <= 9999);

    return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
}

/**
 * Redirect ke URL
 * 
 * @param string $url
 * @return void
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Cek login admin
 * 
 * @return void
 */
function cek_login(): void {
    if (!isset($_SESSION['admin_id'])) {
        redirect(ADMIN_URL . 'login.php');
    }
}

/**
 * Set flash message
 * 
 * @param string $tipe Tipe alert (success, danger, warning, info)
 * @param string $pesan Pesan yang ditampilkan
 * @return void
 */
function set_flash(string $tipe, string $pesan): void {
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

/**
 * Tampilkan flash message
 * 
 * @return void
 */
function tampil_flash(): void {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-' . $f['tipe'] . ' alert-dismissible fade show" role="alert">';
        echo $f['pesan'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash']);
    }
}

/**
 * Upload gambar dengan validasi
 * 
 * @param array $file File dari $_FILES
 * @param string $tujuan Folder tujuan
 * @param int $max_size Ukuran maksimal (byte)
 * @return array{status: bool, pesan?: string, nama_file?: string}
 */
function upload_gambar(array $file, string $tujuan, int $max_size = 2097152): array {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'pesan' => 'File tidak valid.'];
    }

    if ($file['size'] > $max_size) {
        $max_mb = round($max_size / 1024 / 1024, 1);
        return ['status' => false, 'pesan' => "Ukuran file terlalu besar (max {$max_mb}MB)."];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
        return ['status' => false, 'pesan' => 'Format file harus JPG, PNG, WEBP, atau GIF.'];
    }

    // Cek MIME kalau ekstensi fileinfo aktif
    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
        $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowed_mime)) {
            return ['status' => false, 'pesan' => 'File bukan gambar yang valid.'];
        }
    }

    $nama_baru = date('YmdHis') . '_' . uniqid() . '.' . $ext;
    $path_tujuan = rtrim($tujuan, '/') . '/' . $nama_baru;

    if (!is_dir($tujuan)) {
        mkdir($tujuan, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $path_tujuan)) {
        return ['status' => true, 'nama_file' => $nama_baru];
    }

    return ['status' => false, 'pesan' => 'Gagal mengupload file.'];
}

/**
 * Potong teks
 * 
 * @param string $teks
 * @param int $panjang
 * @return string
 */
function potong_teks(string $teks, int $panjang = 100): string {
    $teks = strip_tags($teks);
    if (strlen($teks) <= $panjang) return $teks;
    return substr($teks, 0, $panjang) . '...';
}

/**
 * Normalisasi nomor WhatsApp ke format lokal Indonesia
 * (tanpa kode negara 62 dan tanpa 0 di depan)
 *
 * @param string $nomor
 * @return string
 */
function normalisasi_wa(string $nomor): string {
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    if (strpos($nomor, '62') === 0) {
        $nomor = substr($nomor, 2);
    }
    if (strpos($nomor, '0') === 0) {
        $nomor = substr($nomor, 1);
    }
    return $nomor;
}

/**
 * Buat link WhatsApp
 * 
 * @param string $nomor Nomor WA (boleh dengan atau tanpa 62/0)
 * @param string $pesan Pesan otomatis
 * @return string
 */
function link_wa(string $nomor, string $pesan = ''): string {
    $nomor = normalisasi_wa($nomor);

    // Tambahkan kode negara Indonesia kalau masih nomor lokal
    if ($nomor !== '' && $nomor[0] === '8') {
        $nomor = '62' . $nomor;
    }

    return 'https://wa.me/' . $nomor . ($pesan ? '?text=' . urlencode($pesan) : '');
}