<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit;
}

$rol = $_SESSION['rol'];

if ($rol != 'Operador') {
    header('Location: ../../operador/admin.php');
    exit;
}

$resultados = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtroID = isset($_GET['ID_Lote']) ? $conn->real_escape_string($_GET['ID_Lote']) : '';
    $filtroTipo = isset($_GET['tipo']) ? $conn->real_escape_string($_GET['tipo']) : '';
    $filtroFechaEntrada = isset($_GET['fechaEntrada']) ? $conn->real_escape_string($_GET['fechaEntrada']) : '';
    $filtroFechaCaducidad = isset($_GET['fechaCaducidad']) ? $conn->real_escape_string($_GET['fechaCaducidad']) : '';

    $query = "SELECT l.*, 
                     COALESCE(p.Nombre, mp.Nombre) AS NombreElemento
              FROM Lote l
              LEFT JOIN Producto p ON l.ID_Producto = p.ID_Producto
              LEFT JOIN MateriaPrima mp ON l.ID_MateriaPrima = mp.ID_MateriaPrima
              WHERE 1=1";

    if (!empty($filtroID)) {
        $query .= " AND l.ID_Lote LIKE '%$filtroID%'";
    }
    if (!empty($filtroTipo)) {
        $query .= " AND l.Tipo = '$filtroTipo'";
    }
    if (!empty($filtroFechaEntrada)) {
        $query .= " AND l.FechaEntrada = '$filtroFechaEntrada'";
    }
    if (!empty($filtroFechaCaducidad)) {
        $query .= " AND l.FechaCaducidad = '$filtroFechaCaducidad'";
    }

    $res = $conn->query($query);

    if ($res) {
        while ($fila = $res->fetch_assoc()) {
            $resultados[] = $fila;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrar Lotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .badge {
            font-size: 0.9em;
        }
        .badge-producto {
            background-color: #0d6efd;
        }
        .badge-materia {
            background-color: #198754;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Listado de Lotes</h1>
            <a href="../cajero.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al menú
            </a>
        </div>

        <form method="GET" action="">
            <div class="card mb-4 shadow">
                <div class="card-body">
                    <h5 class="card-title mb-3">Filtros de búsqueda</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="ID_Lote" class="form-label">ID Lote</label>
                            <input type="text" class="form-control" id="ID_Lote" name="ID_Lote" placeholder="ID del lote" value="<?= htmlspecialchars($_GET['ID_Lote'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Todos</option>
                                <option value="MateriaPrima" <?= ($_GET['tipo'] ?? '') == 'MateriaPrima' ? 'selected' : '' ?>>Materia Prima</option>
                                <option value="Producto" <?= ($_GET['tipo'] ?? '') == 'Producto' ? 'selected' : '' ?>>Producto</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fechaEntrada" class="form-label">Fecha Entrada</label>
                            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" value="<?= htmlspecialchars($_GET['fechaEntrada'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fechaCaducidad" class="form-label">Fecha Caducidad</label>
                            <input type="date" class="form-control" id="fechaCaducidad" name="fechaCaducidad" value="<?= htmlspecialchars($_GET['fechaCaducidad'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="lotesmostrar.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card shadow">
            <div class="card-body p-0">
                <?php if (!empty($resultados)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Lote</th>
                                    <th>Tipo</th>
                                    <th>Elemento</th>
                                    <th>Fecha Entrada</th>
                                    <th>Fecha Caducidad</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados as $fila): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fila['ID_Lote']) ?></td>
                                        <td>
                                            <?php if ($fila['Tipo'] === 'Producto'): ?>
                                                <span class="badge badge-producto">Producto</span>
                                            <?php else: ?>
                                                <span class="badge badge-materia">Materia Prima</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($fila['NombreElemento'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($fila['FechaEntrada']) ?></td>
                                        <td><?= htmlspecialchars($fila['FechaCaducidad']) ?></td>
                                        <td><?= htmlspecialchars($fila['Cantidad']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
                        <p class="mt-2">No se encontraron lotes con los filtros seleccionados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>