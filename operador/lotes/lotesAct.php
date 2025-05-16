<?php
session_start();
    require_once '../../includes/db.php';




if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit;
    }

    $rol = $_SESSION['rol'];

    if ($rol!= 'Operador'){
        header('Location: ../../operador/admin.php')
        exit;
    }


    #Variables xddd
    $productoID='';
    $tipo='';
    $fechaEntrada = '';
    $fechaCaducidad = '';
    $errores = [];
    $productosDisponibles = [];

    #Definir variables con lo del formulario
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $productoID = $_POST['productoId'];
        $tipo = $_POST['tipo'];
        $fechaEntrada = $_POST['fechaEntrada'];
        $fechaCaducidad = $_POST['fechaCaducidad'];
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modificar Lote</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center">
            <h2 class="mb-4">Modificar Lote</h2>
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
                <label for="productoID" class="form-label">ID lote</label>
                <select class="form-select" id="IDlOTE" name="IDlOTE" required>
                    <option value="">Selecciona un id</option>
                    <?php foreach ($LotesDisponibles as $lote): ?>
                    <option value="<?= $lote['ID_Lote'] ?>" <?= $IDlOTE == $lote['ID_Lote'] ? 'selected' : '' ?>>
                    <?= $producto['ID_Lote'] ?> - <?= htmlspecialchars($producto['Nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="lotes.php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>
        </form>
    </body>
</html>