<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'admin.php');
}

// Hanya super admin yang bisa akses
if ($_SESSION['admin_role'] !== 'super') {
    set_flash('danger', 'Anda tidak memiliki akses untuk mengelola admin.');
    redirect(ADMIN_URL . 'dashboard.php');
}

// ============ AMBIL DATA ============
$id               = (int)($_POST['id'] ?? 0);
$mode             = $_POST['mode'] ?? 'tambah';
$username         = trim($_POST['username'] ?? '');
$nama             = trim($_POST['nama'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$role             = $_POST['role'] ?? 'admin';

// ============ VALIDASI ============
$errors = [];

// Username
if (empty($username)) {
    $errors[] = 'Username wajib diisi.';
} elseif (strlen($username) < 4 || strlen($username) > 50) {
    $errors[] = 'Username harus 4-50 karakter.';
} elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    $errors[] = 'Username hanya boleh huruf, angka, titik, underscore, dan dash.';
} else {
    // Cek username unik
    $u_esc = $koneksi->real_escape_string($username);
    if ($mode === 'edit' && $id > 0) {
        $cek = $koneksi->query("SELECT id FROM admin WHERE username = '$u_esc' AND id != $id");
    } else {
        $cek = $koneksi->query("SELECT id FROM admin WHERE username = '$u_esc'");
    }
    if ($cek->num_rows > 0) {
        $errors[] = "Username '$username' sudah dipakai.";
    }
}

// Nama
if (empty($nama)) {
    $errors[] = 'Nama lengkap wajib diisi.';
} elseif (strlen($nama) > 100) {
    $errors[] = 'Nama maksimal 100 karakter.';
}

// Email
if (!empty($email)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        // Cek email unik
        $e_esc = $koneksi->real_escape_string($email);
        if ($mode === 'edit' && $id > 0) {
            $cek = $koneksi->query("SELECT id FROM admin WHERE email = '$e_esc' AND id != $id");
        } else {
            $cek = $koneksi->query("SELECT id FROM admin WHERE email = '$e_esc'");
        }
        if ($cek->num_rows > 0) {
            $errors[] = "Email '$email' sudah dipakai.";
        }
    }
}

// Password
if ($mode === 'tambah') {
    if (empty($password)) {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $errors[] = 'Password dan konfirmasi tidak sama.';
    }
} else {
    // Edit — password opsional
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Password dan konfirmasi tidak sama.';
        }
    }
}

// Role
if (!in_array($role, ['super', 'admin'])) {
    $errors[] = 'Role tidak valid.';
}

// ============ JIKA ERROR ============
if (!empty($errors)) {
    $_SESSION['form_admin'] = $_POST;
    set_flash('danger', 'Periksa kembali:<br>• ' . implode('<br>• ', $errors));
    if ($mode === 'edit') {
        redirect(ADMIN_URL . "admin_form.php?id=$id");
    } else {
        redirect(ADMIN_URL . 'admin_form.php');
    }
}

// ============ SIMPAN ============
if ($mode === 'edit' && $id > 0) {
    // Cek admin target
    $cek_admin = $koneksi->prepare("SELECT role FROM admin WHERE id = ? LIMIT 1");
    $cek_admin->bind_param('i', $id);
    $cek_admin->execute();
    $target = $cek_admin->get_result()->fetch_assoc();

    if (!$target) {
        set_flash('danger', 'Admin tidak ditemukan!');
        redirect(ADMIN_URL . 'admin.php');
    }

    // Cegah downgrade super admin terakhir
    if ($target['role'] === 'super' && $role !== 'super') {
        $total_super = $koneksi->query("SELECT COUNT(*) as jml FROM admin WHERE role='super'")->fetch_assoc()['jml'];
        if ($total_super <= 1) {
            set_flash('danger', 'Tidak bisa downgrade super admin terakhir!');
            redirect(ADMIN_URL . "admin_form.php?id=$id");
        }
    }

    // Update
    if (!empty($password)) {
        // Update dengan password
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE admin SET username=?, nama=?, email=?, password=?, role=? WHERE id=?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('sssssi', $username, $nama, $email, $hash, $role, $id);
    } else {
        // Update tanpa password
        $sql = "UPDATE admin SET username=?, nama=?, email=?, role=? WHERE id=?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('ssssi', $username, $nama, $email, $role, $id);
    }

    if ($stmt->execute()) {
        set_flash('success', '✅ Admin <strong>' . htmlspecialchars($nama) . '</strong> berhasil diupdate!');
    } else {
        set_flash('danger', '❌ Gagal update: ' . $stmt->error);
    }

} else {
    // INSERT
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO admin (username, password, nama, email, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('sssss', $username, $hash, $nama, $email, $role);

    if ($stmt->execute()) {
        set_flash('success', '✅ Admin <strong>' . htmlspecialchars($nama) . '</strong> berhasil ditambahkan!');
    } else {
        set_flash('danger', '❌ Gagal menyimpan: ' . $stmt->error);
    }
}

redirect(ADMIN_URL . 'admin.php');