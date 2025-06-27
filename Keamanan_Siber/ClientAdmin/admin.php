<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

$mysqli = new mysqli("yuven.ub.ac.id", "root", "", "library");
if ($mysqli->connect_error) {
    die('Database connection error: ' . $mysqli->connect_error);
}

echo "<h1>Selamat Datang, " . htmlspecialchars($_SESSION['username']) . "</h1>";
echo "<h2>Tambah Anggota Perpustakaan</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];

    // Kirim permintaan untuk menambah anggota
    $url = 'http://yuven.ub.ac.id/ServerTeman/add_member.php';
    $data = json_encode(['username' => $username]);

    // opsi permintaan http
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => $data,
        ],
    ];

    // Konteks permintaan dibuat dengan opsi yang telah ditentukan
    $context = stream_context_create($options);
    // mengirim permintaan ke server dan mendapatkan responsnya
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "Error: Tidak dapat menghubungi server.";
    } else {
        $response = json_decode($result, true);
        echo htmlspecialchars($response['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tambah Anggota</title>
</head>

<body>
    <form method="POST">
        <input type="text" name="username" placeholder="Masukkan username" required>
        <button type="submit">Tambah Anggota</button>
    </form>
</body>

</html>