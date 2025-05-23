<?php
session_start();
$rol = $_SESSION['rol'];

if ($rol != 'Administrador') {
    header('Location: ../../operador/cajero.php');
    exit;
}
include('../../includes/db.php');
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search){
    $query = "SELECT * FROM `MateriaPrima` WHERE Nombre LIKE '%$search%' 
             OR Unidad LIKE '%$search%'";
}else {
   $query = "SELECT * FROM `MateriaPrima`";

}
$filas = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Materia Prima</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .card-producto {
      transition: all 0.3s ease-in-out;
      border: 1px solid #dee2e6;
      border-radius: 1rem;
      padding: 20px;
      background-color: #ffffff;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      text-align: center; /* CENTRAR TEXTO EN TODA LA CARD */
    }

    .card-producto:hover {
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      transform: translateY(-4px);
    }

    .card-title {
      font-size: 1.25rem;
      font-weight: bold;
      color: #0d6efd;
      text-transform: uppercase;
      margin-bottom: 15px;
    }

    .card-text, 
    .card-date {
      font-size: 1rem;
      color: #555;
      margin-bottom: 10px;
    }

    @media (max-width: 768px) {
      .card-producto {
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="container py-5">
  <!-- Buscador -->
  <h2 class="text-center mb-4">Buscar Materia Prima</h2>
  <a href="../admin.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al menú
    </a>
  <form action="#" method="GET" class="row justify-content-center mb-5">
    <div class="col-md-6">
      <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, tipo o descripción...">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Buscar</button>
    </div>
  </form>

  <!-- Grid de productos -->
  <div class="row g-4">
    <?php while ($columnas = mysqli_fetch_assoc($filas)): ?>
  <div class="col-md-4 col-sm-6">
    <div class="card-producto h-100">
      <h5 class="card-title"><?php echo strtoupper($columnas['Nombre']); ?></h5>
      <p class="card-text"><strong>ID Materia Prima:</strong> <?php echo $columnas['ID_MateriaPrima']; ?></p>
      <p class="card-text"><strong>ID Proveedor:</strong> <?php echo $columnas['ID_Proveedor']; ?></p>
      <p class="card-text"><strong>Unidad:</strong> <?php echo $columnas['Unidad']; ?></p>
    </div>
  </div>
<?php endwhile; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
