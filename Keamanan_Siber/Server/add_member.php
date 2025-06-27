<?php
// File: add_member.php (REVISI)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Di produksi, ganti '*' dengan domain admin panel Anda, misal: '[http://admin.perpustakaan.com](http://admin.perpustakaan.com)'
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Tambahkan 'Authorization'

require_once 'config.php';

// Validasi API Key, hanya role 'admin' yang bisa mengakses endpoint ini
validate_api_key('admin');

// Koneksi database
$host = 'localhost';
$user = 'root';
$pass = '24434';
$db = 'library_members';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$data = json_decode(file_get_contents('php://input'), true);

// Validasi input dasar
if (!isset($data['username']) || empty(trim($data['username']))) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Username is required.']));
}
$username = trim($data['username']);

// Menggunakan prepared statement untuk mencegah SQL Injection (sudah baik dari kode awal)
$query = "INSERT INTO members (username) VALUES (?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Anggota berhasil ditambahkan'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan anggota. Error: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>