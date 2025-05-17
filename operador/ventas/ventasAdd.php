<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit();
}

$rol = $_SESSION['rol'];
$usuarioID = $_SESSION['usuario'];


if ($rol != 'Operador') {
    header('Location: ../../admin/admin.php');
    exit();
}

# Variables
$errores = [];
$clientesDisponibles = [];
$productosDisponibles = [];

# Obtener clientes
$result = $conn->query("SELECT ID_Cliente, Nombre FROM Cliente");
if ($result) $clientesDisponibles = $result->fetch_all(MYSQLI_ASSOC);

# Obtener productos
$result = $conn->query("SELECT ID_Producto, Nombre, Presentacion FROM Producto");
if ($result) $productosDisponibles = $result->fetch_all(MYSQLI_ASSOC);

# Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clienteID = $_POST['clienteID'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $productoID = $_POST['productoID'] ?? '';
    $cantidad = (float)($_POST['cantidad'] ?? 0);
    $precioUnitario = (float)($_POST['precio'] ?? 0);

    # Validaciones básicas
    if (empty($clienteID) || empty($fecha) || empty($productoID) || $cantidad <= 0 || $precioUnitario <= 0) {
        $errores[] = "Todos los campos son obligatorios y deben ser válidos";
    }

    # Validar fecha
    if ($fecha < date('Y-m-d')) {
        $errores[] = "La fecha no puede ser anterior al día actual";
    }

    # Obtener lotes válidos
    $lotes = [];
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT ID_Lote, Cantidad, FechaCaducidad 
                              FROM Lote 
                              WHERE ID_Producto = ? 
                              AND Tipo = 'Producto'
                              AND Cantidad > 0
                              ORDER BY FechaCaducidad ASC");
        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $lotes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($lotes)) {
            $errores[] = "No hay stock disponible para este producto";
        }
    }

    # Validar cantidades y caducidades
        $totalDisponible = 0;
        $lotesValidos = [];
        foreach ($lotes as $lote) {
            if ($lote['FechaCaducidad'] < date('Y-m-d')) {
                $errores[] = "El lote {$lote['ID_Lote']} está caducado";
                continue;
            }
            $totalDisponible += $lote['Cantidad'];
            $lotesValidos[] = $lote;
        }

 

    if ($totalDisponible < $cantidad && empty($errores)) {
        $errores[] = "Stock insuficiente. Disponible: $totalDisponible";
    }

    # Procesar venta si no hay errores
    if (empty($errores)) {
        $conn->begin_transaction();
        try {
            # 1. Registrar venta principal
            $stmtVenta = $conn->prepare("INSERT INTO Venta (Fecha, ID_Usuario, ID_Cliente) VALUES (?, ?, ?)");
            $stmtVenta->bind_param("sii", $fecha, $usuarioID, $clienteID);
            $stmtVenta->execute();
            $ventaID = $conn->insert_id;

            # 2. Distribuir cantidad en lotes
            $restante = $cantidad;
            foreach ($lotesValidos as $lote) {
                if ($restante <= 0) break;
                
                $usar = min($lote['Cantidad'], $restante);
                
                # Actualizar lote
                $stmtLote = $conn->prepare("UPDATE Lote SET Cantidad = Cantidad - ? WHERE ID_Lote = ?");
                $stmtLote->bind_param("di", $usar, $lote['ID_Lote']);
                $stmtLote->execute();
                
                # Registrar detalle
                $stmtDetalle = $conn->prepare("INSERT INTO DetalleVenta (ID_Venta, ID_Lote, ID_Producto, Cantidad, PrecioUnitario) VALUES (?, ?, ?, ?, ?)");
                $stmtDetalle->bind_param("iiidd", $ventaID, $lote['ID_Lote'], $productoID, $usar, $precioUnitario);
                $stmtDetalle->execute();
                
                # Registrar movimiento
                $stmtMov = $conn->prepare("INSERT INTO MovimientoInventario (TipoMovimiento, Fecha, Cantidad, ID_Lote) VALUES ('Salida', ?, ?, ?)");
                $stmtMov->bind_param("sdi", $fecha, $usar, $lote['ID_Lote']);
                $stmtMov->execute();
                
                $restante -= $usar;
            }

            $conn->commit();
            $_SESSION['exito_venta'] = "Venta registrada correctamente";
            header('Location: ../ventas/ventas.php');
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errores[] = "Error al procesar la venta: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Registrar Venta</h2>
        <a href="../cajero.php" class="btn btn-primary position-absolute top-0 start-0 m-3">Volver al menú</a>

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
            <div class="mb-3 row">
                <div class="col-md-10 text-start">
                    <label for="clienteID" class="form-label">Cliente</label>
                    <select class="form-select" id="clienteID" name="clienteID" required>
                        <option value="">Seleccionar cliente</option>
                        <?php foreach ($clientesDisponibles as $cliente): ?>
                            <option value="<?= $cliente['ID_Cliente'] ?>" <?= ($clienteID ?? '') == $cliente['ID_Cliente'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="../clientes/clientesCre.php" class="btn btn-primary w-100">Nuevo Cliente</a>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label for="fecha" class="form-label">Fecha de venta</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required 
                       value="<?= htmlspecialchars($fecha ?? date('Y-m-d')) ?>">
            </div>

            <div class="mb-3 text-start">
                <label for="productoID" class="form-label">Producto</label>
                <select class="form-select" id="productoID" name="productoID" required>
                    <option value="">Seleccionar producto</option>
                    <?php foreach ($productosDisponibles as $producto): ?>
                        <option value="<?= $producto['ID_Producto'] ?>" <?= ($productoID ?? '') == $producto['ID_Producto'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($producto['Nombre']) ?> (<?= htmlspecialchars($producto['Presentacion']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label for="cantidad" class="form-label">Cantidad</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" 
                       required min="0.01" step="0.01" value="<?= htmlspecialchars($cantidad ?? '') ?>">
            </div>

            <div class="mb-3 text-start">
                <label for="precio" class="form-label">Precio Unitario</label>
                <input type="number" class="form-control" id="precio" name="precio" 
                       required min="0.01" step="0.01" value="<?= htmlspecialchars($precioUnitario ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100">Registrar Venta</button>
        </form>
    </div>
</body>
</html>