<?php
session_start();
$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}
include('../../includes/db.php');

$mensaje = '';
$producto = null;

$queryLista = "SELECT * FROM Producto";
$lista = mysqli_query($conn, $queryLista);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_id'])) {
    $id = (int) $_POST['buscar_id'];
    $query = "SELECT * FROM Producto WHERE ID_Producto = $id LIMIT 1";
    $resultado = mysqli_query($conn, $query);
    $producto = mysqli_fetch_assoc($resultado);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int) $_POST['id'];
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $presentacion = $_POST['presentacion'];

    $updateQuery = "UPDATE Producto 
                    SET Nombre = '$nombre',
                        Tipo = '$tipo',
                        Presentacion = '$presentacion'
                    WHERE ID_Producto = $id";

    if (mysqli_query($conn, $updateQuery)) {
        $mensaje = '<div id="alerta" class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> Se actualizó correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>'; 
    } else {
        $mensaje = '<div id="alerta" class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>¡Error!</strong> No se pudo actualizar.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>'; 
    }

    $lista = mysqli_query($conn, $queryLista);
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Actualizar Producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">

  <h2 class="text-center mb-4">Lista de Productos</h2>
  <a href="../admin.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al menú
    </a>
  <?php echo $mensaje; ?>

  <table class="table table-bordered table-hover">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Presentación</th>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php while ($fila = mysqli_fetch_assoc($lista)): ?>
        <tr>
          <td><?php echo $fila['ID_Producto']; ?></td>
          <td><?php echo $fila['Nombre']; ?></td>
          <td><?php echo $fila['Tipo']; ?></td>
          <td><?php echo $fila['Presentacion']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <form method="POST" class="mb-5">
    <div class="input-group">
      <span class="input-group-text">ID a modificar</span>
      <input type="number" name="buscar_id" class="form-control" required>
      <button type="submit" class="btn btn-primary">Editar</button>
    </div>
  </form>

  <?php if ($producto): ?>
    <div class="card">
      <div class="card-header bg-warning text-white">
        <h4>Editar Producto ID <?php echo $producto['ID_Producto']; ?></h4>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="id" value="<?php echo $producto['ID_Producto']; ?>">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo $producto['Nombre']; ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Tipo</label>
            <input type="text" name="tipo" class="form-control" value="<?php echo $producto['Tipo']; ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Presentación</label>
            <input type="text" name="presentacion" class="form-control" value="<?php echo $producto['Presentacion']; ?>" required>
          </div>
          <button type="submit" name="actualizar" class="btn btn-success">Guardar Cambios</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const alerta = document.getElementById('alerta');
    if (alerta) {
      setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alerta);
        bsAlert.close();
      }, 3000);
    }
  });
</script>
</body>
</html>
