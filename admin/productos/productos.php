<?php 
    if (!isset($_SESSION['usuario'])){
        header('Location: login.php');
        exit;
    }

    $rol = $_SESSION['rol'];+

    if ($rol!= 'Administrador'){
        header('Location: cajero.php');
        exit;
    }
?>