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

if (isset($_GET['id'])) {
    $id_proveedor = $_GET['id'];

    // Verificar si tiene materias primas asociadas
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM MateriaPrima WHERE ID_Proveedor = ?");
    $stmt->bind_param("i", $id_proveedor);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        $errores[] = "No se puede eliminar al proveedor porque tiene materias primas asociadas.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmar'])) {
    if ($_POST['confirmar'] == 'si') {
        $id = $_POST['id'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM Proveedor WHERE ID_Proveedor = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Proveedor eliminado correctamente";
            } else {
                $_SESSION['error'] = "Error al eliminar el proveedor";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
    
    header('Location: proveedores.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminación de Proveedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Eliminación de proveedor</h2>
        <a href="../admin.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al menú
    </a>

        <?php if(!empty($errores)) : ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php 
            sleep(3);
            header('Location: proveedores.php');
            exit;
            ?>
        <?php else : ?>
            <div class="card shadow-sm" style="max-width: 500px; margin: 0 auto;">
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
                        <p class="lead mb-4">¿Estás seguro de eliminar al proveedor ID <?= htmlspecialchars($_GET['id'] ?? '') ?>?</p>
                        
                        <div class="d-grid gap-2 d-md-block">
                            <button type="submit" name="confirmar" value="si" class="btn btn-danger me-2">Sí, eliminar</button>
                            <button type="submit" name="confirmar" value="no" class="btn btn-secondary">No, cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>