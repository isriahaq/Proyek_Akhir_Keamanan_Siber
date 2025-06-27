<?php
// File: list_members.php (REVISI)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Ganti '*' dengan domain admin panel Anda
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config.php';

// Validasi API Key, hanya 'admin' yang bisa mengakses
validate_api_key('admin');

$host = 'localhost';
$user = 'root';
$pass = '24434';
$db = 'library_members';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Connection failed']));
}

$query = "SELECT username, created_at FROM members ORDER BY created_at DESC";
$result = $conn->query($query);

$members = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
}

echo json_encode($members);
$conn->close();
?>
