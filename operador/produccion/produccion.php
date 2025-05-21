<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit;
}

$rol = $_SESSION['rol'];

if ($rol != 'Operador') {
    header('Location: ../../admin/admin.php');
    exit;
}

// Manejo de mensajes de sesión
$mensaje_exito = '';
$mensaje_error = '';

// Modifica el manejo de mensajes para ser más flexible:
if (isset($_SESSION['exito_produccion'])) {
    if (is_array($_SESSION['exito_produccion'])) {
        // Mensaje detallado (nuevo formato)
        $mensaje_exito = "Producción #".$_SESSION['exito_produccion']['id']." registrada: ".
                         $_SESSION['exito_produccion']['cantidad']." unidades de ".
                         $_SESSION['exito_produccion']['producto']." (".$_SESSION['exito_produccion']['fecha'].")";
    } else {
        // Mensaje simple (formato antiguo)
        $mensaje_exito = $_SESSION['exito_produccion'];
    }
    unset($_SESSION['exito_produccion']);
}

if (isset($_SESSION['error_produccion'])) {
    $mensaje_error = $_SESSION['error_produccion'];
    unset($_SESSION['error_produccion']);
}

// Consulta para obtener las producciones con información del producto
$sql = "SELECT p.*, pr.Nombre as NombreProducto, pr.Tipo as TipoProducto
        FROM Produccion p
        JOIN Producto pr ON p.ID_Producto = pr.ID_Producto
        ORDER BY p.FechaProduccion DESC";

$producciones = [];
if ($result = $conn->query($sql)) {
    $producciones = $result->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener los detalles de materia prima usada en una producción
function obtenerDetallesProduccion($conn, $id_produccion) {
    $sql = "SELECT pmp.*, mp.Nombre as NombreMateriaPrima, l.ID_Lote
            FROM ProduccionMateriaPrima pmp
            JOIN Lote l ON pmp.ID_Lote = l.ID_Lote
            JOIN MateriaPrima mp ON l.ID_MateriaPrima = mp.ID_MateriaPrima
            WHERE pmp.ID_Produccion = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_produccion);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Producción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
        }
        .badge {
            font-size: 0.85em;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Historial de Producción</h1>
            
            <a href="../cajero.php" class="btn btn-primary">Volver al menú</a>
            <a href="produccionAdd.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nueva Producción
            </a>
        </div>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $mensaje_exito ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $mensaje_error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($producciones)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay producciones registradas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($producciones as $produccion): ?>
                                    <?php 
                                    $detalles = obtenerDetallesProduccion($conn, $produccion['ID_Produccion']);
                                    $total_materias = count($detalles);
                                    ?>
                                    <tr>
                                        <td><?= $produccion['ID_Produccion'] ?></td>
                                        <td><?= htmlspecialchars($produccion['NombreProducto']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($produccion['TipoProducto']) ?>
                                            </span>
                                        </td>
                                        <td><?= $produccion['CantidadProducida'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($produccion['FechaProduccion'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#detalle-<?= $produccion['ID_Produccion'] ?>">
                                                <i class="bi bi-info-circle"></i> Detalles
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="6" class="p-0 border-0">
                                            <div class="collapse" id="detalle-<?= $produccion['ID_Produccion'] ?>">
                                                <div class="p-3">
                                                    <h6 class="mb-3">Materias Primas Utilizadas:</h6>
                                                    <?php if ($total_materias > 0): ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Materia Prima</th>
                                                                        <th>Lote</th>
                                                                        <th>Cantidad Usada</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($detalles as $detalle): ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($detalle['NombreMateriaPrima']) ?></td>
                                                                            <td>Lote #<?= $detalle['ID_Lote'] ?></td>
                                                                            <td><?= $detalle['CantidadUsada'] ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-warning mb-0">
                                                            No se registraron materias primas para esta producción
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
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