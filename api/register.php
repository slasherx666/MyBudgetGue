<?php
require __DIR__ . '/config.php';
require_post();

$in       = read_json();
$username = trim((string) ($in['username'] ?? ''));
$nama     = trim((string) ($in['nama_lengkap'] ?? ''));
$password = (string) ($in['password'] ?? '');

// --- Validasi (selalu dilakukan di server, jangan hanya di browser) ---
if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
    json_response(422, ['error' => 'Username 3-30 karakter, hanya huruf, angka, dan garis bawah (_).']);
}
if (strlen($password) < 6) {
    json_response(422, ['error' => 'Password minimal 6 karakter.']);
}
if (strlen($password) > 72) {
    json_response(422, ['error' => 'Password maksimal 72 karakter.']);
}
if ($nama === '') {
    $nama = $username;
}
if (mb_strlen($nama) > 100) {
    json_response(422, ['error' => 'Nama lengkap maksimal 100 karakter.']);
}

$pdo = db();

// Username sudah dipakai?
$cek = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$cek->execute([$username]);
if ($cek->fetch()) {
    json_response(409, ['error' => 'Username sudah dipakai. Coba yang lain.']);
}

// Simpan akun + pengaturan awal dalam satu transaksi (sukses semua atau batal semua)
$pdo->beginTransaction();
try {
    $ins = $pdo->prepare(
        'INSERT INTO users (username, nama_lengkap, password_hash, unlocked_badges)
         VALUES (?, ?, ?, ?)'
    );
    $ins->execute([$username, $nama, password_hash($password, PASSWORD_DEFAULT), '[]']);
    $userId = (int) $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO settings (user_id) VALUES (?)')->execute([$userId]);
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() === '23000') { // username kembar (dua pendaftar bersamaan)
        json_response(409, ['error' => 'Username sudah dipakai. Coba yang lain.']);
    }
    json_response(500, ['error' => 'Gagal membuat akun. Coba lagi.']);
}

// Langsung login setelah daftar
session_regenerate_id(true);
$_SESSION['user_id'] = $userId;

json_response(201, ['user' => [
    'id'           => $userId,
    'username'     => $username,
    'nama_lengkap' => $nama,
]]);
