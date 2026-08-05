<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'mantenimientos';
$pageSubtitle = 'Gestión de mantenimientos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Mantenimientos</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Mantenimientos</h2>
   <p class="page-desc">Programación y seguimiento de mantenimientos.</p>
  </div>

  <div class="tabs-container">
   <div class="tabs">
    <button class="tab active" data-tab="programados">Programados</button>
    <button class="tab" data-tab="historial">Historial</button>
   </div>

   <div class="tab-content active" id="tab-programados">
    <div class="card">
     <div class="card-header">
      <div class="filters-row">
       <div class="filter-group">
        <input type="text" class="filter-input" placeholder="Buscar mantenimiento...">
       </div>
       <div class="filter-group">
        <select class="filter-input">
         <option value="">Todos los tipos</option>
         <option value="preventivo">Preventivo</option>
         <option value="correctivo">Correctivo</option>
         <option value="calibracion">Calibración</option>
        </select>
       </div>
       <div class="filter-group filter-group-btn">
        <button class="btn btn-primary">+ Programar Mantenimiento</button>
       </div>
      </div>
     </div>
     <div class="table-responsive">
      <table class="table">
       <thead>
        <tr>
         <th>ID</th>
         <th>Tipo</th>
         <th>Descripción</th>
         <th>Sensor/Tanque</th>
         <th>Fecha programada</th>
         <th>Técnico</th>
         <th>Prioridad</th>
         <th>Acciones</th>
        </tr>
       </thead>
       <tbody>
        <tr>
         <td>MT-001</td>
         <td>Preventivo</td>
         <td>Limpieza general de sensores</td>
         <td>Tanque Norte</td>
         <td>15/07/2026</td>
         <td>Juan Pérez</td>
         <td><span class="badge media">Media</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>MT-002</td>
         <td>Calibración</td>
         <td>Calibración sensor de presión</td>
         <td>S-002</td>
         <td>18/07/2026</td>
         <td>María Gómez</td>
         <td><span class="badge alta">Alta</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
         </td>
        </tr>
        <tr>
         <td>MT-003</td>
         <td>Correctivo</td>
         <td>Reparación cableado sensor</td>
         <td>S-004</td>
         <td>20/07/2026</td>
         <td>Roberto Sánchez</td>
         <td><span class="badge alta">Alta</span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
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
        <input type="text" class="filter-input" placeholder="Buscar en historial...">
       </div>
       <div class="filter-group">
        <input type="date" class="filter-input" placeholder="Desde">
       </div>
       <div class="filter-group">
        <input type="date" class="filter-input" placeholder="Hasta">
       </div>
      </div>
     </div>
     <div class="table-responsive">
      <table class="table">
       <thead>
        <tr>
         <th>ID</th>
         <th>Tipo</th>
         <th>Descripción</th>
         <th>Sensor/Tanque</th>
         <th>Fecha completado</th>
         <th>Técnico</th>
         <th>Estado</th>
        </tr>
       </thead>
       <tbody>
        <tr>
         <td>MT-010</td>
         <td>Preventivo</td>
         <td>Mantenimiento preventivo trimestral</td>
         <td>Tanque Centro</td>
         <td>01/07/2026</td>
         <td>Juan Pérez</td>
         <td><span class="badge completada">Completado</span></td>
        </tr>
        <tr>
         <td>MT-011</td>
         <td>Calibración</td>
         <td>Calibración semestral sensores</td>
         <td>Tanque Sur</td>
         <td>15/06/2026</td>
         <td>María Gómez</td>
         <td><span class="badge completada">Completado</span></td>
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