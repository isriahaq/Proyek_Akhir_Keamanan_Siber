<?php
// File: server_soap.php (BARU)

// Kelas yang akan menangani logika bisnis perpustakaan
class LibraryService {
    private $conn;

    public function __construct() {
        try {
            // Koneksi ke database. Pastikan kredensial ini benar.
            $this->conn = new PDO("mysql:host=192.168.1.20;dbname=library", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Jika koneksi gagal, throw SoapFault agar client tahu ada masalah
            throw new SoapFault("Server", "Database connection error: " . $e->getMessage());
        }
    }

    /**
     * Mencari buku berdasarkan judul.
     * Menggunakan prepared statement untuk mencegah SQL Injection.
     * @param string $title
     * @return string
     */
    public function searchBooks($title) {
        $stmt = $this->conn->prepare("SELECT title, author, year FROM books WHERE title LIKE ?");
        $searchTerm = "%" . $title . "%";
        $stmt->execute([$searchTerm]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($results) > 0) {
            $response = "Hasil pencarian untuk '{$title}':\n";
            foreach ($results as $book) {
                $response .= "- {$book['title']} by {$book['author']} ({$book['year']})\n";
            }
            return $response;
        } else {
            return "Tidak ada buku yang cocok dengan judul '{$title}'.";
        }
    }

    /**
     * Mendapatkan semua daftar buku.
     * @return string
     */
    public function getAllBooks() {
        $stmt = $this->conn->query("SELECT title, author, year FROM books");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bookArray = [];
        foreach ($results as $book) {
            $bookArray[] = "{$book['title']} by {$book['author']} ({$book['year']})";
        }
        return implode("; ", $bookArray);
    }

    /**
     * Memproses peminjaman buku.
     * Memindahkan data buku ke tabel borrowed_books.
     * @param string $bookTitle
     * @param string $username
     * @return string
     */
    public function pinjamBuku($bookTitle, $username) {
        if (empty($bookTitle) || empty($username)) {
            throw new SoapFault("Client", "Book title and username are required.");
        }

        try {
            $this->conn->beginTransaction();

            // Cek apakah buku ada dan belum dipinjam
            $stmt = $this->conn->prepare("SELECT id FROM books WHERE title = ?");
            $stmt->execute([$bookTitle]);
            $book = $stmt->fetch();

            if (!$book) {
                $this->conn->rollBack();
                return "Peminjaman gagal: Buku dengan judul '{$bookTitle}' tidak ditemukan.";
            }

            // Pindahkan ke tabel borrowed_books
            $stmt = $this->conn->prepare("INSERT INTO borrowed_books (book_title, username) VALUES (?, ?)");
            $stmt->execute([$bookTitle, $username]);
            
            // Hapus buku dari tabel 'books' (asumsi buku fisik hanya ada satu)
            $stmt = $this->conn->prepare("DELETE FROM books WHERE title = ?");
            $stmt->execute([$bookTitle]);

            $this->conn->commit();
            
            return "Peminjaman buku '{$bookTitle}' oleh '{$username}' berhasil.";

        } catch (PDOException $e) {
            $this->conn->rollBack();
            // Jangan ekspos detail error database ke client
            throw new SoapFault("Server", "Terjadi kesalahan internal saat memproses peminjaman.");
        }
    }
}

// Menonaktifkan WSDL caching selama pengembangan
ini_set('soap.wsdl_cache_enabled', '0');

// Opsi untuk SOAP server
$options = [
    'uri' => '[http://192.168.1.20/revisi/](http://192.168.1.20/revisi/)', // URI untuk namespace
];

// Buat instance SoapServer
$server = new SoapServer(null, $options);
// Set kelas yang akan menangani request
$server->setClass('LibraryService');
// Handle request SOAP yang masuk
$server->handle();
?>
