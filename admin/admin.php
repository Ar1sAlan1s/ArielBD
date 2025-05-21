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

// Consultas para contar registros
$totalUsuarios     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Usuario"))['total'];
$totalProveedores  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Proveedor"))['total'];
$totalMP           = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM MateriaPrima"))['total'];
$totalRecetas      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Receta"))['total'];
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
        <span class="navbar-brand">🔧 Panel de Administración</span>
        <div class="d-flex ms-auto">
            <a href="../logins/logout.php" class="btn btn-outline-light">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center mb-4">Administración del Sistema</h2>

    <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- Usuarios -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-users"></i> Usuarios</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalUsuarios ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="usuarios/usuarios.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Listar
                        </a>
                        <a href="usuarios/usuariosCre.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Crear
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proveedores -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-truck"></i> Proveedores</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalProveedores ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="proveedores/proveedores.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Listar
                        </a>
                        <a href="proveedores/proveedoresCre.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Crear
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Materias Primas -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-flask"></i> Materias Primas</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalMP ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="materia-prima/mp.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Listar
                        </a>
                        <a href="materia-prima/mpCre.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Crear
                        </a>
                        <a href="materia-prima/mpAct.php" class="btn btn-sm btn-warning action-btn">
                            <i class="fas fa-edit"></i> Actualizar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recetas -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-book"></i> Recetas</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalRecetas ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="recetas/recetas.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Listar
                        </a>
                        <a href="recetas/recetasCre.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Crear
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-box-open"></i> Productos</h5>
                </div>
                <div class="card-body">
                    <p class="display-6"><?= $totalProductos ?></p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="productos/productos.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-list"></i> Listar
                        </a>
                        <a href="productos/productosCre.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-plus"></i> Crear
                        </a>
                        <a href="productos/productosAct.php" class="btn btn-sm btn-warning action-btn">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="productos/productosDel.php" class="btn btn-sm btn-danger action-btn">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <!-- Reportes -->
        <div class="col">
            <div class="card text-center shadow h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-bar"></i> Reportes</h5>
                </div>
                <div class="card-body">
                    <p class="display-6">📊</p>
                    <div class="d-flex flex-wrap justify-content-center">
                        <a href="productos/productos.php" class="btn btn-sm btn-primary action-btn">
                            <i class="fas fa-boxes"></i> Inventario
                        </a>
                        <a href="reportes/ventas.php" class="btn btn-sm btn-info action-btn">
                            <i class="fas fa-shopping-cart"></i> Ventas
                        </a>
                        <a href="reportes/produccion.php" class="btn btn-sm btn-success action-btn">
                            <i class="fas fa-industry"></i> Producción
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