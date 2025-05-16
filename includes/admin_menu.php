<!-- /ArielBD/includes/admin_menu.php -->
<style>
  .dropdown:hover .dropdown-menu {
    display: block;
  }

  .dropdown-menu {
    background-color: #212529;
  }

  .dropdown-item {
    color: #fff;
  }

  .dropdown-item:hover {
    background-color: #343a40;
  }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/ArielBD/admin/dashboard.php">🔧 Módulo de Administración</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <!-- Usuarios -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Usuarios</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/admin/usuarios/usuariosCre.php">Crear</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/usuarios/usuarios.php">Listar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/usuarios/usuariosAct.php">Editar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/usuarios/usuariosDel.php">Eliminar</a></li>
          </ul>
        </li>

        <!-- Proveedores -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Proveedores</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/admin/proveedores/proveedoresCre.php">Crear</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/proveedores/proveedores.php">Listar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/proveedores/proveedoresAct.php">Editar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/proveedores/proveedoresDel.php">Eliminar</a></li>
          </ul>
        </li>

        <!-- Materias Primas -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Materias Primas</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/admin/materia-prima/mpCre.php">Crear</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/materia-prima/mp.php">Listar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/materia-prima/mpAct.php">Editar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/materia-prima/mpDel.php">Eliminar</a></li>
          </ul>
        </li>

        <!-- Productos -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Productos</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/admin/productos/productosCre.php">Crear</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/productos/productos.php">Listar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/productos/productosAct.php">Editar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/productos/productosDel.php">Eliminar</a></li>
          </ul>
        </li>

        

        <!-- Movimiento de Inventario -->
        <li class="nav-item">
          <a class="nav-link" href="/ArielBD/operador/movimientos/movs.php">Movimiento de Inventario</a>
        </li>

        <!-- Producción -->
        <li class="nav-item">
          <a class="nav-link" href="/ArielBD/operador/produccion/produccion.php">Producción</a>
        </li>
         <!-- Recetas -->
        <li class="nav-item">
          <a class="nav-link" href="/ArielBD/admin/recetas.php">Recetas</a>
        </li>

        <!-- Clientes -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Clientes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/operador/clientes/clientesCre.php">Crear</a></li>
            <li><a class="dropdown-item" href="/ArielBD/operador/clientes/clientes.php">Listar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/operador/clientes/clientesAct.php">Editar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/operador/clientes/clientesDel.php">Eliminar</a></li>
          </ul>
        </li>

        <!-- Reportes -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Reportes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/admin/reportes.php">Inventario actual</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/reportes.php">Producción por fecha/producto</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/reportes.php">Ventas por fecha/producto/cliente</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/reportess.php">Movimientos históricos</a></li>
          </ul>
        </li>

      </ul>

      <!-- Cerrar sesión -->
      <form class="d-flex" method="POST" action="/ArielBD/logins/logout.php">
        <button class="btn btn-outline-light" type="submit">Cerrar sesión</button>
      </form>
    </div>
  </div>
</nav>




