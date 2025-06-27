<?php
// register.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    try {
        // Koneksi ke database
        $conn = new PDO("mysql:host=192.168.1.20;dbname=library", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Simpan ke database
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            // Jika pendaftaran berhasil, alihkan ke halaman login
            header("Location: login.php");
            exit();
        } else {
            echo "Pendaftaran gagal!";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }

    // Tutup koneksi
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>

<body>
    <h2>Daftar</h2>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Daftar</button>
    </form>
</body>

</html>