<?php
session_start();


// Validar sesión y rol
if (!isset($_SESSION['usuario'])) {
    header('Location: ../logins/login.php');
    exit;
}

if ($_SESSION['rol'] !== 'Administrador') {
    header('Location: ../operador/cajero.php');
    exit;
}
include('../includes/db.php');
include('../includes/admin_menu.php');

// Consultas para contar registros
$totalUsuarios     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Usuario"))['total'];
$totalProveedores  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Proveedor"))['total'];
$totalMP           = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM MateriaPrima"))['total'];
$totalMovs         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM MovimientoInventario"))['total'];
$totalProduccion   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Produccion"))['total'];
$totalRecetas      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM ProduccionMateriaPrima"))['total'];
$totalClientes     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Cliente"))['total'];
$totalProductos    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Producto"))['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">🔧 Módulo de Administración</h2>

    <div class="row row-cols-1 row-cols-md-4 g-4">

        <!-- Usuarios -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">👤 Usuarios</h5>
                    <p class="display-6"><?= $totalUsuarios ?></p>
                    <a href="usuarios/usuarios.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Proveedores -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">🚚 Proveedores</h5>
                    <p class="display-6"><?= $totalProveedores ?></p>
                    <a href="proveedores/proveedores.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Materias Primas -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">🧪 Materias Primas</h5>
                    <p class="display-6"><?= $totalMP ?></p>
                    <a href="materia-prima/mp.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Movimiento Inventario -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">📦 Inventario</h5>
                    <p class="display-6"><?= $totalMovs ?></p>
                    <a href="../operador/movimientos/movs.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Producción -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">🏭 Producción</h5>
                    <p class="display-6"><?= $totalProduccion ?></p>
                    <a href="../operador/produccion/produccion.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Recetas -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">📋 Recetas</h5>
                    <p class="display-6"><?= $totalRecetas ?></p>
                    <a href="../operador/produccion/produccion.php" class="btn btn-primary">Ver recetas</a>
                </div>
            </div>
        </div>

        <!-- Clientes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">👥 Clientes</h5>
                    <p class="display-6"><?= $totalClientes ?></p>
                    <a href="../operador/clientes/clientes.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">🧴 Productos</h5>
                    <p class="display-6"><?= $totalProductos ?></p>
                    <a href="productos/productos.php" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>

        <!-- Reportes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">📈 Reportes</h5>
                    <p class="display-6">📊</p>
                    <a href="reportes/reportes.php" class="btn btn-primary">Ver reportes</a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>