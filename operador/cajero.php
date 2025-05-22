<?php
session_start();

// Validación de sesión y rol
if (!isset($_SESSION['usuario'])) {
    header('Location: ../logins/login.php');
    exit;
}
if ($_SESSION['rol'] !== 'Operador') {
    header('Location: ../admin/admin.php');
    exit;
}

include('../includes/db.php');

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
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .action-btn {
            margin: 2px;
            font-size: 0.8rem;
        }
        .navbar-brand {
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">🧾 Panel del Cajero</span>
        <div class="d-flex ms-auto">
            <a href="../logins/logout.php" class="btn btn-outline-light">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- Clientes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-users"></i> Clientes</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalClientes ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="clientes/clientes.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Listar
                        </a>
                        <a href="clientes/clientesCre.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Crear
                        </a>
                        <a href="clientes/clientesDel.php" class="btn btn-sm btn-danger action-btn">
                            <i class="fas fa-minus"></i> Eliminar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-cash-register"></i> Ventas</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalVentas ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="ventas/ventas.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Historial
                        </a>
                        <a href="ventas/ventasAdd.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Nueva Venta
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <!-- Lotes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-pallet"></i> Lotes</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalLotes ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="lotes/lotesMost.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Listar
                        </a>
                        <a href="lotes/lotesCre.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Nuevo Lote
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Producción -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-industry"></i> Producción</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalProduccion ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="produccion/produccion.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Historial
                        </a>
                        <a href="produccion/produccionAdd.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Registrar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movimientos de Inventario -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-exchange-alt"></i> Movimientos</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalMovs ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="movimientos/movs.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Historial
                        </a>
                        <a href="movimientos/movsAdd.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Registrar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>