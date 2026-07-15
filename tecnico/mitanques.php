<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'TECNICO'){
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Mis Tanques</title>
<link rel="stylesheet" href="css/tecnico.css">
</head>
<body>
<aside class="sidebar">
 <a href="indextec.php" class="sidebar-logo">
  <svg class="logo-svg" width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg">
   <circle cx="26.9785" cy="43.3208" r="3" fill="#3C75C6"/>
   <path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#3C75C6"/>
  </svg>
  <span>EVA</span>
 </a>
 <div class="sidebar-role">Técnico</div>
 <nav>
  <ul>
   <li class="anim-slide1"><a href="indextec.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>Inicio</span></a></li>
   <li class="anim-slide2"><a href="misdispositivos.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg><span>Mis dispositivos</span></a></li>
   <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
   <li class="anim-slide4"><a href="mediciones.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span>Mediciones</span></a></li>
   <li class="active anim-slide5"><a href="mitanques.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v1a3 3 0 006 0V7m0 0H9m6 0h6M3 7l1.5-4h15L21 7M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7"/></svg><span>Mis tanques</span></a></li>
   <li class="anim-slide6"><a href="sensores.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4m0 14v4m-7.07-15.07l2.83 2.83m8.48 8.48l2.83 2.83M1 12h4m14 0h4m-15.07 7.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg><span>Sensores</span></a></li>
   <li class="anim-slide7" data-toggle="mant"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span><span class="arrow">▶</span></li>
   <ul class="sub-menu"><li><a href="mantenimientos.php">Lista de mantenimientos</a></li></ul>
   <li class="anim-slide8" data-toggle="inst"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg><span>Instalación</span><span class="arrow">▶</span></li>
   <ul class="sub-menu"><li><a href="instalaciones.php">Lista de instalaciones</a></li></ul>
   <li class="anim-slide9"><a href="historialtecnico.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Histórico Técnico</span></a></li>
   <li class="anim-slide10"><a href="notificaciones.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span>Notificaciones</span></a></li>
   <li class="anim-slide11"><a href="perfil.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Perfil</span></a></li>
  </ul>
 </nav>
 <div class="sidebar-footer"><a href="../config/logout.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Cerrar sesión</span></a></div>
</aside>

<div class="main">
 <header class="header">
  <div class="header-left"><div class="header-greeting">Mis Tanques</div><div class="header-subtitle">Tanques asignados a tu cuenta</div></div>
  <div class="header-right">
   <button class="bell-btn" title="Notificaciones"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span class="bell-badge">3</span></button>
   <button class="theme-btn" id="themeToggle" title="Cambiar tema"><svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg><svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
   <div class="user-dropdown" id="userDropdown"><div class="user-info"><div class="user-details"><div class="user-name">Técnico</div><div class="user-role">Soporte</div></div><div class="user-avatar">T</div><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div><div class="user-menu hidden" id="userMenu"><a class="user-menu-item" href="perfil.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Mi perfil</span></a></div></div>
  </div>
 </header>

 <div class="content">
  <div class="stats-row anim-bounce0">
   <div class="stat-card">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v1a3 3 0 006 0V7m0 0H9m6 0h6M3 7l1.5-4h15L21 7M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-label">Total tanques</div><div class="stat-card-val">4</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-label">Operativos</div><div class="stat-card-val">2</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-label">En mantenimiento</div><div class="stat-card-val">1</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-label">Fuera de servicio</div><div class="stat-card-val">1</div></div>
   </div>
  </div>

  <div class="filtros-bar anim-bounce1">
   <input class="form-input filtros-buscar" type="text" placeholder="Buscar tanques..." id="buscarTanques">
   <select class="form-select filtros-select" id="filtrarEstado">
    <option value="">Todos</option>
    <option value="operativo">Operativo</option>
    <option value="mantenimiento">Mantenimiento</option>
    <option value="fuera-servicio">Fuera de servicio</option>
   </select>
  </div>

  <div class="dispositivos-grid anim-bounce2">
   <div class="dispositivo-card" data-estado="operativo">
    <div class="dispositivo-card-header">
     <div class="dispositivo-card-nombre">Tanque Norte</div>
     <span class="badge completado">Operativo</span>
    </div>
    <div class="dispositivo-card-body">
     <div class="dispositivo-info-grid">
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Capacidad</span><span class="dispositivo-info-val">50,000 L</span></div>
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Edificio</span><span class="dispositivo-info-val">Edificio Central</span></div>
     </div>
     <div class="tank-level">
      <div class="tank-level-header"><span class="tank-level-label">Nivel actual</span><span class="tank-level-pct">73%</span></div>
      <div class="tank-level-bar"><div class="tank-level-fill" style="width:73%"></div></div>
     </div>
    </div>
    <div class="dispositivo-card-footer">
     <button class="btn btn-sm btn-outline" onclick="consultarNivelTanque('Tanque Norte')">Consultar nivel</button>
     <button class="btn btn-sm btn-outline" onclick="abrirHistorialTanque('Tanque Norte')">Ver historial</button>
     <button class="btn btn-sm btn-outline" onclick="verDispositivoTanque('Tanque Norte')">Ver dispositivo</button>
     <button class="btn btn-sm btn-outline" onclick="verSensorTanque('Tanque Norte')">Ver sensor</button>
    </div>
   </div>

   <div class="dispositivo-card" data-estado="mantenimiento">
    <div class="dispositivo-card-header">
     <div class="dispositivo-card-nombre">Tanque Centro</div>
     <span class="badge pendiente">Mantenimiento</span>
    </div>
    <div class="dispositivo-card-body">
     <div class="dispositivo-info-grid">
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Capacidad</span><span class="dispositivo-info-val">75,000 L</span></div>
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Edificio</span><span class="dispositivo-info-val">Edificio Central</span></div>
     </div>
     <div class="tank-level">
      <div class="tank-level-header"><span class="tank-level-label">Nivel actual</span><span class="tank-level-pct">45%</span></div>
      <div class="tank-level-bar"><div class="tank-level-fill" style="width:45%"></div></div>
     </div>
    </div>
    <div class="dispositivo-card-footer">
     <button class="btn btn-sm btn-outline" onclick="consultarNivelTanque('Tanque Centro')">Consultar nivel</button>
     <button class="btn btn-sm btn-outline" onclick="abrirHistorialTanque('Tanque Centro')">Ver historial</button>
     <button class="btn btn-sm btn-outline" onclick="verDispositivoTanque('Tanque Centro')">Ver dispositivo</button>
     <button class="btn btn-sm btn-outline" onclick="verSensorTanque('Tanque Centro')">Ver sensor</button>
    </div>
   </div>

   <div class="dispositivo-card" data-estado="operativo">
    <div class="dispositivo-card-header">
     <div class="dispositivo-card-nombre">Tanque Sur</div>
     <span class="badge completado">Operativo</span>
    </div>
    <div class="dispositivo-card-body">
     <div class="dispositivo-info-grid">
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Capacidad</span><span class="dispositivo-info-val">30,000 L</span></div>
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Edificio</span><span class="dispositivo-info-val">Edificio Norte</span></div>
     </div>
     <div class="tank-level">
      <div class="tank-level-header"><span class="tank-level-label">Nivel actual</span><span class="tank-level-pct">84%</span></div>
      <div class="tank-level-bar"><div class="tank-level-fill" style="width:84%"></div></div>
     </div>
    </div>
    <div class="dispositivo-card-footer">
     <button class="btn btn-sm btn-outline" onclick="consultarNivelTanque('Tanque Sur')">Consultar nivel</button>
     <button class="btn btn-sm btn-outline" onclick="abrirHistorialTanque('Tanque Sur')">Ver historial</button>
     <button class="btn btn-sm btn-outline" onclick="verDispositivoTanque('Tanque Sur')">Ver dispositivo</button>
     <button class="btn btn-sm btn-outline" onclick="verSensorTanque('Tanque Sur')">Ver sensor</button>
    </div>
   </div>

   <div class="dispositivo-card" data-estado="fuera-servicio">
    <div class="dispositivo-card-header">
     <div class="dispositivo-card-nombre">Tanque Este</div>
     <span class="badge danger">Fuera de servicio</span>
    </div>
    <div class="dispositivo-card-body">
     <div class="dispositivo-info-grid">
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Capacidad</span><span class="dispositivo-info-val">60,000 L</span></div>
      <div class="dispositivo-info-item"><span class="dispositivo-info-label">Edificio</span><span class="dispositivo-info-val">Edificio Sur</span></div>
     </div>
     <div class="tank-level">
      <div class="tank-level-header"><span class="tank-level-label">Nivel actual</span><span class="tank-level-pct">12%</span></div>
      <div class="tank-level-bar"><div class="tank-level-fill" style="width:12%"></div></div>
     </div>
    </div>
    <div class="dispositivo-card-footer">
     <button class="btn btn-sm btn-outline" onclick="consultarNivelTanque('Tanque Este')">Consultar nivel</button>
     <button class="btn btn-sm btn-outline" onclick="abrirHistorialTanque('Tanque Este')">Ver historial</button>
     <button class="btn btn-sm btn-outline" onclick="verDispositivoTanque('Tanque Este')">Ver dispositivo</button>
     <button class="btn btn-sm btn-outline" onclick="verSensorTanque('Tanque Este')">Ver sensor</button>
    </div>
   </div>
  </div>
 </div>
</div>

<div class="modal-overlay hidden" id="modalNivelTanque">
 <div class="modal">
  <div class="modal-header">
   <div class="modal-title" id="modalNivelTitulo">Nivel del tanque</div>
   <button class="modal-close" onclick="cerrarModal('modalNivelTanque')">&times;</button>
  </div>
  <div class="modal-body">
   <div class="tank-level" style="margin-bottom:1.5rem">
    <div class="tank-level-header"><span class="tank-level-label">Nivel actual</span><span class="tank-level-pct" id="nivelPctModal">73%</span></div>
    <div class="tank-level-bar" style="height:18px"><div class="tank-level-fill" id="nivelBarModal" style="width:73%"></div></div>
   </div>
   <div class="card-header"><div class="card-title">Lecturas recientes</div></div>
   <table class="table">
    <thead><tr><th>Fecha</th><th>Hora</th><th>Nivel (m)</th><th>Porcentaje</th><th>Litros</th><th>Estado</th></tr></thead>
    <tbody>
     <tr><td>14/07/2026</td><td>09:15</td><td>1.45 m</td><td>73%</td><td>36,500 L</td><td><span class="badge completado">Normal</span></td></tr>
     <tr><td>14/07/2026</td><td>08:30</td><td>1.42 m</td><td>71%</td><td>35,500 L</td><td><span class="badge completado">Normal</span></td></tr>
     <tr><td>14/07/2026</td><td>07:45</td><td>1.38 m</td><td>69%</td><td>34,500 L</td><td><span class="badge completado">Normal</span></td></tr>
     <tr><td>13/07/2026</td><td>22:00</td><td>1.50 m</td><td>75%</td><td>37,500 L</td><td><span class="badge completado">Normal</span></td></tr>
     <tr><td>13/07/2026</td><td>16:00</td><td>1.35 m</td><td>67%</td><td>33,500 L</td><td><span class="badge completado">Normal</span></td></tr>
    </tbody>
   </table>
  </div>
 </div>
</div>

<div class="modal-overlay hidden" id="modalHistorialTanque">
 <div class="modal">
  <div class="modal-header">
   <div class="modal-title" id="modalHistorialTitulo">Historial del tanque</div>
   <button class="modal-close" onclick="cerrarModal('modalHistorialTanque')">&times;</button>
  </div>
  <div class="modal-body">
   <table class="table">
    <thead><tr><th>Fecha</th><th>Actividad</th><th>Detalle</th></tr></thead>
    <tbody>
     <tr><td>12/07/2026</td><td>Mantenimiento preventivo</td><td>Limpieza y calibración de sensor</td></tr>
     <tr><td>28/06/2026</td><td>Revisión general</td><td>Inspección de estructura y válvulas</td></tr>
     <tr><td>15/06/2026</td><td>Reparación</td><td>Reemplazo de sonda ultrasónica</td></tr>
     <tr><td>01/06/2026</td><td>Instalación</td><td>Instalación inicial del sistema EVA</td></tr>
     <tr><td>20/05/2026</td><td>Calibración</td><td>Calibración de sensor de nivel</td></tr>
    </tbody>
   </table>
  </div>
 </div>
</div>

<div class="modal-overlay hidden" id="modalDispositivoTanque">
 <div class="modal">
  <div class="modal-header">
   <div class="modal-title" id="modalDispositivoTitulo">Dispositivo instalado</div>
   <button class="modal-close" onclick="cerrarModal('modalDispositivoTanque')">&times;</button>
  </div>
  <div class="modal-body">
   <div class="dispositivo-info-grid">
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Modelo</span><span class="dispositivo-info-val" id="dispModelo">EVA-US-200</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">N° Serie</span><span class="dispositivo-info-val" id="dispSerial">EVA-US-2024-001</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Firmware</span><span class="dispositivo-info-val" id="dispFirmware">v2.4.1</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Batería</span><span class="dispositivo-info-val" id="dispBateria">87%</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Señal</span><span class="dispositivo-info-val" id="dispSenal">-42 dBm</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Última conexión</span><span class="dispositivo-info-val" id="dispConexion">14/07/2026 08:30</span></div>
   </div>
  </div>
 </div>
</div>

<div class="modal-overlay hidden" id="modalSensorTanque">
 <div class="modal">
  <div class="modal-header">
   <div class="modal-title" id="modalSensorTitulo">Sensor instalado</div>
   <button class="modal-close" onclick="cerrarModal('modalSensorTanque')">&times;</button>
  </div>
  <div class="modal-body">
   <div class="dispositivo-info-grid">
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Modelo</span><span class="dispositivo-info-val" id="sensorModelo">EVA-US-200</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">N° Serie</span><span class="dispositivo-info-val" id="sensorSerial">EVA-US-2024-001</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Estado</span><span class="dispositivo-info-val" id="sensorEstado">Activo</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Precisión</span><span class="dispositivo-info-val" id="sensorPrecision">±1mm</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Fecha instalación</span><span class="dispositivo-info-val" id="sensorFechaInst">01/03/2026</span></div>
    <div class="dispositivo-info-item"><span class="dispositivo-info-label">Fecha calibración</span><span class="dispositivo-info-val" id="sensorFechaCal">12/07/2026</span></div>
   </div>
  </div>
 </div>
</div>

<script src="js/tecnico.js"></script>
</body>
</html>