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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);

    // Validaciones
    if (empty($nombre)) $errores[] = "El nombre es obligatorio";
    if (empty($telefono)) $errores[] = "El teléfono es obligatorio";
    if (empty($direccion)) $errores[] = "La dirección es obligatoria";

    if (empty($errores)) {
        $stmt = $conn->prepare("INSERT INTO Proveedor (Nombre, Direccion, Telefono) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $direccion, $telefono);
        
        if ($stmt->execute()) {
            header('Location: proveedores.php?success=1');
            exit;
        } else {
            $errores[] = "Error al registrar el proveedor: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Proveedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Nuevo Proveedor</h2>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>

            <div class="mb-3 text-start">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" required>
            </div>

            <div class="mb-3 text-start">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" required>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100">Guardar</button>
            <a href="proveedores.php" class="btn btn-danger btn-lg w-100 mt-2">Cancelar</a>
        </form>
    </div>
</body>
</html>