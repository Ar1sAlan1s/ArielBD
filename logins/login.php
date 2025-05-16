<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $usuario = $_POST['nombreUsuario'];
    $contrasena = $_POST['contrasena'];

    $stmt = $conn->prepare("SELECT * FROM Usuario WHERE Nombre = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuarioData = $result->fetch_assoc();


    if ($usuarioData && password_verify($contrasena, $usuarioData['Contrasena'])) {
        $_SESSION['usuario'] = $usuarioData['ID_Usuario'];
        $_SESSION['nombre'] = $usuarioData['Nombre'];
        $_SESSION['rol'] = $usuarioData['Rol'];
        $_SESSION['contrasena'] = $usuarioData['Contrasena'];

        if ($usuarioData['Rol'] == 'Administrador'){
            header("Location: ../admin/admin.php");
        } else{
            header("Location: ../operador/cajero.php");
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }

}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card bg-secondary shadow-lg border-0">
                    <div class="card-body">
                        <h3 class="text-center mb-4 text-white">Iniciar Sesión</h3>

                        <?php if (!empty($error)) : ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="nombreUsuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="nombreUsuario" name="nombreUsuario" required>
                            </div>

                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-light">Ingresar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>