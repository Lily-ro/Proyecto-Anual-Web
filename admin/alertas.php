<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'alertas';
$pageSubtitle = 'Gestión de alertas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Alertas</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Alertas</h2>
   <p class="page-desc">Monitoreo y gestión de alertas del sistema.</p>
  </div>

  <div class="stats-row">
   <div class="stat-card stat-critical">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
    <div class="stat-info"><div class="stat-val">3</div><div class="stat-label">Críticas</div></div>
   </div>
   <div class="stat-card stat-warning">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
    <div class="stat-info"><div class="stat-val">5</div><div class="stat-label">Advertencias</div></div>
   </div>
   <div class="stat-card stat-info">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></div>
    <div class="stat-info"><div class="stat-val">12</div><div class="stat-label">Informativas</div></div>
   </div>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" placeholder="Buscar alerta...">
     </div>
     <div class="filter-group">
      <select class="filter-input">
       <option value="">Todos los niveles</option>
       <option value="critica">Crítica</option>
       <option value="advertencia">Advertencia</option>
       <option value="informativa">Informativa</option>
      </select>
     </div>
     <div class="filter-group">
      <select class="filter-input">
       <option value="">Todos los estados</option>
       <option value="activa">Activa</option>
       <option value="revisada">Revisada</option>
       <option value="resuelta">Resuelta</option>
      </select>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead>
      <tr>
       <th>ID</th>
       <th>Nivel</th>
       <th>Mensaje</th>
       <th>Sensor</th>
       <th>Tanque</th>
       <th>Fecha</th>
       <th>Estado</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <tr>
       <td>AL-001</td>
       <td><span class="badge alta">Crítica</span></td>
       <td>Nivel de agua bajo mínimo en tanque</td>
       <td>S-001</td>
       <td>Tanque Norte</td>
       <td>14/07/2026 08:30</td>
       <td><span class="badge pendiente">Activa</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Marcar revisada"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>AL-002</td>
       <td><span class="badge media">Advertencia</span></td>
       <td>Temperatura fuera de rango normal</td>
       <td>S-004</td>
       <td>Tanque Norte</td>
       <td>14/07/2026 07:15</td>
       <td><span class="badge pendiente">Activa</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Marcar revisada"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>AL-003</td>
       <td><span class="badge media">Advertencia</span></td>
       <td>Presión de sensor inconsistente</td>
       <td>S-002</td>
       <td>Tanque Centro</td>
       <td>13/07/2026 16:45</td>
       <td><span class="badge en_camino">Revisada</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Marcar resuelta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>AL-004</td>
       <td><span class="badge baja">Informativa</span></td>
       <td>Mantenimiento programado próximo</td>
       <td>--</td>
       <td>Tanque Sur</td>
       <td>13/07/2026 10:00</td>
       <td><span class="badge completada">Resuelta</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>