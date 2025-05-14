<!DOCTYPE html>
<html lang="en">
<head>
    <title>LOTES</title>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <form method="POST">
        <input type="text" name="Id_Lote" placeholder="ID Lote" required><br>
        <input type="text" name="Tipo" placeholder="Tipo" required><br>
        <input type="text" name="Fecha_Entrada" placeholder="Fexcha Entrada" required><br>
        <input type="text" name="Fecha Caducidad" placeholder="Fecha Caducidad" required><br>
        <input type="text" name="Id_Producto" placeholder="Id Producto" required><br>
        <button type="submit">Registrar Lote</button>
    </form>
</body>
</html>

<?php
include("includes/db.php");

?>
