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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_id'])) {
    $id = (int) $_POST['buscar_id'];
    $query = "SELECT * FROM MateriaPrima WHERE ID_MateriaPrima = $id LIMIT 1";
    $resultado = mysqli_query($conn, $query);
    $materia = mysqli_fetch_assoc($resultado);

    $queryProveedores = "SELECT * FROM Proveedor";
    $proveedores = mysqli_query($conn, $queryProveedores);
}

// 3. Si se envió el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int) $_POST['id'];
    $nombre = $_POST['nombre'];
    $unidad = $_POST['unidad'];
    $id_proveedor = (int) $_POST['id_proveedor'];
    $caducidad = $_POST['caducidad'];

    $updateQuery = "UPDATE MateriaPrima 
                    SET Nombre = '$nombre',
                        Unidad = '$unidad',
                        ID_Proveedor = $id_proveedor,
                        FechaCaducidad = '$caducidad'
                    WHERE ID_MateriaPrima = $id";

    if (mysqli_query($conn, $updateQuery)) {
$mensaje = '<div id="alerta" class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Éxito!</strong> Se actualizo Correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>'; 
 } else {
        
$mensaje = '<div id="alerta" class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>¡Error!</strong> No se pudo actualizar.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';     }

    // Volver a mostrar lista actualizada
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
  <?php echo $mensaje; ?>

  <table class="table table-bordered table-hover">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Unidad</th>
        <th>Proveedor</th>
        <th>Fecha de Caducidad</th>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php while ($fila = mysqli_fetch_assoc($lista)): ?>
        <tr>
          <td><?php echo $fila['ID_MateriaPrima']; ?></td>
          <td><?php echo $fila['Nombre']; ?></td>
          <td><?php echo $fila['Unidad']; ?></td>
          <td><?php echo $fila['ID_Proveedor']; ?></td>
          <td><?php echo $fila['FechaCaducidad']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Formulario para pedir el ID a modificar -->
  <form method="POST" class="mb-5">
    <div class="input-group">
      <span class="input-group-text">ID a modificar</span>
      <input type="number" name="buscar_id" class="form-control" required>
      <button type="submit" class="btn btn-primary">Editar</button>
    </div>
  </form>

  <!-- Mostrar formulario si se seleccionó un ID válido -->
  <?php if ($materia): ?>
    <div class="card">
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
            <label class="form-label">Unidad</label>
            <input type="text" name="unidad" class="form-control" value="<?php echo $materia['Unidad']; ?>" required>
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
          <div class="mb-3">
            <label class="form-label">Fecha de Caducidad</label>
            <input type="date" name="caducidad" class="form-control" value="<?php echo $materia['FechaCaducidad']; ?>" required>
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
