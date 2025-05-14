<?php
    require_once '../../includes/db.php';
    #Variables xddd
    $idLote = '';
    $productoID='';
    $tipo='';
    $fechaEntrada = '';
    $fechaCaducidad = '';
    $errores = [];

    #Definir variables con lo del formulario
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $idLote = $_POST['loteID'];
        $productoID = $_POST['productoID'];
        $tipo = $_POST['tipo'];
        $fechaEntrada = $_POST['fechaEntrada'];
        $fechaCaducidad = $_POST['fechaCaducidad'];
    }
   
    #Despliega los ids dependiendo el tipo
    if (!empty($tipo)) {
        if ($tipo === 'Materia Prima') {
            $tabla = 'MateriaPrima';
            $Id = 'ID_MateriaPrima';
        } elseif ($tipo === 'Producto') {
            $tabla = 'Producto';
            $Id = 'ID_Producto';
        } else {
            $tabla = null;
        }

        if ($tabla) {
            $query = "SELECT ID_Producto, Nombre FROM $tabla";
            $result = $conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $productosDisponibles[] = $row;
                }
            }
        }
    }

    #Verificar que las fechas sean mas adelante de la que se hace el registro
    $hoy = date('Y-m-d');
    if (empty($fechaEntrada) || empty($fechaCaducidad)) {
        $errores[] = 'Ambas fechas son obligatorias.';
    } elseif ($fechaEntrada < $hoy) {
        $errores[] = 'La fecha de entrada debe ser igual o posterior a la fecha de hoy.';
    } elseif ($fechaCaducidad <= $fechaEntrada) {
        $errores[] = 'La fecha de caducidad debe ser posterior a la fecha de entrada.';
    }
    #Se registra el lote
    if (empty($errores)) {
        $insertStmt = $conn->prepare(
            "INSERT INTO Lote (ID_Producto,Tipo, Fecha_Entrada, Fecha_Caducidad) VALUES (?, ?, ?, ?)"
        );
        $tipo   = trim($_POST['tipo']);
        $insertStmt->bind_param(
            "isss",
            $productoID,
            $tipo,
            $fechaEntrada,
            $fechaCaducidad
            
        );
        if ($insertStmt->execute()) {
            header('Location: lotes.php?status=success');
            exit;
        } else {
            $errores[] = 'Error al registrar el lote. Inténtalo de nuevo.';
        }
        $insertStmt->close();
    }


?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resgistrar Lote</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center">
            <h2 class="mb-4">Registrar Lote</h2>
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
                <select class="form-select" id="tipo" name="tipo" required onchange="this.form.submit()">
                    <option value="" disabled <?= $tipo === '' ? 'selected' : '' ?>>Selecciona una opción</option>
                    <option value="Materia Prima" <?= $tipo === 'Materia Prima' ? 'selected' : '' ?>>Materia Prima</option>
                    <option value="Producto" <?= $tipo === 'Producto' ? 'selected' : '' ?>>Producto</option>
                </select>
            </div>

           <div class="mb-3 text-start">
                <label for="productoID" class="form-label">ID Producto</label>
                <select class="form-select" id="productoID" name="productoId" required>
                    <option value="">Selecciona un id</option>
                    <?php foreach ($productosDisponibles as $producto): ?>
                    <option value="<?= $producto['ID_Producto'] ?>" <?= $productoID == $producto['ID_Producto'] ? 'selected' : '' ?>>
                    <?= $producto['ID_Producto'] ?> - <?= htmlspecialchars($producto['Nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
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
            <a href="lotes.php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>
        </form>
    </body>
</html>