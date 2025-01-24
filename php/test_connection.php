<?php
$servername = "srv1603.hstgr.io";
$username = "u469674049_Admin";
$password = "-YeshuaSalvador316";
$database = "u469674049_p2";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
echo "Conexión exitosa a la base de datos.";
$conn->close();
?>
