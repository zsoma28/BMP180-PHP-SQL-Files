<?php
/**
 * BMP180 Adatbeviteli Modul - Biztonságos verzió
 * Használat: insert_data.php?api_key=123456789&temperature=25.4&pressure=1013.25
 */

// --- 1. KONFIGURÁCIÓ ---
$servername = "localhost";
$username   = "root";          // Az adatbázis felhasználód
$password   = "";              // Az adatbázis jelszavad
$dbname     = "BMP180";

// Ezt a kulcsot állítsd be az eszközödön (pl. ESP32 vagy Python script) is!
$valid_api_key = "123456789"; 

header('Content-Type: text/plain; charset=utf-8');

// --- 2. KAPCSOLÓDÁS ---
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    die("Adatbázis kapcsolódási hiba!");
}

// --- 3. BIZTONSÁGI ELLENŐRZÉSEK ---

// API Kulcs ellenőrzése
if (!isset($_GET['api_key']) || $_GET['api_key'] !== $valid_api_key) {
    http_response_code(403); // Forbidden
    die("Hiba: Érvénytelen vagy hiányzó API kulcs!");
}

// Paraméterek meglétének ellenőrzése
if (!isset($_GET['temperature']) || !isset($_GET['pressure'])) {
    http_response_code(400); // Bad Request
    die("Hiba: Hiányzó adatok (temperature és pressure szükséges)!");
}

// Adatok tisztítása és validálása (szám-e?)
$temperature = filter_var($_GET['temperature'], FILTER_VALIDATE_FLOAT);
$pressure    = filter_var($_GET['pressure'],    FILTER_VALIDATE_FLOAT);

if ($temperature === false || $pressure === false) {
    http_response_code(400);
    die("Hiba: Érvénytelen formátum (csak számokat fogadunk el)!");
}

// Ésszerű tartomány ellenőrzése (opcionális, védi az adatbázist a hibás mérésektől)
if ($temperature < -50 || $temperature > 80 || $pressure < 300 || $pressure > 1200) {
    http_response_code(400);
    die("Hiba: Az adatok kívül esnek a reális fizikai tartományon!");
}

// --- 4. ADATMENTÉS (PREPARED STATEMENT) ---
$sql = "INSERT INTO measurements (temperature, pressure, created_at) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("dd", $temperature, $pressure); // "dd" = két darab double (lebegőpontos) szám

    if ($stmt->execute()) {
        echo "Siker: Adatok rögzítve! ({$temperature} °C, {$pressure} hPa)";
    } else {
        http_response_code(500);
        echo "Hiba: Nem sikerült a mentés az adatbázisba: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    http_response_code(500);
    echo "Hiba: SQL előkészítési hiba!";
}

$conn->close();
?>
