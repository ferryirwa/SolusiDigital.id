<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'profil.php');
}

$aksi = $_POST['aksi'] ?? '';
$id = $_SESSION['admin_id'];

// ============ AKSI: UPDATE PROFIL ============
if ($aksi === 'profil') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $errors = [];

    if (empty($nama)) {
        $errors[] = 'Nama wajib diisi.';
    } elseif (strlen($nama) > 100) {
        $errors[] = 'Nama maksimal 100 karakter.';
    }

    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } else {
            $e_esc = $koneksi->real_escape_string($email);
            $cek = $koneksi->query("SELECT id FROM admin WHERE email = '$e_esc' AND id != $id");
            if ($cek->num_rows > 0) {
                $errors[] = 'Email sudah dipakai admin lain.';
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['form_profil'] = $_POST;
        set_flash('danger', implode('<br>', $errors));
        redirect(ADMIN_URL . 'profil.php');
    }

    // Update
    $stmt = $koneksi->prepare("UPDATE admin SET nama = ?, email = ? WHERE id = ?");
    $stmt->bind_param('ssi', $nama, $email, $id);

    if ($stmt->execute()) {
        // Update session nama
        $_SESSION['admin_nama'] = $nama;
        $_SESSION['admin_email'] = $email;
        set_flash('success', '✅ Profil berhasil diupdate!');
    } else {
        set_flash('danger', '❌ Gagal update: ' . $stmt->error);
    }

    redirect(ADMIN_URL . 'profil.php');
}

// ============ AKSI: GANTI PASSWORD ============
if ($aksi === 'password') {
    $lama = $_POST['password_lama'] ?? '';
    $baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['password_konfirmasi'] ?? '';

    $errors = [];

    if (empty($lama)) $errors[] = 'Password lama wajib diisi.';
    if (empty($baru)) $errors[] = 'Password baru wajib diisi.';
    if (strlen($baru) < 6) $errors[] = 'Password baru minimal 6 karakter.';
    if ($baru !== $konfirmasi) $errors[] = 'Konfirmasi password tidak sama.';
    if ($lama === $baru) $errors[] = 'Password baru harus berbeda dari yang lama.';

    if (!empty($errors)) {
        set_flash('danger', implode('<br>', $errors));
        redirect(ADMIN_URL . 'profil.php');
    }

    // Ambil password lama dari DB
    $stmt = $koneksi->prepare("SELECT password FROM admin WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || !password_verify($lama, $row['password'])) {
        set_flash('danger', 'Password lama salah!');
        redirect(ADMIN_URL . 'profil.php');
    }

    // Update password
    $hash = password_hash($baru, PASSWORD_BCRYPT);
    $stmt_upd = $koneksi->prepare("UPDATE admin SET password = ? WHERE id = ?");
    $stmt_upd->bind_param('si', $hash, $id);

    if ($stmt_upd->execute()) {
        // Logout otomatis
        session_destroy();
        session_start();
        session_regenerate_id(true);
        $_SESSION['flash'] = ['tipe' => 'success', 'pesan' => '✅ Password berhasil diubah. Silakan login ulang.'];
        redirect(ADMIN_URL . 'login.php');
    } else {
        set_flash('danger', '❌ Gagal ganti password: ' . $stmt_upd->error);
        redirect(ADMIN_URL . 'profil.php');
    }
}

// Kalau aksi tidak dikenali
redirect(ADMIN_URL . 'profil.php');