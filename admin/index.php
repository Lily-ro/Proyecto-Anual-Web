<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
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
<aside class="sidebar">
 <a href="../index.php" class="sidebar-logo">
  <svg class="logo-svg" width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg">
   <circle cx="26.9785" cy="43.3208" r="3" fill="#3C75C6"/>
   <path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#3C75C6"/>
  </svg>
  <span>EVA</span>
 </a>
 <div class="sidebar-role">Administrador</div>
 <nav>
  <ul>
   <li class="active anim-slide1">
    <a href="index.php">
     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
     <span>Inicio</span>
    </a>
   </li>
   <li class="anim-slide2" data-toggle="usuarios">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
    <span>Usuarios</span>
    <span class="arrow">&#9654;</span>
   </li>
   <ul class="sub-menu">
    <li><a href="#">Gestionar usuarios</a></li>
    <li><a href="#">Roles y permisos</a></li>
   </ul>
   <li class="anim-slide3" data-toggle="tecnicos">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
    <span>Técnicos</span>
    <span class="arrow">&#9654;</span>
   </li>
   <ul class="sub-menu">
    <li><a href="#">Asignar instalaciones</a></li>
    <li><a href="#">Supervisar mantenimientos</a></li>
   </ul>
   <li class="anim-slide4" data-toggle="dispositivos">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>
    <span>Dispositivos EVA</span>
    <span class="arrow">&#9654;</span>
   </li>
   <ul class="sub-menu">
    <li><a href="#">Gestionar dispositivos</a></li>
   </ul>
   <li class="anim-slide5">
    <a href="#">
     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
     <span>Estadísticas</span>
    </a>
   </li>
   <li class="anim-slide6">
    <a href="#">
     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
     <span>Historial del sistema</span>
    </a>
   </li>
   <li class="anim-slide7">
    <a href="#">
     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
     <span>Configuración</span>
    </a>
   </li>
  </ul>
 </nav>
 <div class="sidebar-footer">
   <a href="../config/logout.php">
   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
   <span>Cerrar sesión</span>
  </a>
 </div>
</aside>

<div class="main">
 <header class="header">
  <div class="header-left">
   <div class="header-greeting">¡Hola, <?php echo $_SESSION['nombre'] ?? 'Administrador'; ?>!</div>
   <div class="header-subtitle">Resumen general del sistema</div>
  </div>
  <div class="header-right">
   <button class="bell-btn" title="Notificaciones">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
    <span class="bell-badge">3</span>
   </button>
   <button class="theme-btn" id="themeToggle" title="Cambiar tema">
    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    <svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
   </button>
   <div class="user-dropdown" id="userDropdown">
    <div class="user-info">
     <div class="user-details"><div class="user-name">Administrador</div><div class="user-role">Admin</div></div>
     <div class="user-avatar">A</div>
     <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="user-menu hidden" id="userMenu">
     <a class="user-menu-item" href="#">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Mi perfil</span>
     </a>
     <a class="user-menu-item" href="#">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
      <span>Configuración</span>
     </a>
    </div>
   </div>
  </div>
 </header>

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
     <a class="quick-action" href="#">
      <div class="quick-action-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
      <div class="quick-action-text">Asignar instalación</div>
     </a>
     <a class="quick-action" href="#">
      <div class="quick-action-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg></div>
      <div class="quick-action-text">Agregar dispositivo</div>
     </a>
     <a class="quick-action" href="#">
      <div class="quick-action-icon purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
      <div class="quick-action-text">Generar reporte</div>
     </a>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
