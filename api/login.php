<?php
require __DIR__ . '/config.php';
require_post();

$in       = read_json();
$username = trim((string) ($in['username'] ?? ''));
$password = (string) ($in['password'] ?? '');

if ($username === '' || $password === '') {
    json_response(422, ['error' => 'Username dan password wajib diisi.']);
}

$stmt = db()->prepare('SELECT id, username, nama_lengkap, password_hash FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

// Pesan sengaja sama untuk "username tidak ada" dan "password salah"
if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(401, ['error' => 'Username atau password salah.']);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];

json_response(200, ['user' => [
    'id'           => (int) $user['id'],
    'username'     => $user['username'],
    'nama_lengkap' => $user['nama_lengkap'],
]]);
