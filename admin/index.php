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
<title>EVA - Panel de Administración</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="stats-row">
   <div class="stat-card anim-bounce0">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Usuarios</div>
     <div class="stat-card-value">32</div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce1">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Técnicos</div>
     <div class="stat-card-value">8</div>
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
    <div class="stat-card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Mantenimientos</div>
     <div class="stat-card-value">18</div>
     <div class="stat-card-sub">Pendientes</div>
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
     <thead><tr><th>Tarea</th><th>Ubicación</th><th>Fecha</th><th>Prioridad</th></tr></thead>
     <tbody>
      <tr><td>Revisión general</td><td>Tanque Norte</td><td>15/05/2025</td><td><span class="badge alta">Alta</span></td></tr>
      <tr><td>Limpieza de sensores</td><td>Tanque Centro</td><td>16/05/2025</td><td><span class="badge media">Media</span></td></tr>
      <tr><td>Calibración de sensores</td><td>Tanque Sur</td><td>17/05/2025</td><td><span class="badge media">Media</span></td></tr>
     </tbody>
    </table>
   </div>
   <div class="card anim-bounce2">
    <div class="card-header"><div class="card-title">Actividad del sistema (7 días)</div></div>
    <div class="line-chart">
     <svg id="lineChart" width="100%" height="160" viewBox="0 0 320 160"></svg>
    </div>
   </div>
  </div>

  <div class="bottom-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Usuarios recientes</div></div>
    <table class="table">
     <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último acceso</th></tr></thead>
     <tbody>
      <tr><td>Juan Pérez</td><td>Técnico</td><td><span class="badge activo">Activo</span></td><td>12/05/2025 10:15</td></tr>
      <tr><td>María Gómez</td><td>Técnico</td><td><span class="badge activo">Activo</span></td><td>12/05/2025 09:40</td></tr>
      <tr><td>Carlos Ruiz</td><td>Administrador</td><td><span class="badge activo">Activo</span></td><td>12/05/2025 08:30</td></tr>
      <tr><td>Laura Díaz</td><td>Técnico</td><td><span class="badge inactivo">Inactivo</span></td><td>10/05/2025 14:20</td></tr>
     </tbody>
    </table>
   </div>
   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Acciones rápidas</div></div>
    <div class="quick-actions">
     <a class="quick-action" href="#">
      <div class="quick-action-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></div>
      <div class="quick-action-text">Agregar usuario</div>
     </a>
     <a class="quick-action" href="tecnicos.php">
      <div class="quick-action-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
      <div class="quick-action-text">Gestionar técnicos</div>
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
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
