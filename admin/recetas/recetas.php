<?php 
session_start();
require_once '../../includes/db.php';


if (!isset($_SESSION['usuario'])) {
    header('Location: ../../logins/login.php');
    exit;
}

$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$sql = "SELECT 
            r.ID_Producto,
            r.ID_MateriaPrima,
            r.CantidadNecesaria,
            p.Nombre AS NombreProducto,
            m.Nombre AS NombreMateriaPrima,
            m.Unidad
        FROM Receta r
        JOIN Producto p ON r.ID_Producto = p.ID_Producto
        JOIN MateriaPrima m ON r.ID_MateriaPrima = m.ID_MateriaPrima
        ORDER BY p.Nombre, m.Nombre";

$result = $conn->query($sql);
if ($result) {
    $recetas = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Error al obtener las recetas: " . $conn->error;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recetas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
         <?php if ($success) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Recetas registradas</h2>
            <a href="recetasCre.php" class="btn btn-success">Agregar nueva receta</a>
        </div>

        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">¡Operación exitosa!</div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Materia Prima</th>
                        <th>Cantidad Necesaria</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recetas as $receta) : ?>
                        <tr>
                            <td><?= htmlspecialchars($receta['NombreProducto']) ?></td>
                            <td><?= htmlspecialchars($receta['NombreMateriaPrima']) ?></td>
                            <td><?= htmlspecialchars($receta['CantidadNecesaria']) ?> <?= htmlspecialchars($receta['Unidad']) ?></td>
                            <td>
                                <a href="recetasAct.php?id_producto=<?= $receta['ID_Producto'] ?>&id_materia=<?= $receta['ID_MateriaPrima'] ?>" 
                                   class="btn btn-sm btn-warning">Editar</a>
                                <a href="recetasDel.php?id_producto=<?= $receta['ID_Producto'] ?>&id_materia=<?= $receta['ID_MateriaPrima'] ?>" 
                                   class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    
</body>
</html>