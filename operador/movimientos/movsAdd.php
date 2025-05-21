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

$errores = [];
$lotesDisponibles = [];

// Consulta para obtener lotes disponibles con su información relacionada
$sqlLotes = "SELECT l.ID_Lote, l.Tipo, l.Cantidad, 
                    COALESCE(p.Nombre, mp.Nombre) as NombreElemento,
                    CASE 
                        WHEN l.Tipo = 'Producto' THEN 'Producto'
                        WHEN l.Tipo = 'MateriaPrima' THEN 'Materia Prima'
                    END as TipoElemento
             FROM Lote l
             LEFT JOIN Producto p ON l.ID_Producto = p.ID_Producto
             LEFT JOIN MateriaPrima mp ON l.ID_MateriaPrima = mp.ID_MateriaPrima
             ORDER BY l.Tipo, l.FechaCaducidad";

$resultLotes = $conn->query($sqlLotes);
if ($resultLotes) {
    $lotesDisponibles = $resultLotes->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipoMovimiento = $_POST['tipoMovimiento'] ?? '';
    $id_lote = $_POST['id_lote'] ?? '';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $cantidad = $_POST['cantidad'] ?? 0; // Nuevo campo para cantidad de salida

    // Validaciones básicas
    if (empty($tipoMovimiento) || empty($id_lote)) {
        $errores[] = "Todos los campos obligatorios deben ser completados";
    }

    // Obtener información del lote seleccionado
    $cantidad_lote = 0;
    if (empty($errores)) {
        $sqlLoteInfo = "SELECT Cantidad FROM Lote WHERE ID_Lote = ?";
        $stmtLoteInfo = $conn->prepare($sqlLoteInfo);
        $stmtLoteInfo->bind_param("i", $id_lote);
        $stmtLoteInfo->execute();
        $loteInfo = $stmtLoteInfo->get_result()->fetch_assoc();
        $cantidad_lote = $loteInfo['Cantidad'];
        
        // Validaciones específicas por tipo de movimiento
        if ($tipoMovimiento == 'Entrada') {
            // Para entradas, usamos la cantidad total del lote
            $cantidad = $cantidad_lote;
        } elseif ($tipoMovimiento == 'Salida') {
            // Validar cantidad para salida
            if (!is_numeric($cantidad) || $cantidad <= 0) {
                $errores[] = "La cantidad debe ser un número positivo";
            } elseif ($cantidad > $cantidad_lote) {
                $errores[] = "No hay suficiente cantidad en el lote (Disponible: $cantidad_lote)";
            }
        } elseif ($tipoMovimiento == 'Pérdida') {
            // Para pérdidas, usamos la cantidad total del lote
            $cantidad = $cantidad_lote;
        }
    }

    if (empty($errores)) {
        $conn->begin_transaction();
        
        try {
            // 1. Registrar el movimiento
            $insertMovimiento = $conn->prepare("INSERT INTO MovimientoInventario 
                                              (TipoMovimiento, Fecha, Cantidad, ID_Lote) 
                                              VALUES (?, ?, ?, ?)");
            $insertMovimiento->bind_param("ssdi", $tipoMovimiento, $fecha, $cantidad, $id_lote);
            $insertMovimiento->execute();

            // 2. Actualizar el lote según el tipo de movimiento
            if ($tipoMovimiento == 'Entrada') {
                // No hacemos cambios para entradas (la cantidad ya está en el lote)
            } elseif ($tipoMovimiento == 'Salida') {
                // Para salidas, restamos la cantidad especificada
                $updateLote = $conn->prepare("UPDATE Lote SET Cantidad = Cantidad - ? WHERE ID_Lote = ?");
                $updateLote->bind_param("di", $cantidad, $id_lote);
                $updateLote->execute();
            } elseif ($tipoMovimiento == 'Pérdida') {
                // Para pérdidas, establecemos la cantidad a 0
                $updateLote = $conn->prepare("UPDATE Lote SET Cantidad = 0 WHERE ID_Lote = ?");
                $updateLote->bind_param("i", $id_lote);
                $updateLote->execute();
            }

            $conn->commit();
            $_SESSION['exito_movimiento'] = "Movimiento registrado correctamente";
            header('Location: movimientos.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $errores[] = "Error al registrar el movimiento: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Movimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card {
            max-width: 800px;
            margin: 0 auto;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .cantidad-container {
            display: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Registrar Movimiento</h1>
            <a href="../cajero.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al menú
            </a>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="info-box">
                    <h5><i class="bi bi-info-circle"></i> Instrucciones</h5>
                    <p class="mb-0">
                        - <strong>Entrada</strong>: Registra la entrada completa de un lote (usa la cantidad total del lote)<br>
                        - <strong>Salida</strong>: Registra salida parcial/full (especifica cantidad a descontar)<br>
                        - <strong>Pérdida</strong>: Registra la pérdida completa del lote (cantidad se establecerá a 0)
                    </p>
                </div>

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipoMovimiento" class="form-label required-field">Tipo de Movimiento</label>
                            <select class="form-select" id="tipoMovimiento" name="tipoMovimiento" required>
                                <option value="">Seleccionar...</option>
                                <option value="Entrada" <?= ($_POST['tipoMovimiento'] ?? '') == 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                                <option value="Salida" <?= ($_POST['tipoMovimiento'] ?? '') == 'Salida' ? 'selected' : '' ?>>Salida</option>
                                <option value="Pérdida" <?= ($_POST['tipoMovimiento'] ?? '') == 'Pérdida' ? 'selected' : '' ?>>Pérdida</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" 
                                   value="<?= htmlspecialchars($_POST['fecha'] ?? date('Y-m-d')) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="id_lote" class="form-label required-field">Lote</label>
                        <select class="form-select" id="id_lote" name="id_lote" required>
                            <option value="">Seleccionar lote...</option>
                            <?php foreach ($lotesDisponibles as $lote): ?>
                                <option value="<?= $lote['ID_Lote'] ?>" 
                                    data-cantidad="<?= $lote['Cantidad'] ?>"
                                    <?= ($_POST['id_lote'] ?? '') == $lote['ID_Lote'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars("Lote #{$lote['ID_Lote']} - {$lote['TipoElemento']}: {$lote['NombreElemento']} (Disponible: {$lote['Cantidad']})") ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 cantidad-container" id="cantidadSalidaContainer">
                        <label for="cantidad" class="form-label required-field">Cantidad a Salida</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" 
                               min="0.01" step="0.01" 
                               value="<?= htmlspecialchars($_POST['cantidad'] ?? '') ?>">
                        <small class="text-muted">Cantidad disponible: <span id="cantidadDisponible">0</span></small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Registrar Movimiento
                        </button>
                        <a href="movimientos.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar campo de cantidad según tipo de movimiento
        document.getElementById('tipoMovimiento').addEventListener('change', function() {
            const tipoMovimiento = this.value;
            const cantidadContainer = document.getElementById('cantidadSalidaContainer');
            
            if (tipoMovimiento === 'Salida') {
                cantidadContainer.style.display = 'block';
                actualizarCantidadDisponible();
            } else {
                cantidadContainer.style.display = 'none';
            }
        });

        // Actualizar cantidad disponible cuando se selecciona un lote
        document.getElementById('id_lote').addEventListener('change', function() {
            if (document.getElementById('tipoMovimiento').value === 'Salida') {
                actualizarCantidadDisponible();
            }
        });

        function actualizarCantidadDisponible() {
            const selectLote = document.getElementById('id_lote');
            const selectedOption = selectLote.options[selectLote.selectedIndex];
            const cantidadDisponibleSpan = document.getElementById('cantidadDisponible');
            
            if (selectedOption && selectedOption.value !== '') {
                const cantidad = parseFloat(selectedOption.getAttribute('data-cantidad'));
                cantidadDisponibleSpan.textContent = cantidad;
                
                // Establecer el máximo permitido para el input
                document.getElementById('cantidad').max = cantidad;
            } else {
                cantidadDisponibleSpan.textContent = '0';
            }
        }

        // Inicializar visibilidad del campo cantidad
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('tipoMovimiento').value === 'Salida') {
                document.getElementById('cantidadSalidaContainer').style.display = 'block';
                actualizarCantidadDisponible();
            }
        });
    </script>
</body>
</html>