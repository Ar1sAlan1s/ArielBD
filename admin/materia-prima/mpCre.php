<?php 
session_start();
$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}
include('../../includes/db.php');

$mensaje = ''; 
  $query2 = "SELECT * FROM `Proveedor`;";
$resultado =mysqli_query($conn, $query2);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST['nombre'];
  $unidad = $_POST['unidad'];
  $id_proveedor = $_POST['id_proveedor'];
  $caducidad = $_POST['caducidad'];

  $query = "INSERT INTO `MateriaPrima`(`Nombre`,`Unidad`,`ID_Proveedor`,`FechaCaducidad`) VALUES
  ('$nombre','$unidad','$id_proveedor','$caducidad')";



  if (mysqli_query($conn, $query)) {
    $mensaje = '<div id="alerta" class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Éxito!</strong> Se agregó correctamente el producto.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
  }else {
$mensaje = '<div id="alerta" class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Error!</strong> Revise sus datos.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';  }
}
include_once '../../includes/admin_menu.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Registro de Producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="card shadow-lg">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Registrar Materia Prima</h4>
      </div>
      <div class="card-body">
        <?php echo $mensaje; ?>

        <form action="mpCre.php" method="POST">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required />
          </div>
          <div class="mb-3">
            <label for="unidad" class="form-label">Unidad</label>
            <input type="text" class="form-control" id="unidad" name="unidad" required />
          </div>
          <div class="mb-3">
           <label for="usuario">Proveedores</label>
                <select name="id_proveedor" id="id_proveedor" class="form-select" required>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <option value="<?php echo $fila['ID_Proveedor']; ?>">
                    <?php echo $fila['ID_Proveedor'] . ' - ' . $fila['Nombre']; ?>
                    </option>
                <?php endwhile; ?>
                </select>
          </div>
          <div class="mb-3">
            <label for="caducidad" class="form-label">Fecha de Caducidad</label>
            <input type="date" class="form-control" id="caducidad" name="caducidad" required />
          </div>
          <button type="submit" class="btn btn-success">Registrar</button>
        </form>
      </div>
    </div>
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
