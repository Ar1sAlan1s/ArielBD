<?php
    session_start();
    require_once '../../includes/db.php';

        
    if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit;
    }

    $rol = $_SESSION['rol'];

    if ($rol!= 'Operador'){
        header('Location: ../../operador/admin.php');
        exit;
    }
            

    $resultados = [];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $filtroID = isset($_GET['ID_Cliente']) ? $conn->real_escape_string($_GET['ID_Cliente']) : '';
        $filtroNombre= isset($_GET['Nombre']) ? $conn->real_escape_string($_GET['Nombre']) : '';
        $filtroCiudad = isset($_GET['Ciudad']) ? $conn->real_escape_string($_GET['Ciudad']) : '';
        $filtroEstado = isset($_GET['Estado']) ? $conn->real_escape_string($_GET['Estado']) : '';
        
        $query = "SELECT * FROM Cliente";

        if (!empty($filtroID)) {
            $query .= " WHERE ID_Cliente LIKE '%$filtroID%'";
        }
        if (!empty($filtroNombre)) {
            $query .= " WHERE Nombre LIKE '%$filtroNombre%'";
        }
        if (!empty($filtroCiudad)) {
            $query .= " WHERE TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(Direccion, ',', -4), ',', 1)) LIKE '%$filtroCiudad%'";
        }
        if (!empty($filtroEstado)) {
            $query .= " WHERE TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(Direccion, ',', -3), ',', 1)) LIKE '%$filtroEstado%'";
        }

        $res = $conn->query($query);

        if ($res) {
            while ($fila = $res->fetch_assoc()) {
                $resultados[] = $fila;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mostrar Lote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <form method="GET" action="">
        <div class="container text-center">
            <h2>Mostrar Cliente</h2>
        </div>
        <div class="container mb-3">
            <div class="input-group mb-3">
                <span class="input-group-text">ID</span>
                <input type="text" class="form-control" name="ID_Cliente" placeholder="Introduzca el ID">

                <span class="input-group-text">Nombre</span>
                <input type="text" class="form-control" name="Nombre" placeholder="Introduzca el nombre">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Ciudad</span>
                <input type="text" class="form-control" name="Ciudad">

                <span class="input-group-text">Estado</span>
                <input type="text" class="form-control" name="Estado" >
            </div>

            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <div class="container">
        <?php if (!empty($resultados)): ?>
            <h4>Resultados encontrados:</h4>
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th>ID Cliente</th>
                        <th>Nombre</th>
                        <th>RFC</th>
                        <th>Direccion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['ID_Cliente']) ?></td>
                            <td><?= htmlspecialchars($fila['Nombre']) ?></td>
                            <td><?= htmlspecialchars($fila['RFC']) ?></td>
                            <td><?= htmlspecialchars($fila['Direccion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
            <p>No se encontraron resultados.</p>
        <?php endif; ?>

        <a href="..\cajero.php" class="btn btn-danger w-100 mt-3">Volver</a>
    </div>
</body>
</html>