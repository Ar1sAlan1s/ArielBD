<?php
session_start();
$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}
include('../../includes/db.php');



$mensaje = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $consultaLote ="SELECT ID_Producto FROM Lote;";
    $resultado = mysqli_query($conn,$consultaLote);
    $id = $_POST['id'];
   $existe = false;

    while ($fila = mysqli_fetch_assoc($resultado)) {
        if ($fila['ID_Producto'] == $id) {
        $existe = true;
        break;
    }
    }
    if (!$existe) {
        $consulta = "DELETE FROM Producto WHERE ID_Producto = $id;";
    mysqli_query($conn, $consulta);
    $mensaje = '<div id="alerta" class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Éxito!</strong> Se eliminó correctamente el producto.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    }else {
        $mensaje = '<div id="alerta" class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>¡Error!</strong> No se puede eliminar este producto ya que se encuentra en un lote.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
    }
   
   
}

$subconsulta = "SELECT * FROM `Producto`";
$filas = mysqli_query($conn, $subconsulta);


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Eliminar Producto por ID</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-5">
  <h3 class="mb-4 text-center">Eliminar Productos</h3>
  <?php echo $mensaje; ?>
  <div class="row">
    <?php while ($columnas = mysqli_fetch_assoc($filas)): ?>
      <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <h5 class="card-title text-primary">ID: <?php echo $columnas['ID_Producto']; ?></h5>
              <p class="card-text mb-1"><strong>Nombre:</strong> <?php echo $columnas['Nombre']; ?></p>
              <p class="card-text mb-1"><strong>Tipo:</strong> <?php echo $columnas['Tipo']; ?></p>
              <p class="card-text"><strong>Presentación:</strong> <?php echo $columnas['Presentacion']; ?></p>
            </div>
            <form action="productosDel.php" method="post" class="mt-3">
              <input type="hidden" name="id" value="<?php echo $columnas['ID_Producto']; ?>">
              <button type="submit" class="btn btn-danger w-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash me-1" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5z"/>
                  <path d="M3.5 6a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8A.5.5 0 0 1 3.5 6z"/>
                  <path d="M14.5 3a1 1 0 0 1-1-1H2.5a1 1 0 0 1-1 1H1v1h14V3h-.5zM3.5 4a.5.5 0 0 0-.5.5v8A1.5 1.5 0 0 0 4.5 14h7a1.5 1.5 0 0 0 1.5-1.5v-8a.5.5 0 0 0-.5-.5h-9z"/>
                </svg>
                Eliminar
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
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
