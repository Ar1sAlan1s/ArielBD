<?php
session_start();

// Validación de sesión y rol
if (!isset($_SESSION['usuario'])) {
    header('Location: ../logins/login.php');
    exit;
}
if ($_SESSION['rol'] !== 'Operador') {
    header('Location: ../admin/dashboard.php');
    exit;
}

include('../includes/db.php');
include('../includes/cajero_menu.php');

// Consultas para mostrar cantidades
$totalClientes    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Cliente"))['total'];
$totalVentas      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Venta"))['total'];
$totalLotes       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Lote"))['total'];
$totalProduccion  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Produccion"))['total'];
$totalMovs        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM MovimientoInventario"))['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Cajero</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">🧾 Panel del Cajero</h2>

    <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- Clientes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">👥 Clientes</h5>
                    <p class="display-6"><?= $totalClientes ?></p>
                    <a href="clientes/clientes.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Registrar Venta -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">🛒 Registrar Venta</h5>
                    <p class="display-6"><?= $totalVentas ?></p>
                    <a href="ventas/ventasAdd.php" class="btn btn-success">Registrar</a>
                </div>
            </div>
        </div>

        <!-- Lotes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">📦 Lotes</h5>
                    <p class="display-6"><?= $totalLotes ?></p>
                    <a href="../admin/lotes/lotes.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Registrar Producción -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">🏭 Registrar Producción</h5>
                    <p class="display-6"><?= $totalProduccion ?></p>
                    <a href="produccion/produccionAdd.php" class="btn btn-success">Registrar</a>
                </div>
            </div>
        </div>

        <!-- Movimiento de Inventario -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">🔁 Movimientos</h5>
                    <p class="display-6"><?= $totalMovs ?></p>
                    <a href="movimientos/movs.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Reportes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">📈 Reportes</h5>
                    <p class="display-6">📊</p>
                    <a href="../admin/reportes/reportes.php" class="btn btn-primary">Ver reportes</a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>