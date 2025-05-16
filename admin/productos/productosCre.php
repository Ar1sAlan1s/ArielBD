<?php 
session_start();
$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}
include('../../includes/db.php');

$mensaje = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST['nombre'];
  $tipo = $_POST['tipo'];
  $presentacion = $_POST['presentacion'];

  $subconsulta = "INSERT INTO `Producto`(`Nombre`,`Tipo`,`Presentacion`) VALUES
  ('$nombre','$tipo','$presentacion')";

  if (mysqli_query($conn, $subconsulta)) {
    $mensaje = '<div id="alerta" class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Éxito!</strong> Se agregó correctamente el producto.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
  }
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
        <h4 class="mb-0">Registrar Producto</h4>
      </div>
      <div class="card-body">
        <?php echo $mensaje; ?>

        <form action="productosCre.php" method="POST">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required />
          </div>
          <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <input type="text" class="form-control" id="tipo" name="tipo" required />
          </div>
          <div class="mb-3">
            <label for="presentacion" class="form-label">Presentación</label>
            <input type="text" class="form-control" id="presentacion" name="presentacion" required />
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
