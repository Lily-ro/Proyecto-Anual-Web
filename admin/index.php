<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'inicio';
$pageSubtitle = 'Resumen general del sistema';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Panel de Administracion</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="stats-row stats-row-6">
   <div class="stat-card anim-bounce0">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Clientes</div>
     <div class="stat-card-value">32</div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce1">
    <div class="stat-card-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M9 7v14M15 7v14M21 7v14M6 11h.01M6 15h.01M12 11h.01M12 15h.01M18 11h.01M18 15h.01"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Edificios</div>
     <div class="stat-card-value">5</div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce2">
    <div class="stat-card-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Dispositivos EVA</div>
     <div class="stat-card-value">42</div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce3">
    <div class="stat-card-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Instalaciones</div>
     <div class="stat-card-value">25</div>
     <div class="stat-card-sub">Totales</div>
    </div>
   </div>
   <div class="stat-card anim-bounce4">
    <div class="stat-card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Tanques</div>
     <div class="stat-card-value">18</div>
     <div class="stat-card-sub">Totales</div>
    </div>
   </div>
   <div class="stat-card anim-bounce5">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Tecnicos</div>
     <div class="stat-card-value">15</div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce6">
    <div class="stat-card-icon purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Sensores</div>
     <div class="stat-card-value">96</div>
     <div class="stat-card-sub">Instalados</div>
    </div>
   </div>
  </div>

  <div class="main-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Estado de dispositivos</div></div>
    <div class="donut-wrapper">
     <div class="donut-container">
      <svg class="donut-svg" id="donutSvg" viewBox="0 0 140 140"></svg>
      <div class="donut-center">
       <div class="donut-val">42</div>
       <div class="donut-label">Total</div>
      </div>
     </div>
     <div class="donut-legend">
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:#4caf50"></div><div class="donut-legend-text">Activos</div><div class="donut-legend-val">30</div><div class="donut-legend-pct">(71%)</div></div>
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:#ff9800"></div><div class="donut-legend-text">Advertencias</div><div class="donut-legend-val">8</div><div class="donut-legend-pct">(19%)</div></div>
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:#f44336"></div><div class="donut-legend-text">Inactivos</div><div class="donut-legend-val">4</div><div class="donut-legend-pct">(4%)</div></div>
     </div>
    </div>
   </div>
   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Mantenimientos pendientes</div><a class="card-link">Ver todos</a></div>
    <table class="table">
     <thead><tr><th>Tarea</th><th>Ubicacion</th><th>Fecha</th><th>Prioridad</th></tr></thead>
     <tbody>
      <tr><td>Revision general</td><td>Tanque Norte</td><td>15/05/2025</td><td><span class="badge alta">Alta</span></td></tr>
      <tr><td>Limpieza de sensores</td><td>Tanque Centro</td><td>16/05/2025</td><td><span class="badge media">Media</span></td></tr>
      <tr><td>Calibracion de sensores</td><td>Tanque Sur</td><td>17/05/2025</td><td><span class="badge media">Media</span></td></tr>
     </tbody>
    </table>
   </div>
   <div class="card anim-bounce2">
    <div class="card-header"><div class="card-title">Actividad del sistema (7 dias)</div></div>
    <div class="line-chart">
     <svg id="lineChart" width="100%" height="160" viewBox="0 0 320 160"></svg>
    </div>
   </div>
  </div>

  <div class="bottom-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Usuarios recientes</div></div>
    <table class="table">
     <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Ultimo acceso</th></tr></thead>
     <tbody>
      <tr><td>Juan Perez</td><td>Tecnico</td><td><span class="badge activo">Activo</span></td><td>12/05/2025 10:15</td></tr>
      <tr><td>Maria Gomez</td><td>Tecnico</td><td><span class="badge activo">Activo</span></td><td>12/05/2025 09:40</td></tr>
      <tr><td>Carlos Ruiz</td><td>Administrador</td><td><span class="badge activo">Activo</span></td><td>12/05/2025 08:30</td></tr>
      <tr><td>Laura Diaz</td><td>Tecnico</td><td><span class="badge inactivo">Inactivo</span></td><td>10/05/2025 14:20</td></tr>
     </tbody>
    </table>
   </div>
   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Acciones rapidas</div></div>
    <div class="quick-actions">
     <a class="quick-action" href="#">
      <div class="quick-action-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></div>
      <div class="quick-action-text">Agregar usuario</div>
     </a>
     <a class="quick-action" href="empresas.php">
      <div class="quick-action-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M3 21h18M3 7v14M9 7v14M15 7v14M21 7v14M6 11h.01M6 15h.01M12 11h.01M12 15h.01M18 11h.01M18 15h.01"/></svg></div>
      <div class="quick-action-text">Gestionar empresas</div>
     </a>
     <a class="quick-action" href="dispositivos.php">
      <div class="quick-action-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg></div>
      <div class="quick-action-text">Gestionar dispositivos</div>
     </a>
     <a class="quick-action" href="tanques.php">
      <div class="quick-action-icon purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
      <div class="quick-action-text">Gestionar tanques</div>
     </a>
    </div>
   </div>
  </div>

  <div class="charts-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Nivel promedio de agua por dia</div></div>
    <div class="chart-container"><svg id="nivelDiaChart" viewBox="0 0 400 180"></svg></div>
   </div>
   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Nivel promedio por edificio</div></div>
    <div class="chart-container"><svg id="nivelEdifChart" viewBox="0 0 400 180"></svg></div>
   </div>
   <div class="card anim-bounce2">
    <div class="card-header"><div class="card-title">Consumo semanal</div></div>
    <div class="chart-container"><svg id="consumoChart" viewBox="0 0 400 180"></svg></div>
   </div>
  </div>

  <div class="tables-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Dispositivos</div><a class="card-link">Ver todos</a></div>
    <div class="table-responsive">
     <table class="table">
      <thead><tr><th>Dispositivo</th><th>Tanque</th><th>Estado</th><th>Bateria</th><th>Senal</th><th>Ultima act.</th></tr></thead>
      <tbody>
       <tr><td>EVA-001</td><td>Tanque Norte</td><td><span class="badge activo">Activo</span></td><td><span class="badge activo">85%</span></td><td><span class="badge activo">Fuerte</span></td><td>Hace 2 min</td></tr>
       <tr><td>EVA-002</td><td>Tanque Centro</td><td><span class="badge activo">Activo</span></td><td><span class="badge activo">72%</span></td><td><span class="badge activo">Fuerte</span></td><td>Hace 5 min</td></tr>
       <tr><td>EVA-003</td><td>Tanque Sur</td><td><span class="badge advertencia">Alerta</span></td><td><span class="badge media">45%</span></td><td><span class="badge media">Debil</span></td><td>Hace 12 min</td></tr>
       <tr><td>EVA-004</td><td>Tanque Este</td><td><span class="badge activo">Activo</span></td><td><span class="badge activo">91%</span></td><td><span class="badge activo">Fuerte</span></td><td>Hace 1 min</td></tr>
       <tr><td>EVA-005</td><td>Tanque Oeste</td><td><span class="badge inactivo">Inactivo</span></td><td><span class="badge baja">12%</span></td><td><span class="badge inactivo">Sin senal</span></td><td>Hace 3 horas</td></tr>
      </tbody>
     </table>
    </div>
   </div>

   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Ultimas alertas</div><a class="card-link">Ver todas</a></div>
    <div class="table-responsive">
     <table class="table">
      <thead><tr><th>Fecha</th><th>Tanque</th><th>Tipo</th><th>Estado</th></tr></thead>
      <tbody>
       <tr><td>12/05 10:30</td><td>Tanque Norte</td><td>Nivel bajo</td><td><span class="badge alta">Critica</span></td></tr>
       <tr><td>12/05 09:15</td><td>Tanque Sur</td><td>Fuga detectada</td><td><span class="badge alta">Critica</span></td></tr>
       <tr><td>12/05 08:40</td><td>Tanque Centro</td><td>Temperatura alta</td><td><span class="badge media">Media</span></td></tr>
       <tr><td>11/05 22:10</td><td>Tanque Este</td><td>Sensor offline</td><td><span class="badge media">Media</span></td></tr>
       <tr><td>11/05 18:05</td><td>Tanque Oeste</td><td>Bateria baja</td><td><span class="badge baja">Baja</span></td></tr>
      </tbody>
     </table>
    </div>
   </div>

   <div class="card anim-bounce2">
    <div class="card-header"><div class="card-title">Ultimos mantenimientos</div><a class="card-link">Ver todos</a></div>
    <div class="table-responsive">
     <table class="table">
      <thead><tr><th>Tecnico</th><th>Dispositivo</th><th>Estado</th><th>Fecha</th></tr></thead>
      <tbody>
       <tr><td>Juan Perez</td><td>EVA-001</td><td><span class="badge completada">Completado</span></td><td>12/05/2025</td></tr>
       <tr><td>Maria Gomez</td><td>EVA-003</td><td><span class="badge pendiente">Pendiente</span></td><td>13/05/2025</td></tr>
       <tr><td>Carlos Ruiz</td><td>EVA-002</td><td><span class="badge activo">En curso</span></td><td>12/05/2025</td></tr>
       <tr><td>Laura Diaz</td><td>EVA-005</td><td><span class="badge pendiente">Pendiente</span></td><td>14/05/2025</td></tr>
       <tr><td>Pedro Sanchez</td><td>EVA-004</td><td><span class="badge completada">Completado</span></td><td>11/05/2025</td></tr>
      </tbody>
     </table>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
