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

    $sql = "SELECT * FROM Usuario";
    $result = $conn->query($sql);
    if ($result){
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "Error al obtener los usuarios";
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2>Usuarios registrados.</h2>
            <a href="usuariosCre.php" class="btn btn-success">Agregar nuevo usuario.</a>

        </div>

             <?php if ($success) : ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class = "table-responsive">
                <table class = "table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Contraseña</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['ID_Usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['Nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['Rol']) ?></td>
                                <td><?= htmlspecialchars($usuario['Contrasena']) ?></td>
                                <td>
                                    <a href="usuariosAct.php?id=<?= $usuario['ID_Usuario'] ?>" class = "btn btn-sm btn-warning" >Editar</a>
                                    <a href="usuariosDel.php?id=<?= $usuario['ID_Usuario']?>" class = "btn btn-sm btn-danger">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

    </div>
    
</body>
</html>