<?php
// data_manager.php - Kombinált adatkezelő (Írás és Olvasás)

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "BMP180";
$valid_api_key = "123456789";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(["error" => "Kapcsolódási hiba"]));
}

// --- 1. ÁG: ADATBEVÉTEL (Ha van temperature és pressure a GET-ben) ---
if (isset($_GET['temperature']) && isset($_GET['pressure'])) {
    header('Content-Type: text/plain; charset=utf-8');

    // API kulcs ellenőrzése
    if (!isset($_GET['api_key']) || $_GET['api_key'] !== $valid_api_key) {
        http_response_code(403);
        die("Hiba: Érvénytelen API kulcs!");
    }

    $temp = filter_var($_GET['temperature'], FILTER_VALIDATE_FLOAT);
    $press = filter_var($_GET['pressure'], FILTER_VALIDATE_FLOAT);

    if ($temp !== false && $press !== false) {
        $stmt = $conn->prepare("INSERT INTO measurements (temperature, pressure, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("dd", $temp, $press);
        if ($stmt->execute()) {
            echo "Siker: Adat mentve!";
        } else {
            http_response_code(500);
            echo "Mentési hiba.";
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo "Hiba: Érvénytelen számformátum.";
    }
    $conn->close();
    exit; // Itt megállunk, nem küldünk JSON-t
}

// --- 2. ÁG: ADATSZOLGÁLTATÁS (Ha nincs paraméter, akkor a táblázatnak küldünk JSON-t) ---
header('Content-Type: application/json');
$sql = "SELECT id, temperature, pressure, created_at FROM measurements ORDER BY id DESC LIMIT 50";
$result = $conn->query($sql);

$data = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>
