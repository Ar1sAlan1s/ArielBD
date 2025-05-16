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
        $filtroIDV = isset($_GET['ID_Venta']) ? $conn->real_escape_string($_GET['ID_Venta']) : '';
        $filtroIDU = isset($_GET['ID_Usuario']) ? $conn->real_escape_string($_GET['ID_Usuario']) : '';
        $filtroIDC = isset($_GET['ID_Cliente']) ? $conn->real_escape_string($_GET['ID_Cliente']) : '';
        $filtroFecha = isset($_GET['fecha']) ? $conn->real_escape_string($_GET['fecha']) : '';

        $query = "SELECT * FROM Venta";

        if (!empty($filtroIDV)) {
            $query .= " WHERE ID_Venta LIKE '%$filtroIDV%'";
        }
        if (!empty($filtroIDU)) {
            $query .= " WHERE ID_Usuario LIKE '%$filtroIDU%'";
        }
        if (!empty($filtroIDC)) {
            $query .= " WHERE ID_Cliente LIKE '%$filtroIDC%'";
        }
        if (!empty($filtroFecha)) {
            $query .= " WHERE Fecha = '$filtroFecha'";
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
            <h2>Mostrar Ventas</h2>
        </div>
        <div class="container mb-3">
            <div class="input-group mb-3">
                <span class="input-group-text">ID Venta</span>
                <input type="text" class="form-control" name="ID_Venta" placeholder="Introduzca el ID">
                
                <span class="input-group-text">Fecha</span>
                <input type="date" class="form-control" name="fecha">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">ID Usuario</span>
                <input type="text" class="form-control" name="ID_Usuario" placeholder="Introduzca el ID">

                <span class="input-group-text">ID Cliente</span>
                <input type="text" class="form-control" name="ID_Cliente" placeholder="Introduzca el ID">
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
                        <th>ID Lote</th>
                        <th>ID Usuario</th>
                        <th>ID Cliente</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['ID_Venta']) ?></td>
                            <td><?= htmlspecialchars($fila['ID_Usuario']) ?></td>
                            <td><?= htmlspecialchars($fila['ID_Cliente']) ?></td>
                            <td><?= htmlspecialchars($fila['Fecha']) ?></td>
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
