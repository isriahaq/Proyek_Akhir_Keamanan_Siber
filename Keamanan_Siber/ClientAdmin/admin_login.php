<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $mysqli = new mysqli("yuven.ub.ac.id", "root", "", "library");
    if ($mysqli->connect_error) {
        die('Database connection error: ' . $mysqli->connect_error);
    }

    // Periksa apakah username ada di tabel admin dan passwordnya benar
    $stmt = $mysqli->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        if ($password === $stored_password) {
            $_SESSION['username'] = $username;
            header("Location: admin.php");
            exit;
        } else {
            echo "Username atau password salah!";
        }
    } else {
        echo "Username tidak ditemukan!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
</head>

<body>
    <h1>Login Admin</h1>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>

</html>