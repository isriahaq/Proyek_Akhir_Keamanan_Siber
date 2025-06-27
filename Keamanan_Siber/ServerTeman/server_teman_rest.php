<?php
header('Content-Type: application/json');

$mysqli = new mysqli("alexiw.ub.ac.id", "root", "", "library");

if ($mysqli->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection error']));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $username = $_GET['username'];

    // pernytaan sql
    $stmt = $mysqli->prepare("SELECT id FROM members WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Anggota terdaftar']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Anggota tidak terdaftar']);
    }
}
?>