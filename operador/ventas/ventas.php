<?php
require_once '../../includes/db.php';


    if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit;
    }

    $rol = $_SESSION['rol'];

    if ($rol!= 'Operador'){
        header('Location: ../../admin/admin.php')
        exit;
    }


    # Variables
    $usuarioID = '';
    $clienteID = '';
    $fecha = '';
    $loteID = '';
    $cantidad = '';
    $precioUnitario = '';
    $errores = [];
    

    # Definir variables con lo del formulario
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $usuarioID = $_SESSION['usuario'] ?? '';
        $clienteID = $_POST['productoID'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $loteID = $_POST['loteID'] ?? '';
        $cantidad = $_POST['cantidad'] ?? '';
        $precioUnitario = $_POST['precio'] ?? '';
        $cantidadMax = $_POST['cantidadMax'] ?? '';

        $errores[] = "Cantidad limite: ".$cantidadMax.". Cantidad pedida: ".$cantidad;

        # Verificar que las fechas sean mas adelante de la que se hace el registro
        $hoy = date('Y-m-d');
        if (empty($fecha)) {
            $errores[] = 'La fecha es obligatoria.';
        } elseif ($fecha < $hoy) {
            $errores[] = 'La fecha debe ser igual o posterior a la fecha de hoy.';
        }

        # Verificar que la cantidad del lote no sea mayor a la solicitada
        if($cantidadMax > $cantidad){
            $errores[] = 'La cantidad sobrepasa el limite disponible.';
        }
        

        # Se registra la venta
        if (empty($errores)) {
            # Definir valores NULL para el que no se use
            $ventaID = NULL;
            $detalleID = NULL;

            $insertStmt = $conn->prepare(
                "INSERT INTO Venta (Fecha, ID_Usuario, ID_Cliente) VALUES (?, ?, ?, ?)"
            );
            $insertStmt->bind_param(
                "sii",
                $ventaID,
                $fecha,
                $usuarioID,
                $idMateria
            );

            if ($insertStmt->execute()) {
                // Obtener el ID_Venta generado automáticamente
                $ventaIDGenerado = $conn->insert_id;

                $insertDetalle = $conn->prepare(
                    "INSERT INTO DetalleVenta (ID_Venta, ID_Lote, Cantidad, PrecioUnitario) VALUES (?, ?, ?, ?)"
                );
                $insertDetalle->bind_param(
                    "iiid", 
                    $ventaIDGenerado,
                    $loteID,
                    $cantidad,
                    $PrecioUnitario
                );
                $insertDetalle->execute();
                $insertDetalle->close();
                if ($insertStmt->execute()) {
                    header('Location: ..\ventas\ventas.php?status=success');
                    exit;
                } else {
                    $errores[] = 'Error al registrar el lote. Inténtalo de nuevo.';
                }
                $insertStmt->close();
            } else {
                $errores[] = 'Error al registrar la venta. Inténtalo de nuevo.';
            }
            $insertStmt->close();
        }


    }


    # Obtener IDs de Clientes
    $query = "SELECT ID_Cliente, Nombre FROM Cliente";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $clientesDisponibles[] = $row;
        }
    }

    # Obtener IDs de Lotes
    $query = "SELECT L.ID_Lote, P.Nombre, L.FechaCaducidad, L.Cantidad  FROM Lote L JOIN Producto P ON L.ID_Producto = P.ID_Producto WHERE L.Tipo = 'Producto' AND L.FechaCaducidad > CURDATE();";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $lotesDisponibles[] = $row;
        }
    }

?>


<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resgistrar Venta</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center">
            <h2 class="mb-4">Registrar Venta</h2>
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

        <a href="..\cajero.php" class="btn btn-primary position-absolute top-0 start-0 m-3">Volver al menú</a>


        <div class="mb-3 row">
                <div class="col-md-10 text-start">
                    <label for="clienteID" class="form-label">ID Cliente</label>
                    <select class="form-select" id="clienteID" name="clienteID" required>
                        <option value="">Selecciona un ID</option>
                        <?php foreach ($clientesDisponibles as $cliente): ?>
                        <option value="<?= $cliente['ID_Cliente'] ?>" <?= $clienteID == $cliente['ID_Cliente'] ? 'selected' : '' ?>>
                            <?= $cliente['ID_Cliente'] ?> - <?= htmlspecialchars($cliente['Nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="../clientes/clientesCre.php" class="btn btn-primary w-100">Nuevo Cliente</a>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required value="<?= htmlspecialchars($fecha) ?>">
            </div>

            <div class="col-md-12 text-start">
                <label for="loteID" class="form-label">ID Lote</label>
                <select class="form-select" id="loteID" name="loteID" onchange="actualizarCantidadLote(this)">
                    <option value="">Selecciona un ID</option>
                    <?php foreach ($lotesDisponibles as $lote): ?>
                        <option value="<?= $lote['ID_Lote'] ?>" data-cantidad="<?= $lote['Cantidad'] ?>"
                            <?= $loteID == $lote['ID_Lote'] ? 'selected' : '' ?>>
                            <?= $lote['ID_Lote'] ?> - <?= htmlspecialchars($lote['Nombre']) ?> - <?= htmlspecialchars($lote['Cantidad']) ?> - <?= htmlspecialchars($lote['FechaCaducidad']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Input oculto para la cantidad -->
            <input type="hidden" name="cantidadMax" id="cantidadMax" value="">


            <div class="mb-4 row">
                <div class="col-md-10 text-start">
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" min="1" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad del Lote" required>
                </div>
                <div class="col-md-2">
                    <label for="precio" class="form-label">Precio Unitario</label>
                    <input type="number" min="1" class="form-control" id="precio" name="precio" placeholder="Precio Unitario" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-success btn-lg w-100">Registrar</button>
            <a href=".php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>
        </form>
        <script>
            function actualizarCantidadLote(select) {
                const selectedOption = select.options[select.selectedIndex];
                const cantidad = selectedOption.getAttribute('data-cantidad') || '';
                document.getElementById('cantidadMax').value = cantidad;
            }
        </script>
    </body>
</html>