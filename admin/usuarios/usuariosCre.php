<?php 
    session_start();
    require_once '../../includes/db.php';


    if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit;
    }

    $rol = $_SESSION['rol'];

    if ($rol!= 'Administrador'){
        header('Location: ../../operador/cajero.php');
        exit;
    }

    $errores = [];

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $nombre = trim($_POST['nombre']);
        $rol = trim($_POST['rol']);
        $contrasena1 = trim($_POST['contrasena1']);
        $contrasena2 = trim($_POST['contrasena2']);

        //Validaciones
        if (empty($nombre)) $errores[] = "El nombre es obligatorio";
        if (empty($rol)) $errores[] = "El rol es obligatorio";
        if (empty($contrasena1) || empty($contrasena2)) $errores[] = "La contraseña es obligatoria";

        if ($contrasena1 != $contrasena2){
            $errores[] = "Las contraseñas no coinciden";
        }


        if (empty($errores)){
            $stmt = $conn->prepare("INSERT INTO Usuario (Nombre,Rol,Contrasena) VALUES (?,?,?)");
            //SSS, 3 strings
            $stmt->bind_param("sss", $nombre, $rol, $contrasena1);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Usuario creado con exito!";
                header ('Location: usuarios.php');
                exit;
            } else {
                $errores = "Error al ejecutar la consulta en la base de datos: " .$conn->error;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar usuario.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class = "bg-light d-flex justify-content-center align-items-center vh-100">
    <div class = "container text-center">
        <h2 class = "mb-4">Nuevo Usuario</h2>
        <a href="../admin.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al menú
    </a>

        <?php if (!empty($errores)): ?>
            <div class = "alert alert-danger">
                <ul class = "mb-0">
                    <?php foreach ($errores as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?> 
        
        <form action="" method = "POST">
            <div class = "mb-3 text-start">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>

            <div class="mb-3 text-start">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
                <option value="" disabled selected>Selecciona un rol</option>
                <option value="Administrador">Administrador</option>
                <option value="Operador">Operador</option>
            </select>
            </div>

            <div class = "mb-3 text-start">
                <label for="contrasena1" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="contrasena1" name="contrasena1" required>
            </div>

            <div class = "mb-3 text-start">
                <label for="contrasena2" class="form-label">Repetir contraseña:</label>
                <input type="password" class="form-control" id="contrasena2" name="contrasena2" required>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100">Guardar</button>
            <a href="productos.php" class="btn btn-danger btn-lg w-100 mt-2">Cancelar</a>

        </form>

    </div>
    
</body>
</html>