<?php
require __DIR__ . '/config.php';

$userId = require_login();

$stmt = db()->prepare('SELECT id, username, nama_lengkap FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) { // akun sudah dihapus, tapi sesi masih ada
    $_SESSION = [];
    session_destroy();
    json_response(401, ['error' => 'Belum login.']);
}

json_response(200, ['user' => [
    'id'           => (int) $user['id'],
    'username'     => $user['username'],
    'nama_lengkap' => $user['nama_lengkap'],
]]);
