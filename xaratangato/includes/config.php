<?php
// ============================================================
//  XARATANGATO - Configuración de Base de Datos
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'xaratangatobd');
define('APP_NAME', 'Xaratangato');
define('APP_URL', 'http://localhost/xaratangato/xaratangato');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
?>
