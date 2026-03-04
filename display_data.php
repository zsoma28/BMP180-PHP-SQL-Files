<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "BMP-180";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

$sql = "SELECT id, temperature, pressure, created_at 
        FROM measurements 
        ORDER BY created_at DESC 
        LIMIT 50";   // utolsó 50 mérés

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>BMP180 – Hőmérséklet és Légnyomás adatok</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even){background-color: #f2f2f2;}
    </style>
</head>
<body>

<h2>BMP180 szenzor adatok (utolsó 50 mérés)</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Hőmérséklet (°C)</th>
        <th>Légnyomás (hPa)</th>
        <th>Időpont</th>
    </tr>

<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . number_format($row["temperature"], 2) . "</td>";
        echo "<td>" . number_format($row["pressure"],    2) . "</td>";
        echo "<td>" . $row["created_at"] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>Nincsenek adatok.</td></tr>";
}
$conn->close();
?>

</table>

</body>
</html>
