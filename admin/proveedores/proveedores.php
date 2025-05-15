<?php 
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])){
    header('Location: ../../logins/login.php');
    exit;
}

$rol = $_SESSION['rol'];

if ($rol != 'Administrador'){
    header('Location: ../../operador/cajero.php');
    exit;
}

$sql = "SELECT * FROM Proveedor";
$result = $conn->query($sql);
if ($result){
    $proveedores = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Error al obtener los proveedores: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Proveedores registrados</h2>
            <a href="proveedoresCre.php" class="btn btn-success">Agregar nuevo proveedor</a>
        </div>

        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">¡Operación exitosa!</div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $proveedor) : ?>
                        <tr>
                            <td><?= htmlspecialchars($proveedor['ID_Proveedor']) ?></td>
                            <td><?= htmlspecialchars($proveedor['Nombre']) ?></td>
                            <td><?= htmlspecialchars($proveedor['Direccion']) ?></td>
                            <td><?= htmlspecialchars($proveedor['Telefono']) ?></td>
                            <td>
                                <a href="proveedoresAct.php?id=<?= $proveedor['ID_Proveedor'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a href="proveedoresDel.php?id=<?= $proveedor['ID_Proveedor'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>