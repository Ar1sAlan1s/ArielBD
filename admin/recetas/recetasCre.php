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

// Obtener la lista de productos y materias primas
$errores = [];

$sql_productos = "SELECT ID_Producto, Nombre FROM Producto ORDER BY Nombre";
$productos_result = $conn->query($sql_productos);
if ($productos_result) {
    $productos = $productos_result->fetch_all(MYSQLI_ASSOC);
} else {
    $errores[] = "Error al obtener los productos: " . $conn->error;
}

$sql_materias = "SELECT ID_MateriaPrima, Nombre, Unidad FROM MateriaPrima ORDER BY Nombre";
$materias_result = $conn->query($sql_materias);
if ($materias_result) {
    $materias = $materias_result->fetch_all(MYSQLI_ASSOC);
} else {
    $errores[] = "Error al obtener las materias primas: " . $conn->error;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST['producto'];
    $id_materia = $_POST['materia'];
    $cantidad = trim($_POST['cantidad']);

    // Validaciones
    if (empty($id_producto) || empty($id_materia) || empty($cantidad)) {
        $errores[] = "Todos los campos son obligatorios";
    }
    
    if (!is_numeric($cantidad) || $cantidad <= 0) {
        $errores[] = "La cantidad debe ser un número mayor que cero";
    }

    if (empty($errores)) {
        // Verificar si ya existe la combinación
        $stmt_check = $conn->prepare("SELECT * FROM Receta WHERE ID_Producto = ? AND ID_MateriaPrima = ?");
        $stmt_check->bind_param("ii", $id_producto, $id_materia);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $errores[] = "Esta combinación de producto y materia prima ya existe";
        } else {
            $stmt = $conn->prepare("INSERT INTO Receta (ID_Producto, ID_MateriaPrima, CantidadNecesaria) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $id_producto, $id_materia, $cantidad);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Receta creada exitosamente. ";
                header('Location: recetas.php');
                exit;
            } else {
                $errores[] = "Error al guardar la receta: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Receta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Nueva Receta</h2>

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
                <label for="producto" class="form-label">Producto</label>
                <select class="form-select" id="producto" name="producto" required>
                    <option value="" disabled selected>Seleccione un producto</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?= $producto['ID_Producto'] ?>" <?= isset($_POST['producto']) && $_POST['producto'] == $producto['ID_Producto'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($producto['Nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label for="materia" class="form-label">Materia Prima</label>
                <select class="form-select" id="materia" name="materia" required>
                    <option value="" disabled selected>Seleccione una materia prima</option>
                    <?php foreach ($materias as $materia): ?>
                        <option value="<?= $materia['ID_MateriaPrima'] ?>" <?= isset($_POST['materia']) && $_POST['materia'] == $materia['ID_MateriaPrima'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($materia['Nombre']) ?> (<?= htmlspecialchars($materia['Unidad']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label for="cantidad" class="form-label">Cantidad Necesaria</label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="cantidad" name="cantidad" 
                       value="<?= htmlspecialchars($_POST['cantidad'] ?? '') ?>" required>
                <small class="text-muted">Ingrese la cantidad por unidad de producto</small>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100">Guardar</button>
            <a href="recetas.php" class="btn btn-danger btn-lg w-100 mt-2">Cancelar</a>
        </form>
    </div>
</body>
</html>