<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'tecnicos';
$pageSubtitle = 'Gestión y seguimiento de técnicos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Técnicos</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Técnicos</h2>
   <p class="page-desc">Administrar técnicos y seguimiento de su rendimiento.</p>
  </div>

  <div class="tabs-container">
   <div class="tabs">
    <button class="tab active" data-tab="lista">Lista de Técnicos</button>
    <button class="tab" data-tab="historial">Historial</button>
   </div>

   <div class="tab-content active" id="tab-lista">
    <div class="card">
     <div class="card-header">
      <div class="filters-row">
       <div class="filter-group">
        <input type="text" class="filter-input" placeholder="Buscar técnico...">
       </div>
       <div class="filter-group">
        <select class="filter-input">
         <option value="">Todos los estados</option>
         <option value="activo">Activo</option>
         <option value="inactivo">Inactivo</option>
        </select>
       </div>
       <div class="filter-group filter-group-btn">
        <button class="btn btn-primary">+ Agregar Técnico</button>
       </div>
      </div>
     </div>
     <div class="table-responsive">
      <table class="table">
       <thead>
        <tr>
         <th>ID</th>
         <th>Nombre</th>
         <th>Apellido</th>
         <th>Email</th>
         <th>Especialidad</th>
         <th>Estado</th>
         <th>Trabajos asignados</th>
         <th>Acciones</th>
        </tr>
       </thead>
       <tbody>
        <tr>
         <td>T-001</td>
         <td>Juan</td>
         <td>Pérez</td>
         <td>juan.perez@eva.com</td>
         <td>Sensores ultrasónicos</td>
         <td><span class="badge activo">Activo</span></td>
         <td>12</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial" onclick="verHistorial('T-001')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Desactivar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>T-002</td>
         <td>María</td>
         <td>Gómez</td>
         <td>maria.gomez@eva.com</td>
         <td>Presión y flujo</td>
         <td><span class="badge activo">Activo</span></td>
         <td>8</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial" onclick="verHistorial('T-002')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Desactivar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>T-003</td>
         <td>Laura</td>
         <td>Díaz</td>
         <td>laura.diaz@eva.com</td>
         <td>Instalaciones</td>
         <td><span class="badge inactivo">Inactivo</span></td>
         <td>5</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial" onclick="verHistorial('T-003')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Activar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>T-004</td>
         <td>Roberto</td>
         <td>Sánchez</td>
         <td>roberto.sanchez@eva.com</td>
         <td>Mantenimiento general</td>
         <td><span class="badge activo">Activo</span></td>
         <td>15</td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver historial" onclick="verHistorial('T-004')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Desactivar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></button>
         </td>
        </tr>
       </tbody>
      </table>
     </div>
    </div>
   </div>

   <div class="tab-content" id="tab-historial">
    <div class="card">
     <div class="card-header">
      <div class="filters-row">
       <div class="filter-group">
        <label class="filter-label">Seleccionar técnico</label>
        <select class="filter-input" id="tecnicoSelect" onchange="cargarHistorial()">
         <option value="">-- Seleccionar --</option>
         <option value="T-001">T-001 - Juan Pérez</option>
         <option value="T-002">T-002 - María Gómez</option>
         <option value="T-003">T-003 - Laura Díaz</option>
         <option value="T-004">T-004 - Roberto Sánchez</option>
        </select>
       </div>
       <div class="filter-group">
        <label class="filter-label">Estado</label>
        <select class="filter-input">
         <option value="">Todos</option>
         <option value="asignado">Asignado</option>
         <option value="en_progreso">En progreso</option>
         <option value="completado">Completado</option>
        </select>
       </div>
      </div>
     </div>

     <div id="historialContent">
      <div class="empty-state" id="emptyHistorial">
       <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
       <p>Selecciona un técnico para ver su historial</p>
      </div>

      <div class="tecnico-stats" id="tecnicoStats" style="display:none">
       <div class="tecnico-stat-card">
        <div class="tecnico-stat-val" id="statTotal">0</div>
        <div class="tecnico-stat-label">Total trabajos</div>
       </div>
       <div class="tecnico-stat-card">
        <div class="tecnico-stat-val" id="statCompletados">0</div>
        <div class="tecnico-stat-label">Completados</div>
       </div>
       <div class="tecnico-stat-card">
        <div class="tecnico-stat-val" id="statEnProgreso">0</div>
        <div class="tecnico-stat-label">En progreso</div>
       </div>
       <div class="tecnico-stat-card">
        <div class="tecnico-stat-val" id="statPendientes">0</div>
        <div class="tecnico-stat-label">Pendientes</div>
       </div>
       <div class="tecnico-stat-card">
        <div class="tecnico-stat-val" id="statRendimiento">0%</div>
        <div class="tecnico-stat-label">Rendimiento</div>
       </div>
      </div>

      <div class="table-responsive" id="historialTable" style="display:none">
       <table class="table">
        <thead>
         <tr>
          <th>ID Trabajo</th>
          <th>Tipo</th>
          <th>Descripción</th>
          <th>Ubicación</th>
          <th>Fecha asignación</th>
          <th>Fecha completado</th>
          <th>Estado</th>
          <th>Prioridad</th>
         </tr>
        </thead>
        <tbody id="historialBody">
        </tbody>
       </table>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
