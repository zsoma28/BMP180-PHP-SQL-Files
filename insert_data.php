<?php
// insert_data.php – egyszerű verzió, API kulcs NÉLKÜL (csak teszteléshez!)

$servername = "localhost";
$username   = "root";          // saját adatbázis user
$password   = "";              // saját jelszó
$dbname     = "BMP-180";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Szükséges paraméterek
if (!isset($_GET['temperature']) || !isset($_GET['pressure'])) {
    http_response_code(400);
    die("Hiányzó paraméterek: temperature és pressure kell!");
}

$temperature = floatval($_GET['temperature']);
$pressure    = floatval($_GET['pressure']);

// Beszúrás (egyszerű, nem prepared – teszteléshez OK, élesben NE!)
$sql = "INSERT INTO measurements (temperature, pressure, created_at)
        VALUES ($temperature, $pressure, NOW())";

if ($conn->query($sql) === TRUE) {
    echo "Adatok sikeresen rögzítve! Temp: $temperature °C, Nyomás: $pressure hPa";
} else {
    echo "Hiba: " . $conn->error;
}

$conn->close();
?>
