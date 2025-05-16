<?php
session_start();
require_once '../../includes/db.php';

    if (!isset($_SESSION['usuario'])){
        header('Location: ../../logins/login.php');
        exit;
    }

    $rol = $_SESSION['rol'];+

    if ($rol!= 'Administrador'){
        header('Location: ../../operador/cajero.php')
        exit;
    }


    if (isset($_GET['id_producto']) && isset($_GET('id_materia'))){
        $id_producto = $_GET['id_producto'];
        $id_materia = $_GET['id_materia'];

        
    $stmt = $conn->prepare("DELETE FROM Receta WHERE ID_Producto = ? AND ID_MateriaPrima = ?");
    $stmt->bind_param("ii", $id_producto, $id_materia);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Receta eliminada correctamente. ";
    } else {
        $_SESSION['error'] = "Error al eliminar: " . $conn->error;
    }
    
    
    header('Location: recetas.php');
    exit;

    }

?>
