<?php
session_start();
require_once '../../includes/db.php';

// Limpiar mensajes anteriores de sesión
unset($_SESSION['error_produccion']);
unset($_SESSION['exito_produccion']);

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit;
}

$errores = [];

$sql = "SELECT * FROM Producto";
$result = $conn->query($sql);

if ($result->num_rows === 0){
    $errores[] = "No hay productos registrados en el sistema";
} 

if ($result) {
    $productos = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $errores[] = "Error al obtener los productos. ";
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errores)) {
    $id_producto = $_POST['id_producto'];
    $fecha = $_POST['fecha'];
    $cantidad = $_POST['cantidad'];
    $fecha_caducidad = $_POST['fecha_caducidad'];

    if (empty($id_producto) || empty($fecha) || empty($cantidad) || empty($fecha_caducidad)) {
        $errores[] = "No se puede registrar una producción con campos vacios. ";
    }

     if ($fecha_caducidad <= $fecha) {
        $errores[] = "La fecha de caducidad debe ser posterior a la fecha de producción.";
    }


    $stmt1 = $conn->prepare("SELECT r.*, mp.Nombre as NombreMateriaPrima 
                    FROM Receta r 
                    JOIN MateriaPrima mp ON r.ID_MateriaPrima = mp.ID_MateriaPrima 
                    WHERE r.ID_Producto = ?");
    
    $stmt1->bind_param("i", $id_producto);

    if ($stmt1->execute()) {
        $resultRecetas = $stmt1->get_result();
    } else {
        $errores[] = "Error al obtener las recetas. ";
    }

    if ($resultRecetas->num_rows === 0) {
        $errores[] = "No hay recetas asociadas a el producto seleccionado, no se puede realizar la producción.";
    } else {
        // Obteniendo recetas como array asociativo
        $recetas = $resultRecetas->fetch_all(MYSQLI_ASSOC);

        // Calculando materias primas necesarias
        $materiasPrimasNecesarias = [];
        foreach ($recetas as $receta) {
            $materiasPrimasNecesarias[] = [
                'id_materia_prima' => $receta['ID_MateriaPrima'],
                'nombre' => $receta['NombreMateriaPrima'],
                'cantidad_necesaria' => $receta['CantidadNecesaria'] * $cantidad,
                'cantidad_unitaria' => $receta['CantidadNecesaria']
            ];
        }

        // Lotes para cada materia prima que necesitamos
        $lotesDisponibles = [];
        foreach ($materiasPrimasNecesarias as $mp) {
            $sqlLotes = "SELECT l.* FROM Lote l 
                WHERE l.Tipo = 'MateriaPrima' 
                AND l.ID_MateriaPrima = ? 
                AND l.Cantidad > 0
                ORDER BY l.FechaCaducidad ASC";

            $stmtLotes = $conn->prepare($sqlLotes);
            $stmtLotes->bind_param("i", $mp['id_materia_prima']);
            
            if ($stmtLotes->execute()) {
                $resultLotes = $stmtLotes->get_result();

                if ($resultLotes->num_rows === 0) {
                    $errores[] = "No hay lotes disponibles para la materia prima: ".$mp['nombre'];
                } else {
                    $lotesDisponibles[$mp['id_materia_prima']] = $resultLotes->fetch_all(MYSQLI_ASSOC);
                }
            } else {
                $errores[] = "Error al consultar lotes de: ".$mp['nombre'];
            }
            $stmtLotes->close();
        }

        if (empty($errores)) {
            $distribucionLotes = [];
            $fechaActual = date('Y-m-d');
            
            foreach ($materiasPrimasNecesarias as $mp) {
                $id_mp = $mp['id_materia_prima'];
                $cantidad_necesaria = $mp['cantidad_necesaria'];
                $lotes_mp = $lotesDisponibles[$id_mp];
                $cantidad_asignada = 0;
                $lotes_usados = [];

                foreach ($lotes_mp as $lote) {
                    if ($lote['FechaCaducidad'] < $fechaActual) {
                        $errores[] = "Lote ID {$lote['ID_Lote']} ({$mp['nombre']}) está caducado (caducó el {$lote['FechaCaducidad']}).";

                        //MovimientoInventario tipo perdida (Asignar cantidad de lote a 0 y movimiento de perdida)
                        $cantidad_actual = $lote['Cantidad'];
                        if ($cantidad_actual > 0){
                            $perdidaMovimiento = $conn->prepare("INSERT INTO MovimientoInventario 
                                                (TipoMovimiento, Fecha, Cantidad, ID_Lote) 
                                                VALUES ('Pérdida', ?, ?, ?)");
                            $perdidaMovimiento->bind_param("sdi", $fechaActual, $cantidad_actual, $lote['ID_Lote']);
                            $perdidaMovimiento->execute();

                            $updateLote = $conn->prepare("UPDATE Lote SET Cantidad = Cantidad - ? WHERE ID_Lote = ?");
                            $updateLote->bind_param("di", $cantidad_actual, $lote['ID_Lote']);
                            $updateLote->execute();

                        } 
                        
                        continue;
                    }

                    $cantidad_disponible = $lote['Cantidad'];
                    $cantidad_a_usar = min($cantidad_disponible, $cantidad_necesaria - $cantidad_asignada);

                    if ($cantidad_a_usar > 0) {
                        $lotes_usados[] = [
                            'id_lote' => $lote['ID_Lote'],
                            'cantidad_usar' => $cantidad_a_usar,
                            'cantidad_original' => $cantidad_disponible
                        ];
                        $cantidad_asignada += $cantidad_a_usar;
                    }

                    if ($cantidad_asignada >= $cantidad_necesaria) break;

                }

                if ($cantidad_asignada < $cantidad_necesaria) {
                    $errores[] = "No hay suficiente stock de {$mp['id_materia_prima']}. Necesitas {$cantidad_necesaria}, pero solo hay {$cantidad_asignada} disponible.";
                } else {
                    $distribucionLotes[$id_mp] = [
                        'id_mp' => $mp['id_materia_prima'],
                        'lotes_usados' => $lotes_usados,
                        'cantidad_total_usada' => $cantidad_asignada
                    ];
                }
            }
        }

        //Borrado de la cantidad a los stocks y creacion de movimientos
        if (empty($errores)) {
            // Iniciar transacción para asegurar integridad
            $conn->begin_transaction();
            
            try {
                foreach ($distribucionLotes as $id_mp => $distribucion) {
                    foreach ($distribucion['lotes_usados'] as $lote_usado) {
                        $id_lote = $lote_usado['id_lote'];
                        $cantidad_usar = $lote_usado['cantidad_usar'];
                        
                        // 1. Actualizar lote (restar cantidad)
                        $updateLote = $conn->prepare("UPDATE Lote SET Cantidad = Cantidad - ? WHERE ID_Lote = ?");
                        $updateLote->bind_param("di", $cantidad_usar, $id_lote);
                        $updateLote->execute();
                        
                        // 2. Registrar movimiento de salida
                        $insertMovimiento = $conn->prepare("INSERT INTO MovimientoInventario 
                                                        (TipoMovimiento, Fecha, Cantidad, ID_Lote) 
                                                        VALUES ('Salida', ?, ?, ?)");
                        $insertMovimiento->bind_param("sdi", $fecha, $cantidad_usar, $id_lote);
                        $insertMovimiento->execute();
                    }
                }
                
                // Si todo va bien, confirmar transacción
                $conn->commit();
            } catch (Exception $e) {
                // Si hay error, revertir cambios
                $conn->rollback();
                $errores[] = "Error al procesar la producción: " . $e->getMessage();
                
            }

             try {
                // PASO 7A: Registrar en tabla Produccion
                $insertProduccion = $conn->prepare("INSERT INTO Produccion 
                                                (ID_Producto, FechaProduccion, CantidadProducida) 
                                                VALUES (?, ?, ?)");
                $insertProduccion->bind_param("isd", $id_producto, $fecha, $cantidad);
                $insertProduccion->execute();
                
                // Obtenemos el ID de la producción recién creada
                $id_produccion = $conn->insert_id;

                // PASO 7B: Registrar en ProduccionMateriaPrima (detalle de materias usadas)
                $insertPMP = $conn->prepare("INSERT INTO ProduccionMateriaPrima 
                                        (ID_Produccion, ID_Lote, CantidadUsada) 
                                        VALUES (?, ?, ?)");
                
                foreach ($distribucionLotes as $id_mp => $distribucion) {
                    foreach ($distribucion['lotes_usados'] as $lote_usado) {
                        $insertPMP->bind_param("iid", $id_produccion, $lote_usado['id_lote'], $lote_usado['cantidad_usar']);
                        $insertPMP->execute();
                    }
                }

                // PASO 8: Crear lote del producto producido
                 $insertLoteProducto = $conn->prepare("INSERT INTO Lote 
                                            (Tipo, FechaEntrada, FechaCaducidad, Cantidad, ID_Producto) 
                                            VALUES ('Producto', ?, ?, ?, ?)");
                $insertLoteProducto->bind_param("ssdi", $fecha, $fecha_caducidad, $cantidad, $id_producto);
                $insertLoteProducto->execute();
                $id_lote_producto = $conn->insert_id;

                // PASO 9: Registrar entrada en inventario del producto
                $insertMovEntrada = $conn->prepare("INSERT INTO MovimientoInventario 
                                                (TipoMovimiento, Fecha, Cantidad, ID_Lote) 
                                                VALUES ('Entrada', ?, ?, ?)");
                $insertMovEntrada->bind_param("sdi", $fecha, $cantidad, $id_lote_producto);
                $insertMovEntrada->execute();

                $conn->commit();
                // Obtener nombre del producto
                $stmtNombre = $conn->prepare("SELECT Nombre FROM Producto WHERE ID_Producto = ?");
                $stmtNombre->bind_param("i", $id_producto);
                $stmtNombre->execute();
                $resultNombre = $stmtNombre->get_result();
                $nombre_producto = $resultNombre->fetch_assoc()['Nombre'];
                $stmtNombre->close();

                // Registrar éxito con todos los datos
                $_SESSION['exito_produccion'] = [
                    'id' => $id_produccion,
                    'producto' => $nombre_producto,
                    'cantidad' => $cantidad,
                    'fecha' => $fecha
                ];

                header('Location: produccion.php');
                exit;

            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error_produccion'] = "Error al registrar producción: " . $e->getMessage();
                header('Location: produccionAdd.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar producción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class = "bg-light d-flex justify-content-center align-items-center vh-100">
    <div class = "container text-center">
        <h2 class="mb-4">Nueva producción.</h2>

        <a href="../cajero.php" class="btn btn-primary">Volver al menú</a>

        <?php if (!empty($errores)): ?>
            <div class = "alert alert-danger">
                <ul class = "mb-0">
                    <?php foreach ($errores as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?> 

        <form action="" method="POST">
                <div class="mb-3 text-start">
                <label for="id_producto" class="form-label">Producto</label>
                <select class="form-select" id="id_producto" name="id_producto" required>
                    <option value="">Seleccione un producto</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?= $producto['ID_Producto'] ?>">
                            <?= htmlspecialchars($producto['ID_Producto']) ?> - <?= htmlspecialchars($producto['Nombre']) ?> 
                            (<?= htmlspecialchars($producto['Tipo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3 text-start">
                <label for="fecha" class="form-label">Fecha de producción</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required 
                    value="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3 text-start">
                <label for="fecha_caducidad" class="form-label">Fecha de caducidad</label>
                <input type="date" class="form-control" id="fecha_caducidad" name="fecha_caducidad" required
                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>
            
            <div class="mb-3 text-start">
                <label for="cantidad" class="form-label">Cantidad a producir</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" 
                    required min="1" step="1">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg w-100">Registrar producción</button>
            <a href="produccion.php" class="btn btn-secondary btn-lg w-100 mt-2">Cancelar</a>
        </form>
    </div>
    
</body>
</html>