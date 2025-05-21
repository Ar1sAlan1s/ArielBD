<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit;
}

// Consulta para obtener todos los movimientos de inventario con información relacionada
$sql = "SELECT mi.*, 
               p.Nombre as NombreProducto,
               mp.Nombre as NombreMateriaPrima,
               l.Tipo as TipoLote
        FROM MovimientoInventario mi
        LEFT JOIN Lote l ON mi.ID_Lote = l.ID_Lote
        LEFT JOIN Producto p ON l.ID_Producto = p.ID_Producto
        LEFT JOIN MateriaPrima mp ON l.ID_MateriaPrima = mp.ID_MateriaPrima
        ORDER BY mi.Fecha DESC, mi.ID_Movimiento DESC";

$movimientos = [];
if ($result = $conn->query($sql)) {
    $movimientos = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .badge-entrada {
            background-color: #28a745;
        }
        .badge-salida {
            background-color: #dc3545;
        }
        .badge-perdida {
            background-color: #6c757d;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Movimientos de Inventario</h1>
            <div>
                <a href="../cajero.php" class="btn btn-primary me-2">
                    <i class="bi bi-arrow-left"></i> Volver al menú
                </a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Elemento</th>
                                <th>Cantidad</th>
                                <th>Lote</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay movimientos registrados</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $movimiento): ?>
                                    <tr>
                                        <td><?= $movimiento['ID_Movimiento'] ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = '';
                                            if ($movimiento['TipoMovimiento'] == 'Entrada') {
                                                $badge_class = 'badge-entrada';
                                            } elseif ($movimiento['TipoMovimiento'] == 'Salida') {
                                                $badge_class = 'badge-salida';
                                            } else {
                                                $badge_class = 'badge-perdida';
                                            }
                                            ?>
                                            <span class="badge <?= $badge_class ?>">
                                                <?= $movimiento['TipoMovimiento'] ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($movimiento['Fecha'])) ?></td>
                                        <td>
                                            <?php 
                                            if ($movimiento['TipoLote'] == 'Producto') {
                                                echo htmlspecialchars($movimiento['NombreProducto'] ?? 'N/A');
                                            } else {
                                                echo htmlspecialchars($movimiento['NombreMateriaPrima'] ?? 'N/A');
                                            }
                                            ?>
                                        </td>
                                        <td><?= $movimiento['Cantidad'] ?></td>
                                        <td>Lote #<?= $movimiento['ID_Lote'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>