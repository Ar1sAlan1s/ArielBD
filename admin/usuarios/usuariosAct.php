<?php
    session_start();
    require_once '../../includes/db.php';

if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit;
    }

    $rol = $_SESSION['rol'];+

    if ($rol!= 'Administrador'){
        header('Location: ../../operador/cajero.php')
        exit;
    }


    $errores = [];
    $usuario = null;

    if (isset($_GET['id'])){
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM Usuario WHERE ID_Usuario = ?");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $id = $_POST['id'];
        $nombre = !empty($_POST['nombre']) ? trim($_POST['nombre']) : $usuario['Nombre'];
        $rol = !empty($_POST['rol']) ? trim($_POST['rol']) : $usuario['Rol'];
        $contrasena = !empty($_POST['contrasena']) ? trim($_POST['contrasena']) : $usuario['Contrasena'];       

        //Validaciones
        if ($_POST['nombre']=="" && $_POST['rol'] == "" && $_POST['contrasena']==""){
            $errores[] = "No se puede editar con campos vacios. "; 
        }   

        if(empty($errores)){
            if(!isset($nombre)){
                $nombre = $_SESSION['nombre'];
            } else if (!isset($rol)){
                $rol = $_SESSION['rol'];
            } else if (!isset($contrasena)){
                $contrasena = $_SESSION['contrasena'];
            }

            $stmt = $conn->prepare("UPDATE Usuario SET Nombre = ?, Rol = ?, Contrasena = ? WHERE ID_Usuario = ?");
            $stmt->bind_param("sssi", $nombre, $rol, $contrasena, $id);

            if ($stmt->execute()) {
                header('Location: usuarios.php?success=1');
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
    <title>Editar Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class = "bg-light d-flex justify-content-center align-items-center vh-100">
    <div class = "container text-center">
        <h2 class="mb-4">Editar usuario</h2>

        <?php if(!empty($errores)): ?>
            <div class = "alert alert-danger">
                <ul class = "mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="" method = "POST">
            <input type="hidden" name="id" value="<?= $usuario['ID_Usuario'] ?? '' ?>">

            <div class="mb-3 text-start">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" 
                    value="<?= htmlspecialchars($usuario['Nombre'] ?? '') ?>"
                    placeholder="Dejar vacío para mantener el nombre actual">
            </div>
            
            <div class="mb-3 text-start">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select">
                    <option value="">Mantener rol actual</option>
                    <option value="Administrador" <?= ($usuario['Rol'] ?? '') == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                    <option value="Operador" <?= ($usuario['Rol'] ?? '') == 'Operador' ? 'selected' : '' ?>>Operador</option>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label for="contrasena" class="form-label">Nueva Contraseña (dejar vacío para mantener la actual)</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena">
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Actualizar</button>
            <a href="usuarios.php" class="btn btn-danger btn-lg w-100 mt-2">Cancelar</a>

        </form>

    </div>
</body>
</html>