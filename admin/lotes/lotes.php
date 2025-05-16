<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LOTES</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container text-center" >
        <h2>LOTES</h2>
        <div class="container" style="margin-top: 20px;">
            <form method="GET" action="lotesCre.php">
                <button type="submit" class="btn btn-primary btn-lg">Registrar Lote</button>
            </form>
        </div>
        <div class="container" style="margin-top: 20px;">
            <form method="GET" action="lotesMost.php">
                <button type="submit" class="btn btn-warning btn-lg">Mostrar Lote</button>
            </form>
        </div>    
        <!--
        <div class="container" style="margin-top: 20px;">
            <form method="GET" action="lotesAct.php">
                <button type="submit" class="btn btn-danger btn-lg">Modificar Lote</button>
            </form>

        </div>
        -->
        <a href="../../index.php" class="btn btn-danger btn-lg w-100 mt-2">Volver</a>

    </div>
</body>
</html>
