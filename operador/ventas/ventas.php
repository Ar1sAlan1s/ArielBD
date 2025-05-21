<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit;
}

$resultados = [];
$detallesVentas = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtroIDV = isset($_GET['ID_Venta']) ? $conn->real_escape_string($_GET['ID_Venta']) : '';
    $filtroIDU = isset($_GET['ID_Usuario']) ? $conn->real_escape_string($_GET['ID_Usuario']) : '';
    $filtroIDC = isset($_GET['ID_Cliente']) ? $conn->real_escape_string($_GET['ID_Cliente']) : '';
    $filtroFecha = isset($_GET['fecha']) ? $conn->real_escape_string($_GET['fecha']) : '';

    $query = "SELECT v.*, u.Nombre as NombreUsuario, c.Nombre as NombreCliente 
              FROM Venta v
              LEFT JOIN Usuario u ON v.ID_Usuario = u.ID_Usuario
              LEFT JOIN Cliente c ON v.ID_Cliente = c.ID_Cliente";

    $conditions = [];
    if (!empty($filtroIDV)) {
        $conditions[] = "v.ID_Venta LIKE '%$filtroIDV%'";
    }
    if (!empty($filtroIDU)) {
        $conditions[] = "v.ID_Usuario LIKE '%$filtroIDU%'";
    }
    if (!empty($filtroIDC)) {
        $conditions[] = "v.ID_Cliente LIKE '%$filtroIDC%'";
    }
    if (!empty($filtroFecha)) {
        $conditions[] = "v.Fecha = '$filtroFecha'";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $res = $conn->query($query);

    if ($res) {
        while ($fila = $res->fetch_assoc()) {
            $resultados[] = $fila;
            
            // Obtener detalles de cada venta
            $id_venta = $fila['ID_Venta'];
            $query_detalles = "SELECT dv.*, p.Nombre as NombreProducto, l.ID_Lote
                               FROM DetalleVenta dv
                               JOIN Producto p ON dv.ID_Producto = p.ID_Producto
                               JOIN Lote l ON dv.ID_Lote = l.ID_Lote
                               WHERE dv.ID_Venta = $id_venta";
            
            $res_detalles = $conn->query($query_detalles);
            $detallesVentas[$id_venta] = $res_detalles->fetch_all(MYSQLI_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mostrar Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .details-row {
            background-color: #f8f9fa;
        }
        .product-row td {
            border-top: none !important;
            padding-left: 50px;
        }
    </style>
</head>
<body>
    <form method="GET" action="">
        <div class="container text-center">
            <h2>Mostrar Ventas</h2>
        </div>
        <div class="container mb-3">
            <div class="input-group mb-3">
                <span class="input-group-text">ID Venta</span>
                <input type="text" class="form-control" name="ID_Venta" placeholder="Introduzca el ID">
                
                <span class="input-group-text">Fecha</span>
                <input type="date" class="form-control" name="fecha">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">ID Usuario</span>
                <input type="text" class="form-control" name="ID_Usuario" placeholder="Introduzca el ID">

                <span class="input-group-text">ID Cliente</span>
                <input type="text" class="form-control" name="ID_Cliente" placeholder="Introduzca el ID">
            </div>

            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <div class="container">
        <?php if (!empty($resultados)): ?>
            <h4>Resultados encontrados:</h4>
            <table class="table table-bordered mt-2">
                <thead class="table-dark">
                    <tr>
                        <th>ID Venta</th>
                        <th>Usuario</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $venta): ?>
                        <tr>
                            <td><?= htmlspecialchars($venta['ID_Venta']) ?></td>
                            <td><?= htmlspecialchars($venta['NombreUsuario'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($venta['NombreCliente'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($venta['Fecha']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info toggle-details" 
                                        data-venta="<?= $venta['ID_Venta'] ?>">
                                    Ver Detalles
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Fila de detalles (inicialmente oculta) -->
                        <tr class="details-row" id="details-<?= $venta['ID_Venta'] ?>" style="display: none;">
                            <td colspan="5">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Lote</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unitario</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($detallesVentas[$venta['ID_Venta']])): ?>
                                            <?php foreach ($detallesVentas[$venta['ID_Venta']] as $detalle): ?>
                                                <tr class="product-row">
                                                    <td><?= htmlspecialchars($detalle['NombreProducto']) ?></td>
                                                    <td><?= htmlspecialchars($detalle['ID_Lote']) ?></td>
                                                    <td><?= htmlspecialchars($detalle['Cantidad']) ?></td>
                                                    <td>$<?= number_format($detalle['PrecioUnitario'], 2) ?></td>
                                                    <td>$<?= number_format($detalle['Cantidad'] * $detalle['PrecioUnitario'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr class="product-row">
                                                <td colspan="5">No hay productos en esta venta</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
            <p class="alert alert-warning">No se encontraron resultados.</p>
        <?php endif; ?>

        <a href="../cajero.php" class="btn btn-danger w-100 mt-3">Volver</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar detalles al hacer clic en el botón
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function() {
                const ventaId = this.getAttribute('data-venta');
                const detailsRow = document.getElementById(`details-${ventaId}`);
                
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = 'table-row';
                    this.textContent = 'Ocultar Detalles';
                } else {
                    detailsRow.style.display = 'none';
                    this.textContent = 'Ver Detalles';
                }
            });
        });
    </script>
</body>
</html>