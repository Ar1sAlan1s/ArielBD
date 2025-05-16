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

$resultados = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtroID = isset($_GET['ID_Lote']) ? $conn->real_escape_string($_GET['ID_Lote']) : '';
    $filtroTipo = isset($_GET['tipo']) ? $conn->real_escape_string($_GET['tipo']) : '';
    $filtroFechaEntrada = isset($_GET['fechaEntrada']) ? $conn->real_escape_string($_GET['fechaEntrada']) : '';
    $filtroFechaCaducidad = isset($_GET['fechaCaducidad']) ? $conn->real_escape_string($_GET['fechaCaducidad']) : '';

    $query = "SELECT * FROM Lote WHERE 1=1";

    if (!empty($filtroID)) {
        $query .= " AND ID_Lote LIKE '%$filtroID%'";
    }
    if (!empty($filtroTipo)) {
        $query .= " AND Tipo = '$filtroTipo'";
    }
    if (!empty($filtroFechaEntrada)) {
        $query .= " AND FechaEntrada = '$filtroFechaEntrada'";
    }
    if (!empty($filtroFechaCaducidad)) {
        $query .= " AND FechaCaducidad = '$filtroFechaCaducidad'";
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
            <h2>Mostrar Lote</h2>
        </div>
        <div class="container mb-3">
            <div class="input-group mb-3">
                <span class="input-group-text">ID</span>
                <input type="text" class="form-control" name="ID_Lote" placeholder="Introduzca el ID">

                <span class="input-group-text">Tipo</span>
                <select class="form-select" name="tipo">
                    <option value="">Selecciona una opción</option>
                    <option value="MateriaPrima">Materia Prima</option>
                    <option value="Producto">Producto</option>
                </select>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Fecha de Entrada</span>
                <input type="date" class="form-control" name="fechaEntrada">

                <span class="input-group-text">Fecha de Caducidad</span>
                <input type="date" class="form-control" name="fechaCaducidad">
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
                        <th>Tipo</th>
                        <th>Fecha Entrada</th>
                        <th>Fecha Caducidad</th>
                        <th>ID Producto</th>
                        <th>ID Materia Prima</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['ID_Lote']) ?></td>
                            <td><?= htmlspecialchars($fila['Tipo']) ?></td>
                            <td><?= htmlspecialchars($fila['FechaEntrada']) ?></td>
                            <td><?= htmlspecialchars($fila['FechaCaducidad']) ?></td>
                             <?php if ($fila['Tipo'] === 'Producto'): ?>
                            <td><?= htmlspecialchars($fila['ID_Producto']) ?></td>
                            <td>—</td> 
                        <?php else: ?>
                            <td>—</td> 
                            <td><?= htmlspecialchars($fila['ID_MateriaPrima']) ?></td>
                        <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
            <p>No se encontraron resultados.</p>
        <?php endif; ?>

        <a href="lotes.php" class="btn btn-danger w-100 mt-3">Volver</a>
    </div>
</body>
</html>
