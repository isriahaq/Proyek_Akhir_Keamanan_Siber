<?php
// File: config.php
// File ini bisa di-include di semua endpoint untuk menyimpan konfigurasi terpusat.

// Definisikan API Keys
// Di dunia nyata, ini harus disimpan di environment variables atau secure vault, bukan di hardcode.
define('API_KEY_ADMIN', 'abc-123-admin-secret-key'); // Kunci untuk admin (full access)
define('API_KEY_CLIENT', 'def-456-client-readonly-key'); // Kunci untuk client (read-only)

// Fungsi untuk validasi API Key
function validate_api_key($role = 'client') {
    $headers = getallheaders();
    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');

    if (empty($auth_header)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authorization header not found.']);
        exit;
    }

    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $api_key = $matches[1];
        if ($role === 'admin') {
            if ($api_key !== API_KEY_ADMIN) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden: Invalid Admin API Key.']);
                exit;
            }
        } else { // 'client' role
            if ($api_key !== API_KEY_CLIENT && $api_key !== API_KEY_ADMIN) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden: Invalid Client API Key.']);
                exit;
            }
        }
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid Authorization header format. Use "Bearer <token>".']);
        exit;
    }
}
?>
