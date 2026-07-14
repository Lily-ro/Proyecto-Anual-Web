<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'dispositivos';
$pageSubtitle = 'Gestión de dispositivos EVA y firmware';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Dispositivos</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Dispositivos</h2>
   <p class="page-desc">Administrar dispositivos EVA y control de firmware.</p>
  </div>

  <div class="tabs-container">
   <div class="tabs">
    <button class="tab active" data-tab="lista">Lista de Dispositivos</button>
    <button class="tab" data-tab="firmware">Firmware</button>
   </div>

   <div class="tab-content active" id="tab-lista">
    <div class="card">
     <div class="card-header">
      <div class="filters-row">
       <div class="filter-group">
        <input type="text" class="filter-input" placeholder="Buscar dispositivo...">
       </div>
       <div class="filter-group">
        <select class="filter-input">
         <option value="">Todos los tipos</option>
         <option value="ultrasonico">Ultrasónico</option>
         <option value="presion">Presión</option>
         <option value="nivel">Nivel</option>
         <option value="temperatura">Temperatura</option>
         <option value="flujo">Flujo</option>
        </select>
       </div>
       <div class="filter-group">
        <select class="filter-input">
         <option value="">Todos los estados</option>
         <option value="activo">Activo</option>
         <option value="advertencia">Advertencia</option>
         <option value="inactivo">Inactivo</option>
        </select>
       </div>
       <div class="filter-group filter-group-btn">
        <button class="btn btn-primary">+ Agregar Dispositivo</button>
       </div>
      </div>
     </div>
     <div class="table-responsive">
      <table class="table">
       <thead>
        <tr>
         <th>ID</th>
         <th>Nombre</th>
         <th>Tipo</th>
         <th>Ubicación</th>
         <th>Estado</th>
         <th>Firmware</th>
         <th>Última lectura</th>
         <th>Acciones</th>
        </tr>
       </thead>
       <tbody>
        <tr>
         <td>D-001</td>
         <td>Sensor Ultrasónico Norte</td>
         <td>Ultrasónico</td>
         <td>Tanque Norte</td>
         <td><span class="badge activo">Activo</span></td>
         <td>v2.4.1</td>
         <td>85%</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-002</td>
         <td> Sensor de Presión Centro</td>
         <td>Presión</td>
         <td>Tanque Centro</td>
         <td><span class="badge advertencia">Advertencia</span></td>
         <td>v2.3.8</td>
         <td>45 PSI</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-003</td>
         <td>Sensor de Nivel Sur</td>
         <td>Nivel</td>
         <td>Tanque Sur</td>
         <td><span class="badge activo">Activo</span></td>
         <td>v2.4.1</td>
         <td>72%</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-004</td>
         <td>Sensor de Temperatura Norte</td>
         <td>Temperatura</td>
         <td>Tanque Norte</td>
         <td><span class="badge inactivo">Inactivo</span></td>
         <td>v2.2.0</td>
         <td>--</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-005</td>
         <td>Sensor de Flujo Centro</td>
         <td>Flujo</td>
         <td>Tanque Centro</td>
         <td><span class="badge activo">Activo</span></td>
         <td>v2.4.0</td>
         <td>12 L/min</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       </tbody>
      </table>
     </div>
    </div>
   </div>

   <div class="tab-content" id="tab-firmware">
    <div class="card">
     <div class="card-header">
      <div class="card-title">Control de Firmware</div>
      <div class="firmware-note">
       <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
       <span>Solo visualización. La actualización de firmware no está habilitada para este rol.</span>
      </div>
     </div>
     <div class="table-responsive">
      <table class="table">
       <thead>
        <tr>
         <th>Dispositivo</th>
         <th>Tipo</th>
         <th>Versión actual</th>
         <th>Última actualización</th>
         <th>Estado firmware</th>
         <th>Acciones</th>
        </tr>
       </thead>
       <tbody>
        <tr>
         <td>D-001 - Sensor Ultrasónico Norte</td>
         <td>Ultrasónico</td>
         <td><span class="badge firmware-actual">v2.4.1</span></td>
         <td>15/06/2026</td>
         <td><span class="badge activo">Actualizado</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial de firmware"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></button>
          <button class="btn-icon btn-disabled" title="Actualizar firmware (deshabilitado)" disabled><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-002 - Sensor de Presión Centro</td>
         <td>Presión</td>
         <td><span class="badge firmware-antigua">v2.3.8</span></td>
         <td>01/05/2026</td>
         <td><span class="badge advertencia">Desactualizado</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial de firmware"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></button>
          <button class="btn-icon btn-disabled" title="Actualizar firmware (deshabilitado)" disabled><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-003 - Sensor de Nivel Sur</td>
         <td>Nivel</td>
         <td><span class="badge firmware-actual">v2.4.1</span></td>
         <td>15/06/2026</td>
         <td><span class="badge activo">Actualizado</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial de firmware"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></button>
          <button class="btn-icon btn-disabled" title="Actualizar firmware (deshabilitado)" disabled><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-004 - Sensor de Temperatura Norte</td>
         <td>Temperatura</td>
         <td><span class="badge firmware-antigua">v2.2.0</span></td>
         <td>10/03/2026</td>
         <td><span class="badge inactivo">Obsoleto</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial de firmware"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></button>
          <button class="btn-icon btn-disabled" title="Actualizar firmware (deshabilitado)" disabled><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>D-005 - Sensor de Flujo Centro</td>
         <td>Flujo</td>
         <td><span class="badge firmware-actual">v2.4.0</span></td>
         <td>10/06/2026</td>
         <td><span class="badge activo">Actualizado</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial de firmware"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></button>
          <button class="btn-icon btn-disabled" title="Actualizar firmware (deshabilitado)" disabled><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
         </td>
        </tr>
       </tbody>
      </table>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
