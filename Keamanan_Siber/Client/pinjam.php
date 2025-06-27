<?php
// File: pinjam.php (REVISI)
session_start();

if (!isset($_SESSION['username'])) {
    die("Anda harus login terlebih dahulu.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $bookTitle = $_POST['book'];
    $username = $_SESSION['username'];

    // --- PERUBAHAN KEAMANAN DIMULAI DI SINI ---
    
    // URL endpoint untuk cek keanggotaan
    $restUrl = '[http://192.168.1.4/revisi/check_member.php](http://192.168.1.4/revisi/check_member.php)';
    
    // Kunci API untuk client (seharusnya disimpan dengan aman)
    $apiKey = 'def-456-client-readonly-key'; 

    // Data yang akan dikirim dalam body request
    $data = json_encode(['username' => $username]);

    // Opsi untuk request cURL
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n" .
                        "Authorization: Bearer {$apiKey}\r\n", // Menambahkan header Authorization
            'method' => 'POST',
            'content' => $data,
            'ignore_errors' => true // Agar bisa membaca body respons bahkan saat status error (misal 403)
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($restUrl, false, $context);

    // --- AKHIR PERUBAHAN KEAMANAN ---

    if ($response === FALSE) {
        die("Tidak dapat mengakses REST API untuk verifikasi keanggotaan.");
    }

    $result = json_decode($response, true);

    if (!is_array($result) || !isset($result['status'])) {
        // Cek apakah ada pesan error dari API
        if (isset($result['message'])) {
            die("Error dari API: " . htmlspecialchars($result['message']));
        }
        die("Respons REST API tidak valid.");
    }

    if ($result['status'] === 'member') {
        // (Logika peminjaman buku via SOAP tetap sama)
        try {
            $client = new SoapClient(null, [
                'location' => '[http://192.168.1.20/revisi/server_soap.php](http://192.168.1.20/revisi/server_soap.php)',
                'uri' => '[http://192.168.1.20/revisi/](http://192.168.1.20/revisi/)',
                'trace' => 1,
            ]);

            $resultSoap = $client->__soapCall('pinjamBuku', [$bookTitle, $username]);
            echo "Pesan dari server: " . htmlspecialchars($resultSoap);

        } catch (SoapFault $fault) {
            echo "SOAP Fault: " . htmlspecialchars($fault->faultcode) . " - " . htmlspecialchars($fault->faultstring);
        }
        echo '<br><a href="client.php">Kembali ke Halaman Awal</a>';
    } else {
        echo "Peminjaman Gagal: User '" . htmlspecialchars($username) . "' belum terdaftar sebagai anggota perpustakaan.";
        echo '<br><a href="client.php">Kembali ke Halaman Awal</a>';
    }
}
?>
