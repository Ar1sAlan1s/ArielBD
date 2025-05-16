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

$errores = [];
$receta = null;

// Obtener ID de la receta a editar
if (isset($_GET['id_producto']) && isset($_GET['id_materia'])) {
    $id_producto = $_GET['id_producto'];
    $id_materia = $_GET['id_materia'];
    
    // Obtener datos actuales de la receta
    $stmt = $conn->prepare("SELECT 
                            r.CantidadNecesaria,
                            p.Nombre AS producto_nombre,
                            m.Nombre AS materia_nombre,
                            m.Unidad
                        FROM Receta r
                        JOIN Producto p ON r.ID_Producto = p.ID_Producto
                        JOIN MateriaPrima m ON r.ID_MateriaPrima = m.ID_MateriaPrima
                        WHERE r.ID_Producto = ? AND r.ID_MateriaPrima = ?");
    $stmt->bind_param("ii", $id_producto, $id_materia);
    $stmt->execute();
    $receta = $stmt->get_result()->fetch_assoc();
    
    if (!$receta) {
        $_SESSION['error'] = "Receta no encontrada";
        header('Location: recetas.php');
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST['id_producto'];
    $id_materia = $_POST['id_materia'];
    $cantidad = trim($_POST['cantidad']);
    
    // Validaciones
    if (!is_numeric($cantidad) || $cantidad <= 0) {
        $errores[] = "La cantidad debe ser un número mayor que 0";
    }
    
    if (empty($errores)) {
        $stmt = $conn->prepare("UPDATE Receta SET CantidadNecesaria = ? WHERE ID_Producto = ? AND ID_MateriaPrima = ?");
        $stmt->bind_param("dii", $cantidad, $id_producto, $id_materia);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Receta actualizada correctamente";
            header('Location: recetas.php');
            exit;
        } else {
            $errores[] = "Error al actualizar: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Receta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Editar Receta</h2>
        
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($receta): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($receta['producto_nombre']) ?></h5>
                    <p class="card-text">Materia Prima: <?= htmlspecialchars($receta['materia_nombre']) ?></p>
                </div>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="id_producto" value="<?= htmlspecialchars($id_producto) ?>">
                <input type="hidden" name="id_materia" value="<?= htmlspecialchars($id_materia) ?>">
                
                <div class="mb-3 text-start">
                    <label for="cantidad" class="form-label">Cantidad Necesaria (<?= htmlspecialchars($receta['Unidad']) ?>)</label>
                    <input type="number" step="0.01" min="0.01" class="form-control" id="cantidad" name="cantidad" 
                           value="<?= htmlspecialchars($receta['CantidadNecesaria']) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100">Actualizar</button>
                <a href="recetas.php" class="btn btn-danger btn-lg w-100 mt-2">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>