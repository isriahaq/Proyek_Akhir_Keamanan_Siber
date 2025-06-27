<?php
// File: check_member.php (REVISI)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Ganti '*' dengan domain client Anda
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config.php';

// Validasi API Key, 'client' atau 'admin' bisa mengakses
validate_api_key('client'); 

// Koneksi database
$host = 'localhost';
$user = 'root';
$pass = '24434';
$db = 'library_members';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || empty(trim($data['username']))) {
    http_response_code(400);
    die(json_encode(['error' => 'Username is required.']));
}
$username = trim($data['username']);

// Menggunakan prepared statement
$query = "SELECT * FROM members WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode([
    'status' => $result->num_rows > 0 ? 'member' : 'non-member'
]);

$stmt->close();
$conn->close();
?>