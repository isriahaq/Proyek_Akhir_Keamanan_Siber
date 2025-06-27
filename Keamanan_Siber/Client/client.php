<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Buku</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Pencarian Buku</h1>
    <form method="POST">
        <input type="text" name="title" placeholder="Masukkan judul buku" required>
        <button type="submit">Cari</button>
    </form>

    <?php
    try {
        // Buat instance dari SOAP client
        $client = new SoapClient(null, [
            'location' => 'http://192.168.1.20/TUGAS_AKHIR_PERCOBAAN/Server/server.php',
            'uri' => 'http://192.168.1.20/TUGAS_AKHIR_PERCOBAAN/Server/',
            'trace' => 1,
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
            // Panggil metode searchBooks
            $title = $_POST['title'];
            $result = $client->__soapCall('searchBooks', [$title]);
            echo "<h2>Hasil Pencarian:</h2>";
            echo "<p>$result</p>";
        }

        // Tampilkan semua buku
        echo "<h2>Daftar Semua Buku:</h2>";
        $allBooks = $client->__soapCall('getAllBooks', []);

        // Koneksi ke database untuk mendapatkan buku yang sudah dipinjam
        $mysqli = new mysqli("192.168.1.20", "root", "", "library");
        if ($mysqli->connect_error) {
            die('Database connection error: ' . $mysqli->connect_error);
        }

        $borrowedBooksQuery = "SELECT book_title FROM borrowed_books";
        $borrowedResult = $mysqli->query($borrowedBooksQuery);

        $borrowedBooks = [];
        while ($row = $borrowedResult->fetch_assoc()) {
            $borrowedBooks[] = $row['book_title'];
        }

        // Ubah string menjadi array dan tampilkan dalam tabel
        $bookArray = explode("; ", $allBooks);

        echo "<table>";
        echo "<tr><th>Judul</th><th>Penulis</th><th>Tahun</th><th>Aksi</th></tr>";

        foreach ($bookArray as $book) {
            if (!empty($book)) {
                preg_match('/^(.*?) by (.*?) \((\d{4})\)$/', $book, $matches);
                if ($matches) {
                    echo "<tr><td>{$matches[1]}</td><td>{$matches[2]}</td><td>{$matches[3]}</td>";
                    if (!in_array($matches[1], $borrowedBooks)) {
                        echo "<td><form method='POST' action='pinjam.php'><input type='hidden' name='book' value='{$matches[1]}'><button type='submit'>Pinjam</button></form></td>";
                    } else {
                        echo "<td>Sudah Dipinjam</td>";
                    }
                    echo "</tr>";
                }
            }
        }

        echo "</table>";

    } catch (SoapFault $fault) {
        echo "Fault: " . htmlspecialchars($fault->faultcode) . " - " . htmlspecialchars($fault->faultstring);
    }
    ?>
</body>

</html>