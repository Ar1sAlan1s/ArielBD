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

# Variables
$productoID = '';
$tipo = '';
$fechaEntrada = '';
$fechaCaducidad = '';
$cantidad = '';
$errores = [];
$productosDisponibles = [];

# Definir variables con lo del formulario
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $productoID = $_POST['productoID'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $fechaEntrada = $_POST['fechaEntrada'] ?? '';
    $fechaCaducidad = $_POST['fechaCaducidad'] ?? '';
    $cantidad = $_POST['cantidad'] ?? '';

    if ($tipo === 'Materia Prima') {
        $tipo = 'MateriaPrima';
    }

    # Validaciones solo si es POST
    $hoy = date('Y-m-d');
    if (empty($fechaEntrada) || empty($fechaCaducidad)) {
        $errores[] = 'Ambas fechas son obligatorias.';
    } elseif ($fechaEntrada < $hoy) {
        $errores[] = 'La fecha de entrada debe ser igual o posterior a la fecha de hoy.';
    } elseif ($fechaCaducidad <= $fechaEntrada) {
        $errores[] = 'La fecha de caducidad debe ser posterior a la fecha de entrada.';
    }

    if (empty($cantidad)) {
        $errores[] = 'La cantidad es obligatoria.';
    } elseif (!is_numeric($cantidad)) {
        $errores[] = 'La cantidad debe ser un valor numérico.';
    } elseif ($cantidad <= 0) {
        $errores[] = 'La cantidad debe ser mayor a cero.';
    }
}

# Cargar productos disponibles para ambos tipos
function cargarProductos($conn, $tipo) {
    if ($tipo === 'MateriaPrima') {
        $tabla = 'MateriaPrima';
        $idField = 'ID_MateriaPrima';
    } elseif ($tipo === 'Producto') {
        $tabla = 'Producto';
        $idField = 'ID_Producto';
    } else {
        return [];
    }

    $query = "SELECT $idField AS id, Nombre FROM $tabla";
    $result = $conn->query($query);
    $productos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }
    return $productos;
}

$productosProducto = cargarProductos($conn, 'Producto');
$productosMateria = cargarProductos($conn, 'MateriaPrima'); // Cambiado a "MateriaPrima"

# Se registra el lote
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errores)) {
    #Definir valores NULL para el que no se use
    $idProducto = null;
    $idMateria = null;

    if ($tipo === 'Producto') {
        $idProducto = $productoID;
    } elseif ($tipo === 'MateriaPrima') {
        $idMateria = $productoID;
    }

    // Iniciamos transacción para asegurar integridad
    $conn->begin_transaction();
    
    try {
        // 1. Insertar el lote
        $insertStmt = $conn->prepare(
            "INSERT INTO Lote (Tipo, FechaEntrada, FechaCaducidad, ID_Producto, ID_MateriaPrima, Cantidad) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insertStmt->bind_param(
            "sssiii",
            $tipo,
            $fechaEntrada,
            $fechaCaducidad,
            $idProducto,
            $idMateria,
            $cantidad
        );
        
        if (!$insertStmt->execute()) {
            throw new Exception("Error al registrar el lote");
        }
        
        // Obtenemos el ID del lote recién creado
        $id_lote = $conn->insert_id;
        
        // 2. Registrar movimiento de entrada automático usando la fecha del lote
        $insertMovimiento = $conn->prepare(
            "INSERT INTO MovimientoInventario 
             (TipoMovimiento, Fecha, Cantidad, ID_Lote) 
             VALUES ('Entrada', ?, ?, ?)"
        );
        $insertMovimiento->bind_param("sii", $fechaEntrada, $cantidad, $id_lote);
        
        if (!$insertMovimiento->execute()) {
            throw new Exception("Error al registrar movimiento de entrada");
        }
        
        // Si todo fue bien, confirmamos la transacción
        $conn->commit();
        $_SESSION['exito_lote'] = "Lote registrado correctamente con su movimiento de entrada";
        header('Location: ../cajero.php');
        exit;
        
    } catch (Exception $e) {
        // Si hay error, revertimos la transacción
        $conn->rollback();
        $errores[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Lote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function actualizarProductos() {
            const tipo = document.getElementById('tipo').value;
            const selectProductos = document.getElementById('productoID');
            
            // Limpiar opciones actuales
            selectProductos.innerHTML = '<option value="">Selecciona un id</option>';
            
            // Obtener los productos correspondientes según el tipo seleccionado
            const productos = tipo === 'Producto' 
                ? <?php echo json_encode($productosProducto); ?> 
                : <?php echo json_encode($productosMateria); ?>;
            
            // Añadir nuevas opciones
            productos.forEach(producto => {
                const option = document.createElement('option');
                option.value = producto.id;
                option.textContent = `${producto.id} - ${producto.Nombre}`;
                selectProductos.appendChild(option);
            });
        }
    </script>
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Registrar Lote</h2>
        <a href="../cajero.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Volver al menú
        </a>
        <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo" required onchange="actualizarProductos()">
                    <option value="" disabled <?= $tipo === '' ? 'selected' : '' ?>>Selecciona una opción</option>
                    <option value="MateriaPrima" <?= ($tipo === 'MateriaPrima' || $tipo === 'Materia Prima') ? 'selected' : '' ?>>Materia Prima</option>
                    <option value="Producto" <?= $tipo === 'Producto' ? 'selected' : '' ?>>Producto</option>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label for="productoID" class="form-label">ID <?= htmlspecialchars($tipo ?: 'Producto/Materia') ?></label>
                <select class="form-select" id="productoID" name="productoID" required>
                    <option value="">Selecciona un id</option>
                    <?php 
                    $productosMostrar = ($tipo === 'Producto') ? $productosProducto : 
                                      (($tipo === 'Materia Prima') ? $productosMateria : []);
                    foreach ($productosMostrar as $producto): ?>
                    <option value="<?= $producto['id'] ?>" <?= $productoID == $producto['id'] ? 'selected' : '' ?>>
                        <?= $producto['id'] ?> - <?= htmlspecialchars($producto['Nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3 text-start">
                <label for="cantidad" class="form-label">Cantidad</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" required 
                       min="1" value="<?= htmlspecialchars($cantidad) ?>">
            </div>
            
            <div class="mb-3 text-start">
                <label for="fechaEntrada" class="form-label">Fecha de Entrada</label>
                <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" required value="<?= htmlspecialchars($fechaEntrada) ?>">
            </div>
            
            <div class="mb-3 text-start">
                <label for="fechaCaducidad" class="form-label">Fecha de Caducidad</label>
                <input type="date" class="form-control" id="fechaCaducidad" name="fechaCaducidad" required value="<?= htmlspecialchars($fechaCaducidad) ?>">
            </div>
            
            <button type="submit" class="btn btn-success btn-lg w-100">Registrar</button>
            <a href="../cajero.php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>
        </form>
    </div>
</body>
</html>