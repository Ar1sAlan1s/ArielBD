<?php
    session_start();

    if (!isset($_SESSION['usuario'])) { 
        header('Location: login.php');
        exit;
    }

    $rol = $_SESSION['rol'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <h2>¿Estas seguro de cerrar sesión?</h2>
        <form method="POST" class="mt-4">
            <button type="submit" class="btn btn-danger me-2">Sí, estoy seguro.</button>
            <a href="<?= ($rol === 'Administrador') ? '../admin/admin.php' : '../operador/cajero.php' ?>" class="btn btn-secondary">No, volver.</a>
        </form>
    </div>
</body>
</html>

