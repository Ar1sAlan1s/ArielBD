<!-- /ArielBD/includes/cajero_menu.php -->
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
    <a class="navbar-brand" href="/ArielBD/operador/cajero.php">🧾 Módulo Cajero</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cajeroNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="cajeroNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

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

        <!-- Ventas -->
        <li class="nav-item">
          <a class="nav-link" href="/ArielBD/operador/ventas/ventasAdd.php">Registrar Venta</a>
        </li>

        <!-- Lotes -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Lotes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/admin/lotes/lotesCre.php">Crear</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/lotes/lotes.php">Listar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/lotes/lotesAct.php">Editar</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/lotes/lotesDel.php">Eliminar</a></li>
          </ul>
        </li>

        <!-- Producción -->
        <li class="nav-item">
          <a class="nav-link" href="/ArielBD/operador/produccion/produccionAdd.php">Registrar Producción</a>
        </li>

        <!-- Movimientos de Inventario -->
        <li class="nav-item">
          <a class="nav-link" href="/ArielBD/operador/movimientos/movsAdd.php">Mov. de Inventario</a>
        </li>

        <!-- Reportes -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#">Reportes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/ArielBD/admin/reportes/inventario.php">Inventario actual</a></li>
            <li><a class="dropdown-item" href="/ArielBD/admin/reportes/ventas.php">Ventas</a></li>
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