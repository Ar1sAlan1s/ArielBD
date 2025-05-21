<?php
session_start();
$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}
include('../../includes/db.php');

$mensaje = '';
$materia = null;

$queryLista = "SELECT * FROM MateriaPrima";
$lista = mysqli_query($conn, $queryLista);

$query = "SELECT ID_MateriaPrima AS id, Nombre FROM MateriaPrima";
$result = $conn->query($query);

// Buscar una materia prima seleccionada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto'])) {
    $id = (int) $_POST['producto'];
    $query = "SELECT * FROM MateriaPrima WHERE ID_MateriaPrima = $id LIMIT 1";
    $resultado = mysqli_query($conn, $query);
    $materia = mysqli_fetch_assoc($resultado);

    $queryProveedores = "SELECT * FROM Proveedor";
    $proveedores = mysqli_query($conn, $queryProveedores);
}

// Actualizar la materia prima
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int) $_POST['id'];
    $nombre = $_POST['nombre'];
    $unidad = $_POST['unidad'];
    $id_proveedor = (int) $_POST['id_proveedor'];

    $updateQuery = "UPDATE MateriaPrima 
                    SET Nombre = '$nombre',
                        Unidad = '$unidad',
                        ID_Proveedor = $id_proveedor
                    WHERE ID_MateriaPrima = $id";

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

    // Refrescar la lista
    $lista = mysqli_query($conn, $queryLista);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Actualizar Materia Prima</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">

  <h2 class="text-center mb-4">Lista de Materias Primas</h2>
  <a href="../admin.php" class="btn btn-primary mb-3">
    <i class="bi bi-arrow-left"></i> Volver al menú
  </a>
  <?php echo $mensaje; ?>

  <!-- Formulario para elegir materia prima -->
  <form method="POST" class="mb-5">
    <div class="input-group">
      <span class="input-group-text">ID a modificar</span>
      <select class="form-select" id="producto" name="producto" required>
        <option value="" disabled selected>Seleccione una materia prima</option>
        <?php foreach ($result as $materiaItem): ?>
          <option value="<?= $materiaItem['id'] ?>" <?= isset($_POST['producto']) && $_POST['producto'] == $materiaItem['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($materiaItem['id']) ?> - <?= htmlspecialchars($materiaItem[ 'Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Editar</button>
    </div>
  </form>

  <!-- Tabla de materias primas -->
  <table class="table table-bordered table-hover">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Unidad</th>
        <th>Proveedor</th>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php while ($fila = mysqli_fetch_assoc($lista)): ?>
        <tr>
          <td><?php echo $fila['ID_MateriaPrima']; ?></td>
          <td><?php echo $fila['Nombre']; ?></td>
          <td><?php echo $fila['Unidad']; ?></td>
          <td><?php echo $fila['ID_Proveedor']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Formulario de edición -->
  <?php if ($materia): ?>
    <div class="card mt-4">
      <div class="card-header bg-warning text-white">
        <h4>Editar Materia Prima ID <?php echo $materia['ID_MateriaPrima']; ?></h4>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="id" value="<?php echo $materia['ID_MateriaPrima']; ?>">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo $materia['Nombre']; ?>" required>
          </div>
          <div class="mb-3">
            <label for="unidad" class="form-label">Unidad</label>
            <select type="options" class="form-control" id="unidad" name="unidad" required>
              <option value="" disabled selected>Seleccione una Unidad</option>
              <option value="Kg">Kg</option>
              <option value="g">g</option>
              <option value="Lts">Lts</option>
              <option value="mlts">mlts</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Proveedor</label>
            <select name="id_proveedor" class="form-control" required>
              <?php while ($prov = mysqli_fetch_assoc($proveedores)): ?>
                <option value="<?php echo $prov['ID_Proveedor']; ?>"
                  <?php if ($prov['ID_Proveedor'] == $materia['ID_Proveedor']) echo 'selected'; ?>>
                  <?php echo $prov['ID_Proveedor'] . ' - ' . $prov['Nombre']; ?>
                </option>
              <?php endwhile; ?>
            </select>
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
