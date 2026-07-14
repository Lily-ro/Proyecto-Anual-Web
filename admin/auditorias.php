<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'auditorias';
$pageSubtitle = 'Registro de actividad del sistema';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Auditorías</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Auditorías</h2>
   <p class="page-desc">Registro completo de actividad del sistema: quién ingresó, qué modificó, fecha, hora e IP.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <label class="filter-label">Buscar</label>
      <input type="text" class="filter-input" id="auditSearch" placeholder="Buscar por usuario, acción...">
     </div>
     <div class="filter-group">
      <label class="filter-label">Fecha desde</label>
      <input type="date" class="filter-input" id="auditDateFrom">
     </div>
     <div class="filter-group">
      <label class="filter-label">Fecha hasta</label>
      <input type="date" class="filter-input" id="auditDateTo">
     </div>
     <div class="filter-group">
      <label class="filter-label">Tipo de acción</label>
      <select class="filter-input" id="auditType">
       <option value="">Todas</option>
       <option value="login">Inicio de sesión</option>
       <option value="logout">Cierre de sesión</option>
       <option value="create">Creación</option>
       <option value="update">Modificación</option>
       <option value="delete">Eliminación</option>
       <option value="config">Configuración</option>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary" onclick="filtrarAuditorias()">Filtrar</button>
      <button class="btn btn-secondary" onclick="limpiarFiltros()">Limpiar</button>
     </div>
    </div>
   </div>

   <div class="table-responsive">
    <table class="table" id="auditTable">
     <thead>
      <tr>
       <th>Fecha</th>
       <th>Hora</th>
       <th>Usuario</th>
       <th>Rol</th>
       <th>Acción</th>
       <th>Detalle</th>
       <th>IP</th>
      </tr>
     </thead>
     <tbody>
      <tr>
       <td>01/07/2026</td>
       <td>08:15:32</td>
       <td>Juan Pérez</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge accion-login">Inicio de sesión</td>
       <td>Ingresó al sistema</td>
       <td>192.168.1.45</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>08:22:10</td>
       <td>Juan Pérez</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge accion-update">Modificación</td>
       <td>Actualizó datos del tanque T-001</td>
       <td>192.168.1.45</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>09:05:44</td>
       <td>María Gómez</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge accion-login">Inicio de sesión</td>
       <td>Ingresó al sistema</td>
       <td>192.168.1.78</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>09:30:15</td>
       <td>Carlos Ruiz</td>
       <td><span class="badge rol-admin">Admin</span></td>
       <td><span class="badge accion-create">Creación</td>
       <td>Creó nuevo usuario: Laura Díaz</td>
       <td>10.0.0.12</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>10:12:08</td>
       <td>María Gómez</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge accion-update">Modificación</td>
       <td>Actualizó firmware del dispositivo D-015</td>
       <td>192.168.1.78</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>10:45:33</td>
       <td>Carlos Ruiz</td>
       <td><span class="badge rol-admin">Admin</span></td>
       <td><span class="badge accion-delete">Eliminación</td>
       <td>Eliminó dispositivo D-008 (dado de baja)</td>
       <td>10.0.0.12</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>11:20:19</td>
       <td>Laura Díaz</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge accion-login">Inicio de sesión</td>
       <td>Ingresó al sistema</td>
       <td>192.168.2.33</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>11:55:41</td>
       <td>Juan Pérez</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge accion-logout">Cierre de sesión</td>
       <td>Cerró sesión</td>
       <td>192.168.1.45</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>12:10:27</td>
       <td>Carlos Ruiz</td>
       <td><span class="badge rol-admin">Admin</span></td>
       <td><span class="badge accion-config">Configuración</td>
       <td>Modificó parámetros de alertas del sistema</td>
       <td>10.0.0.12</td>
      </tr>
      <tr>
       <td>01/07/2026</td>
       <td>13:05:55</td>
       <td>María Gómez</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge accion-logout">Cierre de sesión</td>
       <td>Cerró sesión</td>
       <td>192.168.1.78</td>
      </tr>
     </tbody>
    </table>
   </div>

   <div class="table-footer">
    <div class="table-info">Mostrando 10 de 156 registros</div>
    <div class="pagination">
     <button class="btn btn-page" disabled>&laquo; Anterior</button>
     <button class="btn btn-page btn-page-active">1</button>
     <button class="btn btn-page">2</button>
     <button class="btn btn-page">3</button>
     <button class="btn btn-page">...</button>
     <button class="btn btn-page">16</button>
     <button class="btn btn-page">Siguiente &raquo;</button>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
