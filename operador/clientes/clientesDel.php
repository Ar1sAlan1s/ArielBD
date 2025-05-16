<?php
    session_start();
    require_once '../../includes/db.php';
    
    if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit();
    }

    $rol = $_SESSION['rol'];

    if ($rol!= 'Operador'){
        header('Location: ../../admin/admin.php')
        exit();
    }      
    
    # Variables
    $clienteID = '';
    $errores = [];

    #Definir variables con lo del formulario
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $clienteID = $_POST['clienteID'] ?? '';

        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Venta WHERE ID_Cliente = ?");
        $stmt->bind_param("i", $clienteID);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data['total'] > 0) {
            $errores[] = 'No se puede eliminar. Cliente asociado con una venta.';
        }


        if (empty($errores)) {

            $stmtDelete = $conn->prepare("DELETE FROM Cliente WHERE ID_Cliente = ?");
            $stmtDelete->bind_param("i", $clienteID);

            if ($stmtDelete->execute()) {
                header('Location: enlace.php?status=deleted');
                exit;
                #Se elimina el cliente
            } else {
                $errores[] = 'Error al eliminar el cliente. Inténtalo de nuevo.';
            }
            $insertStmt->close();
        }
        
    }

    # Obtener IDs
    $query = "SELECT ID_Cliente, Nombre FROM Cliente";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Eliminar Cliente</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    </head>
    <body class="bg-light d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center">
            <h2 class="mb-4">Eliminar Cliente</h2>
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

            <div class="mb-3 col-md-12 text-start">
                <label for="loteID" class="form-label">ID Cliente</label>
                <select class="form-select" id="clienteID" name="clienteID" >
                    <option value="">Selecciona un ID</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['ID_Cliente'] ?>" <?= $clienteID == $cliente['ID_Cliente'] ? 'selected' : '' ?>>
                            <?= $cliente['ID_Cliente'] ?> - <?= htmlspecialchars($cliente['Nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            
            <button type="submit" class="btn btn-success btn-lg w-100">Eliminar</button>
            <a href="enlace.php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>
        </form>
    </body>
</html>