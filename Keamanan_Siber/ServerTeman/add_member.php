<?php
header('Content-Type: application/json');

$mysqli = new mysqli("alexiw.ub.ac.id", "root", "", "library");

if ($mysqli->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection error']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];

    // Cek apakah username sudah terdaftar di tabel users
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Cek apakah username sudah ada di tabel members
        $stmt = $mysqli->prepare("SELECT id FROM members WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Tambahkan ke tabel members jika belum ada
            $stmt = $mysqli->prepare("INSERT INTO members (username) VALUES (?)");
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Anggota berhasil ditambahkan.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan anggota.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User sudah terdaftar sebagai anggota.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User belum membuat akun di website.']);
    }
}
?>