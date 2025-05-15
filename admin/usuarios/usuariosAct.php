<?php
    session_start();
    require_once '../../includes/db.php';

    if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit;
    }

    $rol = $_SESSION['rol'];

    if ($rol!='Administrador'){
        header('Location: ../../operador/cajero.php')
        exit;
    }

    $errores = [];
    $usuario = null;

    if (isset($_GET['id'])){
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM Usuario WHERE ID_Usuario == ?");

        $stmt = bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $producto = $result->fetch-assoc();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $id = $_POST['id'];
        if ($_POST['nombre'] != ""){
            $nombre = trim($_POST['nombre']);
        } 
        if ($_POST['rol'] != ""){
            $rol = trim($_POST['rol']);
        } 
        if ($_POST['contrasena'] != ""){
            $contrasena = trim($_POST['contrasena']);
        } 
       

        //Validaciones
        if (!isset($nombre) && !isset($rol) && !isset($contrasena)){
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
            $stmt = bind_param("sss", $nombre, $rol, $contrasena, $id);

            if ($stmt->execute()) {
                header ('Location: usuarios.php?success?=1');
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
            
        </form>

    </div>
    
</body>
</html>