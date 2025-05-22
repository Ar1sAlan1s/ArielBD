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

// Cargar todos los productos para el select y la tabla
$queryProductos = "SELECT * FROM Producto";
$result = mysqli_query($conn, $queryProductos);

$queryLista = "SELECT * FROM Producto";
$lista = mysqli_query($conn, $queryLista);

// Si se seleccionó un producto para editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto']) && !isset($_POST['nombre'])) {
    $id = (int) $_POST['producto'];
    $query = "SELECT * FROM Producto WHERE ID_Producto = $id LIMIT 1";
    $resultado = mysqli_query($conn, $query);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $producto = mysqli_fetch_assoc($resultado);
    }
}

// Si se envió el formulario de edición para actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar']) && isset($_POST['nombre'])) {
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

    // Recargar la lista y el select después del update
    $result = mysqli_query($conn, $queryProductos);
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
  <a href="../admin.php" class="btn btn-primary mb-3"><i class="bi bi-arrow-left"></i> Volver al menú</a>
  <?php echo $mensaje; ?>

  <!-- Formulario para seleccionar producto -->
  <form method="POST" class="mb-5">
    <div class="input-group">
      <span class="input-group-text">ID a modificar</span>
      <select class="form-select" id="producto" name="producto" required>
        <option value="" disabled selected>Seleccione un producto</option>
        <?php foreach ($result as $productoItem): ?>
          <option value="<?= $productoItem['ID_Producto'] ?>" <?= isset($_POST['producto']) && $_POST['producto'] == $productoItem['ID_Producto'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($productoItem['ID_Producto']) ?> - <?= htmlspecialchars($productoItem['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Editar</button>
    </div>
  </form>

  <!-- Tabla con lista de productos -->
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
          <td><?= $fila['ID_Producto']; ?></td>
          <td><?= $fila['Nombre']; ?></td>
          <td><?= $fila['Tipo']; ?></td>
          <td><?= $fila['Presentacion']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Formulario para editar producto -->
  <?php if ($producto): ?>
    <div class="card">
      <div class="card-header bg-warning text-white">
        <h4>Editar Producto ID <?= $producto['ID_Producto']; ?></h4>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="id" value="<?= $producto['ID_Producto']; ?>">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= $producto['Nombre']; ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Tipo</label>
            <input type="text" name="tipo" class="form-control" value="<?= $producto['Tipo']; ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Presentación</label>
            <input type="text" name="presentacion" class="form-control" value="<?= $producto['Presentacion']; ?>" required>
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
