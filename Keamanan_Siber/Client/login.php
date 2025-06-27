<?php
// File: login.php (REVISI)
session_start();

const MAX_LOGIN_ATTEMPTS = 5; // Maksimal 5x percobaan gagal
const LOCKOUT_TIME = 300; // Dikunci selama 300 detik (5 menit)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah akun sedang terkunci
    if (isset($_SESSION['login_attempts'][$username]) && $_SESSION['login_attempts'][$username]['count'] >= MAX_LOGIN_ATTEMPTS) {
        $time_since_lockout = time() - $_SESSION['login_attempts'][$username]['time'];
        if ($time_since_lockout < LOCKOUT_TIME) {
            $remaining_time = LOCKOUT_TIME - $time_since_lockout;
            die("Terlalu banyak percobaan login gagal. Coba lagi dalam {$remaining_time} detik.");
        } else {
            // Waktu lockout sudah habis, reset counter
            unset($_SESSION['login_attempts'][$username]);
        }
    }

    try {
        $conn = new PDO("mysql:host=192.168.1.20;dbname=library", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row['password'])) {
                // Login berhasil, hapus catatan percobaan gagal jika ada
                unset($_SESSION['login_attempts'][$username]);
                $_SESSION['username'] = $username;
                header("Location: client.php");
                exit();
            } else {
                // Login gagal, catat percobaan
                if (!isset($_SESSION['login_attempts'][$username])) {
                    $_SESSION['login_attempts'][$username] = ['count' => 1, 'time' => time()];
                } else {
                    $_SESSION['login_attempts'][$username]['count']++;
                    $_SESSION['login_attempts'][$username]['time'] = time();
                }
                echo "Password salah!";
            }
        } else {
            echo "Username tidak ditemukan!";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
