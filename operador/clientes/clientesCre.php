<?php
    session_start();
    require_once '../../includes/db.php';

    if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit();
    }

    $rol = $_SESSION['rol'];

    if ($rol!= 'Operador'){
        header('Location: ../../admin/admin.php');
        exit();
    }

    # Variables
    $nombre = '';
    $RFC = '';
    $direccion = '';
    $errores = [];

    #Definir variables con lo del formulario
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $nombre = $_POST['nombre']." ".$_POST['apellidoPaterno']." ".$_POST['apellidoMaterno'] ?? '';
        $RFC = $_POST['RFC'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        if(strlen($direccion) > 150){
            $errores[] = 'Error al registrar la direccion. Inténtalo de nuevo.';
        }
        if(strlen($RFC) > 13 || strlen($RFC) < 12 ){
            $errores[] = 'RFC no cumple con el tamaño adecuado. Inténtalo de nuevo. '.strlen($RFC);
        }
        

        if (empty($errores)) {

            $insertStmt = $conn->prepare(
                "INSERT INTO Cliente (Nombre, RFC, Direccion) VALUES (?, ?, ?)"
            );
            $insertStmt->bind_param(
                "sss",
                $nombre,
                $RFC,
                $direccion
            );

            if ($insertStmt->execute()) {
                header('Location: ..\cajero.php?status=success');
                exit();
            } else {
                $errores[] = 'Error al registrar el lote. Inténtalo de nuevo.';
            }
            $insertStmt->close();
        }

    }

    #Se registra el cliente
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registrar Cliente</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

        <!-- Estilo opcional para que el mapa tenga altura -->
        <style>
        #map {
            height: 400px;
            margin-bottom: 1rem;
        }
        </style>

    </head>
    <body class="bg-light d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center">
            <h2 class="mb-4">Registrar Cliente</h2>
            <a href="../cajero.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Volver al menú
            </a>
            <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
       <form method="POST" action="">

            <div class="mb-4 row">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del cliente" required>
                </div>
                <div class="col-md-4">
                    <label for="apellidoPaterno" class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="apellidoPaterno" name="apellidoPaterno" placeholder="Apellido paterno" required>
                </div>
                <div class="col-md-4">
                    <label for="apellidoMaterno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="apellidoMaterno" name="apellidoMaterno" placeholder="Apellido materno" required>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label for="RFC" class="form-label">RFC</label>
                <input type="text" class="form-control" id="RFC" name="RFC" placeholder="Ingresa el RFC del cliente" required>
            </div>

            <div class="mb-3 text-start">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Selecciona una ubicación en el mapa" required readonly>
            </div>

            <!-- Mapa -->
            <div id="map"></div>

            <!-- Campos ocultos para latitud y longitud -->
            <input type="hidden" id="latitud" name="latitud">
            <input type="hidden" id="longitud" name="longitud">

            
            <button type="submit" class="btn btn-success btn-lg w-100">Registrar</button>
            <a href="..\ventas\ventas.php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>
        </form>
        <script>
            // Inicializa el mapa en una posición por defecto
            var map = L.map('map').setView([19.7045, -101.1940], 13);

            // Agrega capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var marker;

            // Si el navegador permite obtener ubicación
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                map.setView([lat, lng], 15);
                });
            }

            // Al hacer clic en el mapa
            map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                if (marker) {
                marker.setLatLng(e.latlng);
                } else {
                marker = L.marker(e.latlng).addTo(map);
                }

                document.getElementById('latitud').value = lat;
                document.getElementById('longitud').value = lng;

                // Llamada a Nominatim para obtener dirección
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                    document.getElementById('direccion').value = data.display_name;
                    } else {
                    document.getElementById('direccion').value = 'Dirección no encontrada';
                    }
                })
                .catch(error => {
                    console.error('Error al obtener dirección:', error);
                    document.getElementById('direccion').value = 'Error al obtener dirección';
                });
            });
        </script>
    </body>
</html>