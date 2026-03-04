<?php
// Konfiguráció – ezeket élesben .env fájlba vagy config.php-ba tedd ki!
$servername = "localhost";
$username   = "root";               // ← saját adatbázis felhasználó
$password   = "";                   // ← saját jelszó
$dbname     = "BMP-180";

// API kulcs (élesben ezt is adatbázisból ellenőrizd – lásd sensor_data_api_keys.sql)
$valid_api_key = "123456789";       // ← cseréld ki a Python scriptben használt kulcsra!

header('Content-Type: text/plain; charset=utf-8');

// Kapcsolódás
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// API kulcs ellenőrzése (GET paraméterből)
if (!isset($_GET['api_key']) || $_GET['api_key'] !== $valid_api_key) {
    http_response_code(403);
    echo "Érvénytelen vagy hiányzó API kulcs!";
    exit;
}

// Szükséges paraméterek ellenőrzése
if (!isset($_GET['temperature']) || !isset($_GET['pressure'])) {
    http_response_code(400);
    echo "Hiányzó paraméterek: temperature és pressure szükséges!";
    exit;
}

// Értékek szűrése / validálása
$temperature = filter_var($_GET['temperature'], FILTER_VALIDATE_FLOAT);
$pressure    = filter_var($_GET['pressure'],    FILTER_VALIDATE_FLOAT);

if ($temperature === false || $pressure === false) {
    http_response_code(400);
    echo "Érvénytelen értékek (nem szám)!";
    exit;
}

// Értelmezhető tartomány ellenőrzés (opcionális, de ajánlott)
if ($temperature < -50 || $temperature > 100 || $pressure < 300 || $pressure > 1200) {
    http_response_code(400);
    echo "Értékek kívül esnek a fizikai tartományon!";
    exit;
}

// Beszúrás prepared statementtel (biztonságos)
$stmt = $conn->prepare("
    INSERT INTO measurements (temperature, pressure, created_at)
    VALUES (?, ?, NOW())
");

$stmt->bind_param("dd", $temperature, $pressure);

if ($stmt->execute()) {
    echo "Adatok sikeresen rögzítve. Temp: {$temperature} °C, Nyomás: {$pressure} hPa";
} else {
    http_response_code(500);
    echo "Adatbázis hiba: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
