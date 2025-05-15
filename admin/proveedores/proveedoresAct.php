<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit;
}

$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}

$errores = [];
$proveedor = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM Proveedor WHERE ID_Proveedor = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $proveedor = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = !empty($_POST['nombre']) ? trim($_POST['nombre']) : $proveedor['Nombre'];
    $direccion = !empty($_POST['direccion']) ? trim($_POST['direccion']) : $proveedor['Direccion'];
    $telefono = !empty($_POST['telefono']) ? trim($_POST['telefono']) : $proveedor['Telefono'];

    // Validaciones
    if (empty($nombre) && empty($direccion) && empty($telefono)) {
        $errores[] = "Debe modificar al menos un campo";
    }

    if(empty($errores)) {
        $stmt = $conn->prepare("UPDATE Proveedor SET Nombre = ?, Direccion = ?, Telefono = ? WHERE ID_Proveedor = ?");
        $stmt->bind_param("sssi", $nombre, $direccion, $telefono, $id);

        if ($stmt->execute()) {
            header('Location: proveedores.php?success=1');
            exit;
        } else {
            $errores[] = "Error al actualizar: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Editar Proveedor</h2>

        <?php if(!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?= $proveedor['ID_Proveedor'] ?? '' ?>">

            <div class="mb-3 text-start">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" 
                    value="<?= htmlspecialchars($proveedor['Nombre'] ?? '') ?>"
                    placeholder="Dejar vacío para mantener el nombre actual">
            </div>
            
            <div class="mb-3 text-start">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" 
                    value="<?= htmlspecialchars($proveedor['Direccion'] ?? '') ?>"
                    placeholder="Dejar vacío para mantener la dirección actual">
            </div>

            <div class="mb-3 text-start">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" 
                    value="<?= htmlspecialchars($proveedor['Telefono'] ?? '') ?>"
                    placeholder="Dejar vacío para mantener el teléfono actual">
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Actualizar</button>
            <a href="proveedores.php" class="btn btn-danger btn-lg w-100 mt-2">Cancelar</a>
        </form>
    </div>
</body>
</html>