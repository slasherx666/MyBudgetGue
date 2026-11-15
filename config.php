<?php
/**
 * config.php — dipakai oleh semua file API.
 * Isi: koneksi database, sesi login, dan fungsi bantu.
 */
declare(strict_types=1);

// --- Pengaturan database (default XAMPP: user "root", password kosong) ---
const DB_HOST = 'localhost';
const DB_NAME = 'mybudgetgue';
const DB_USER = 'root';
const DB_PASS = '';

// --- Sesi login (cookie tidak bisa dibaca JavaScript) ---
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Semua respons API berbentuk JSON
header('Content-Type: application/json; charset=utf-8');

/** Kirim respons JSON lalu hentikan program. */
function json_response(int $status, array $data)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Koneksi database (dibuat sekali, dipakai ulang). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            // Detail error sengaja tidak ditampilkan ke pengguna
            json_response(500, ['error' => 'Tidak bisa terhubung ke database. Pastikan MySQL di XAMPP menyala.']);
        }
    }
    return $pdo;
}

/** Baca isi request yang berbentuk JSON. */
function read_json(): array
{
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    return is_array($data) ? $data : [];
}

/** Hanya izinkan POST dengan Content-Type JSON (juga membantu menahan CSRF). */
function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(405, ['error' => 'Method tidak diizinkan.']);
    }
    $type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($type, 'application/json') === false) {
        json_response(415, ['error' => 'Content-Type harus application/json.']);
    }
}

/** Pastikan pengguna sudah login. Mengembalikan id pengguna. */
function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        json_response(401, ['error' => 'Belum login.']);
    }
    return (int) $_SESSION['user_id'];
}
