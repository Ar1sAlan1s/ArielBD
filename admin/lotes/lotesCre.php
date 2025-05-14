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

    if(empty($idLote)){
        $errores[] = 'El ID de lote es obligatorio';
    }
    #Verificar que no este vacio el id y verificar que exista en la BD
   
    if (empty($productoID)) {
        $errores[] = 'El ID de producto es obligatorio.';
    } else {
        $stmt = $conn->prepare("SELECT 1 FROM Producto WHERE ID_Producto = ?");
        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $errores[] = "El producto con ID $productoID no existe.";
        }
        $stmt->close();
    }
    #Verificar que las fechas sean mas adelante de la que se hace el registro
    $hoy = date('Y-m-d');
    if (empty($fechaEntrada) || empty($fechaCaducidad)) {
        $errors[] = 'Ambas fechas son obligatorias.';
    } elseif ($fechaEntrada < $hoy) {
        $errors[] = 'La fecha de entrada debe ser igual o posterior a la fecha de hoy.';
    } elseif ($fechaCaducidad <= $fechaEntrada) {
        $errors[] = 'La fecha de caducidad debe ser posterior a la fecha de entrada.';
    }
    #Se registra el lote
    if (empty($errores)) {
        $insertStmt = $conn->prepare(
            "INSERT INTO Lote (ID_Lote, Tipo, Fecha_Entrada, Fecha_Caducidad, ID_Producto) VALUES (?, ?, ?, ?, ?)"
        );
        $idLote = trim($_POST['loteID']);
        $tipo   = trim($_POST['tipo']);
        $insertStmt->bind_param(
            "isssi",
            $idLote,
            $tipo,
            $fechaEntrada,
            $fechaCaducidad,
            $productoID
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
        <title>MResgistrar Lote</title>
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
                <label for="loteID" class="form-label">ID Lote</label>
                <input type="number" class="form-control" id="loteID" name="loteID" placeholder="ID Lote" required value="<?= htmlspecialchars($idLote) ?>">
            </div>
            <div class="mb-3 text-start">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" placeholder="Tipo" required value="<?= htmlspecialchars($tipo) ?>">
            </div>
            <div class="mb-3 text-start">
                <label for="fechaEntrada" class="form-label">Fecha de Entrada</label>
                <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" required value="<?= htmlspecialchars($fechaEntrada) ?>">
            </div>
            <div class="mb-3 text-start">
                <label for="fechaCaducidad" class="form-label">Fecha de Caducidad</label>
                <input type="date" class="form-control" id="fechaCaducidad" name="fechaCaducidad" required value="<?= htmlspecialchars($fechaCaducidad) ?>">
            </div>
            <div class="mb-3 text-start">
                <label for="productoID" class="form-label">ID Producto</label>
                <input type="number" class="form-control" id="productoID" name="ProductoId" placeholder="ID Producto" required value="<?= htmlspecialchars($productoID) ?>">
            </div>
            <button type="submit" class="btn btn-success btn-lg w-100">Registrar</button>
            <a href="lotes.php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>
        </form>
    </body>
</html>