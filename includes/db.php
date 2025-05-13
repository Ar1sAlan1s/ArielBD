<?php
$host = "bekaiwjfv0jcvy76pdo8-mysql.services.clever-cloud.com";
$dbname = "bekaiwjfv0jcvy76pdo8";
$user = "u4ludh4bz5ls8co5";
$password = "iOQ0fFemJTNM97dhWMzu";
$port = 3306;

$conn = new mysqli($host, $user, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
