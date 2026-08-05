<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'reportes';
$pageSubtitle = 'Generación de reportes y estadísticas generales';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Reportes y Estadísticas</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Reportes y Estadísticas</h2>
   <p class="page-desc">Generar reportes del sistema y visualizar estadísticas generales.</p>
  </div>

  <!-- KPIs generales -->
  <div class="stats-row stats-row-4">
   <div class="stat-card anim-bounce0">
    <div class="stat-card-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Reportes generados</div>
     <div class="stat-card-value">128</div>
     <div class="stat-card-sub">Este mes</div>
    </div>
   </div>
   <div class="stat-card anim-bounce1">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Usuarios activos</div>
     <div class="stat-card-value">32</div>
     <div class="stat-card-sub">Total en sistema</div>
    </div>
   </div>
   <div class="stat-card anim-bounce2">
    <div class="stat-card-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Dispositivos operativos</div>
     <div class="stat-card-value">38</div>
     <div class="stat-card-sub">de 42 totales</div>
    </div>
   </div>
   <div class="stat-card anim-bounce3">
    <div class="stat-card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Mantenimientos</div>
     <div class="stat-card-value">18</div>
     <div class="stat-card-sub">Pendientes</div>
    </div>
   </div>
  </div>

  <!-- Generador de reportes + Estadísticas -->
  <div class="main-grid-2">
   <!-- Generador de reportes -->
   <div class="card">
    <div class="card-header">
     <div class="card-title">Generar reporte</div>
    </div>
    <div class="report-form">
     <div class="form-group">
      <label class="form-label">Tipo de reporte</label>
      <select class="form-input" id="reportTipo">
       <option value="">Seleccionar tipo...</option>
       <option value="usuarios">Reporte de usuarios</option>
        <option value="empresas">Reporte de empresas</option>
       <option value="dispositivos">Reporte de dispositivos</option>
       <option value="tanques">Reporte de tanques</option>
       <option value="compras">Reporte de compras</option>
       <option value="instalaciones">Reporte de instalaciones</option>
       <option value="mantenimientos">Reporte de mantenimientos</option>
       <option value="auditoria">Reporte de auditoría</option>
      </select>
     </div>
     <div class="form-row">
      <div class="form-group">
       <label class="form-label">Fecha desde</label>
       <input type="date" class="form-input" id="reportDesde">
      </div>
      <div class="form-group">
       <label class="form-label">Fecha hasta</label>
       <input type="date" class="form-input" id="reportHasta">
      </div>
     </div>
     <div class="form-group">
      <label class="form-label">Formato de salida</label>
      <div class="format-options">
       <label class="format-option">
        <input type="radio" name="formato" value="pdf" checked>
        <div class="format-card">
         <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
         <span>PDF</span>
        </div>
       </label>
       <label class="format-option">
        <input type="radio" name="formato" value="excel">
        <div class="format-card">
         <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
         <span>Excel</span>
        </div>
       </label>
       <label class="format-option">
        <input type="radio" name="formato" value="csv">
        <div class="format-card">
         <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
         <span>CSV</span>
        </div>
       </label>
      </div>
     </div>
     <button class="btn btn-primary btn-block" onclick="generarReporte()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Generar reporte
     </button>
    </div>
   </div>

   <!-- Estadísticas de actividad -->
   <div class="card">
    <div class="card-header">
     <div class="card-title">Actividad del sistema (30 días)</div>
    </div>
    <div class="chart-container">
     <svg id="barChart" width="100%" height="200" viewBox="0 0 400 200"></svg>
    </div>
    <div class="chart-legend-row">
     <div class="chart-legend-item"><div class="chart-legend-dot" style="background:var(--ac)"></div>Acciones</div>
     <div class="chart-legend-item"><div class="chart-legend-dot" style="background:var(--gn)"></div>Completadas</div>
    </div>
   </div>
  </div>

  <!-- Estadísticas detalladas -->
  <div class="main-grid">
   <!-- Distribución por rol -->
   <div class="card">
    <div class="card-header">
     <div class="card-title">Distribución de usuarios por rol</div>
    </div>
    <div class="donut-wrapper">
     <div class="donut-container">
      <svg class="donut-svg" id="rolesDonut" viewBox="0 0 140 140"></svg>
      <div class="donut-center">
       <div class="donut-val">32</div>
       <div class="donut-label">Total</div>
      </div>
     </div>
     <div class="donut-legend">
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:var(--ac)"></div><div class="donut-legend-text">Administradores</div><div class="donut-legend-val">3</div><div class="donut-legend-pct">(9%)</div></div>
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:var(--ac2)"></div><div class="donut-legend-text">Técnicos</div><div class="donut-legend-val">8</div><div class="donut-legend-pct">(25%)</div></div>
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:var(--gn)"></div><div class="donut-legend-text">Usuarios</div><div class="donut-legend-val">21</div><div class="donut-legend-pct">(66%)</div></div>
     </div>
    </div>
   </div>

   <!-- Estado de dispositivos -->
   <div class="card">
    <div class="card-header">
     <div class="card-title">Estado de dispositivos</div>
    </div>
    <div class="donut-wrapper">
     <div class="donut-container">
      <svg class="donut-svg" id="devicesDonut" viewBox="0 0 140 140"></svg>
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

   <!-- Resumen de compras -->
   <div class="card">
    <div class="card-header">
     <div class="card-title">Resumen de compras (mes)</div>
    </div>
    <div class="report-summary-list">
     <div class="report-summary-item">
      <div class="report-summary-label">Total compras</div>
      <div class="report-summary-val">35</div>
     </div>
     <div class="report-summary-item">
      <div class="report-summary-label">Aprobadas</div>
      <div class="report-summary-val report-summary-green">23</div>
     </div>
     <div class="report-summary-item">
      <div class="report-summary-label">Pendientes</div>
      <div class="report-summary-val report-summary-orange">7</div>
     </div>
     <div class="report-summary-item">
      <div class="report-summary-label">Canceladas</div>
      <div class="report-summary-val report-summary-red">5</div>
     </div>
     <div class="report-summary-item">
      <div class="report-summary-label">Monto total</div>
      <div class="report-summary-val report-summary-accent">$21,150.00</div>
     </div>
    </div>
   </div>
  </div>

  <!-- Últimos reportes generados -->
  <div class="card">
   <div class="card-header">
    <div class="card-title">Últimos reportes generados</div>
    <a class="card-link">Ver historial completo</a>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead>
      <tr>
       <th>ID</th>
       <th>Tipo</th>
       <th>Generado por</th>
       <th>Fecha</th>
       <th>Formato</th>
       <th>Período</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <tr>
       <td>RPT-001</td>
       <td>Reporte de dispositivos</td>
       <td>Carlos Ruiz</td>
       <td>01/07/2026 09:30</td>
       <td><span class="badge format-pdf">PDF</span></td>
       <td>Jun 2026</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Descargar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>RPT-002</td>
       <td>Reporte de técnicos</td>
       <td>Carlos Ruiz</td>
       <td>30/06/2026 14:15</td>
       <td><span class="badge format-excel">Excel</span></td>
       <td>Jun 2026</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Descargar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>RPT-003</td>
       <td>Reporte de compras</td>
       <td>Carlos Ruiz</td>
       <td>28/06/2026 11:00</td>
       <td><span class="badge format-pdf">PDF</span></td>
       <td>Jun 2026</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Descargar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>RPT-004</td>
       <td>Reporte de tanques</td>
       <td>María Gómez</td>
       <td>25/06/2026 16:45</td>
       <td><span class="badge format-csv">CSV</span></td>
       <td>Jun 2026</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Descargar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>RPT-005</td>
       <td>Reporte de auditoría</td>
       <td>Carlos Ruiz</td>
       <td>20/06/2026 10:20</td>
       <td><span class="badge format-pdf">PDF</span></td>
       <td>01/06 - 20/06/2026</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Descargar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
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
