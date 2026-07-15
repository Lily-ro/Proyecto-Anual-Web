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
<title>Sensores - EVA</title>
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
 <div class="sidebar-role">Tecnico</div>
 <nav>
  <ul>
   <li class="anim-slide1"><a href="indextec.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>Inicio</span></a></li>
   <li class="anim-slide2"><a href="misdispositivos.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg><span>Mis dispositivos</span></a></li>
   <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
   <li class="anim-slide4"><a href="mediciones.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span>Mediciones</span></a></li>
   <li class="anim-slide5"><a href="mitanques.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v1a3 3 0 006 0V7m0 0H9m6 0h6M3 7l1.5-4h15L21 7M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7"/></svg><span>Mis tanques</span></a></li>
   <li class="active anim-slide6"><a href="sensores.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4m0 14v4m-7.07-15.07l2.83 2.83m8.48 8.48l2.83 2.83M1 12h4m14 0h4m-15.07 7.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg><span>Sensores</span></a></li>
   <li class="anim-slide7" data-toggle="mant"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span><span class="arrow">&#9654;</span></li>
   <ul class="sub-menu"><li><a href="mantenimientos.php">Lista de mantenimientos</a></li></ul>
   <li class="anim-slide8" data-toggle="inst"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg><span>Instalacion</span><span class="arrow">&#9654;</span></li>
   <ul class="sub-menu"><li><a href="instalaciones.php">Lista de instalaciones</a></li></ul>
   <li class="anim-slide9"><a href="historialtecnico.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Historico Tecnico</span></a></li>
   <li class="anim-slide10"><a href="notificaciones.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span>Notificaciones</span></a></li>
   <li class="anim-slide11"><a href="perfil.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Perfil</span></a></li>
  </ul>
 </nav>
 <div class="sidebar-footer"><a href="../config/logout.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Cerrar sesion</span></a></div>
</aside>

<div class="main">
 <header class="header">
  <div class="header-left"><div class="header-greeting">Sensores</div><div class="header-subtitle">Sensores asignados a tu cuenta</div></div>
  <div class="header-right">
   <button class="bell-btn" title="Notificaciones"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span class="bell-badge">3</span></button>
   <button class="theme-btn" id="themeToggle" title="Cambiar tema"><svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg><svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
   <div class="user-dropdown" id="userDropdown"><div class="user-info"><div class="user-details"><div class="user-name">Tecnico</div><div class="user-role">Soporte</div></div><div class="user-avatar">T</div><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div><div class="user-menu hidden" id="userMenu"><a class="user-menu-item" href="perfil.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Mi perfil</span></a></div></div>
  </div>
 </header>

 <div class="content">
  <div class="stats-row anim-bounce0">
   <div class="stat-card">
    <div class="stat-card-icon blue"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4m0 14v4m-7.07-15.07l2.83 2.83m8.48 8.48l2.83 2.83M1 12h4m14 0h4m-15.07 7.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">Total sensores</div><div class="stat-card-value">6</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">Operativos</div><div class="stat-card-value">4</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon orange"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">En reparacion</div><div class="stat-card-value">1</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon cyan"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">Defectuosos</div><div class="stat-card-value">1</div></div>
   </div>
  </div>

  <div class="filtros-bar anim-bounce1">
   <input class="form-input" type="text" placeholder="Buscar sensor..." id="busquedaSensor" oninput="filtrarSensores()">
   <select class="form-select" id="filtroEstado" onchange="filtrarSensores()">
    <option value="">Todos</option>
    <option value="operativo">Operativo</option>
    <option value="reparacion">En reparacion</option>
    <option value="defectuoso">Defectuoso</option>
   </select>
  </div>

  <div class="sensores-grid" id="listaSensores">
   <div class="sensor-card anim-bounce2" data-nombre="Sensor Ultrasonico 01" data-estado="operativo">
    <div class="sensor-card-header">
     <h3 class="sensor-card-nombre">Sensor Ultrasonico 01</h3>
     <span class="badge completado">Operativo</span>
    </div>
    <div class="sensor-card-body">
     <div class="form-row">
      <div class="form-group"><label class="form-label">Modelo</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-US-200</div></div>
      <div class="form-group"><label class="form-label">Serial</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-US-2024-001</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Precision</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">&plusmn;0.5mm</div></div>
      <div class="form-group"><label class="form-label">Fecha instalacion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">01/03/2026</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Ultima calibracion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">15/06/2026</div></div>
     </div>
    </div>
    <div class="sensor-card-footer">
     <button class="btn btn-primary" onclick="registrarCalibracion('Sensor Ultrasonico 01')">Registrar calibracion</button>
     <button class="btn btn-outline" onclick="marcarReparado('Sensor Ultrasonico 01')">Marcar reparado</button>
     <button class="btn btn-outline" onclick="marcarDefectuoso('Sensor Ultrasonico 01')">Marcar defectuoso</button>
     <div class="form-group" style="width:100%;margin-top:12px">
      <textarea class="form-input" rows="2" placeholder="Escribe una observacion..." style="resize:vertical"></textarea>
      <button class="btn btn-outline" style="margin-top:8px" onclick="agregarObservacionSensor(this)">Agregar observaciones</button>
     </div>
    </div>
   </div>

   <div class="sensor-card anim-bounce3" data-nombre="Sensor de Presion 02" data-estado="operativo">
    <div class="sensor-card-header">
     <h3 class="sensor-card-nombre">Sensor de Presion 02</h3>
     <span class="badge completado">Operativo</span>
    </div>
    <div class="sensor-card-body">
     <div class="form-row">
      <div class="form-group"><label class="form-label">Modelo</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-PR-150</div></div>
      <div class="form-group"><label class="form-label">Serial</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-PR-2024-002</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Precision</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">&plusmn;1.0 PSI</div></div>
      <div class="form-group"><label class="form-label">Fecha instalacion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">15/01/2026</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Ultima calibracion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">01/05/2026</div></div>
     </div>
    </div>
    <div class="sensor-card-footer">
     <button class="btn btn-primary" onclick="registrarCalibracion('Sensor de Presion 02')">Registrar calibracion</button>
     <button class="btn btn-outline" onclick="marcarReparado('Sensor de Presion 02')">Marcar reparado</button>
     <button class="btn btn-outline" onclick="marcarDefectuoso('Sensor de Presion 02')">Marcar defectuoso</button>
     <div class="form-group" style="width:100%;margin-top:12px">
      <textarea class="form-input" rows="2" placeholder="Escribe una observacion..." style="resize:vertical"></textarea>
      <button class="btn btn-outline" style="margin-top:8px" onclick="agregarObservacionSensor(this)">Agregar observaciones</button>
     </div>
    </div>
   </div>

   <div class="sensor-card anim-bounce0" data-nombre="Sensor de Nivel 03" data-estado="reparacion">
    <div class="sensor-card-header">
     <h3 class="sensor-card-nombre">Sensor de Nivel 03</h3>
     <span class="badge pendiente">En reparacion</span>
    </div>
    <div class="sensor-card-body">
     <div class="form-row">
      <div class="form-group"><label class="form-label">Modelo</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-NV-300</div></div>
      <div class="form-group"><label class="form-label">Serial</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-NV-2024-003</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Precision</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">&plusmn;2mm</div></div>
      <div class="form-group"><label class="form-label">Fecha instalacion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">20/02/2026</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Ultima calibracion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">10/04/2026</div></div>
     </div>
    </div>
    <div class="sensor-card-footer">
     <button class="btn btn-primary" onclick="registrarCalibracion('Sensor de Nivel 03')">Registrar calibracion</button>
     <button class="btn btn-outline" onclick="marcarReparado('Sensor de Nivel 03')">Marcar reparado</button>
     <button class="btn btn-outline" onclick="marcarDefectuoso('Sensor de Nivel 03')">Marcar defectuoso</button>
     <div class="form-group" style="width:100%;margin-top:12px">
      <textarea class="form-input" rows="2" placeholder="Escribe una observacion..." style="resize:vertical"></textarea>
      <button class="btn btn-outline" style="margin-top:8px" onclick="agregarObservacionSensor(this)">Agregar observaciones</button>
     </div>
    </div>
   </div>

   <div class="sensor-card anim-bounce1" data-nombre="Sensor de Temperatura 04" data-estado="defectuoso">
    <div class="sensor-card-header">
     <h3 class="sensor-card-nombre">Sensor de Temperatura 04</h3>
     <span class="badge" style="background:rgba(244,67,54,0.12);color:#ef5350">Defectuoso</span>
    </div>
    <div class="sensor-card-body">
     <div class="form-row">
      <div class="form-group"><label class="form-label">Modelo</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-TP-100</div></div>
      <div class="form-group"><label class="form-label">Serial</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-TP-2024-004</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Precision</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">&plusmn;0.1&deg;C</div></div>
      <div class="form-group"><label class="form-label">Fecha instalacion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">05/12/2025</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Ultima calibracion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">20/03/2026</div></div>
     </div>
    </div>
    <div class="sensor-card-footer">
     <button class="btn btn-primary" onclick="registrarCalibracion('Sensor de Temperatura 04')">Registrar calibracion</button>
     <button class="btn btn-outline" onclick="marcarReparado('Sensor de Temperatura 04')">Marcar reparado</button>
     <button class="btn btn-outline" onclick="marcarDefectuoso('Sensor de Temperatura 04')">Marcar defectuoso</button>
     <div class="form-group" style="width:100%;margin-top:12px">
      <textarea class="form-input" rows="2" placeholder="Escribe una observacion..." style="resize:vertical"></textarea>
      <button class="btn btn-outline" style="margin-top:8px" onclick="agregarObservacionSensor(this)">Agregar observaciones</button>
     </div>
    </div>
   </div>

   <div class="sensor-card anim-bounce2" data-nombre="Sensor Ultrasonico 05" data-estado="operativo">
    <div class="sensor-card-header">
     <h3 class="sensor-card-nombre">Sensor Ultrasonico 05</h3>
     <span class="badge completado">Operativo</span>
    </div>
    <div class="sensor-card-body">
     <div class="form-row">
      <div class="form-group"><label class="form-label">Modelo</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-US-200</div></div>
      <div class="form-group"><label class="form-label">Serial</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-US-2024-005</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Precision</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">&plusmn;0.5mm</div></div>
      <div class="form-group"><label class="form-label">Fecha instalacion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">10/03/2026</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Ultima calibracion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">01/06/2026</div></div>
     </div>
    </div>
    <div class="sensor-card-footer">
     <button class="btn btn-primary" onclick="registrarCalibracion('Sensor Ultrasonico 05')">Registrar calibracion</button>
     <button class="btn btn-outline" onclick="marcarReparado('Sensor Ultrasonico 05')">Marcar reparado</button>
     <button class="btn btn-outline" onclick="marcarDefectuoso('Sensor Ultrasonico 05')">Marcar defectuoso</button>
     <div class="form-group" style="width:100%;margin-top:12px">
      <textarea class="form-input" rows="2" placeholder="Escribe una observacion..." style="resize:vertical"></textarea>
      <button class="btn btn-outline" style="margin-top:8px" onclick="agregarObservacionSensor(this)">Agregar observaciones</button>
     </div>
    </div>
   </div>

   <div class="sensor-card anim-bounce3" data-nombre="Sensor de Flujo 06" data-estado="operativo">
    <div class="sensor-card-header">
     <h3 class="sensor-card-nombre">Sensor de Flujo 06</h3>
     <span class="badge completado">Operativo</span>
    </div>
    <div class="sensor-card-body">
     <div class="form-row">
      <div class="form-group"><label class="form-label">Modelo</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-FL-250</div></div>
      <div class="form-group"><label class="form-label">Serial</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">EVA-FL-2024-006</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Precision</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">&plusmn;0.5 L/min</div></div>
      <div class="form-group"><label class="form-label">Fecha instalacion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">28/01/2026</div></div>
     </div>
     <div class="form-row">
      <div class="form-group"><label class="form-label">Ultima calibracion</label><div class="form-input" style="background:transparent;border:none;padding:0;color:var(--tx)">15/05/2026</div></div>
     </div>
    </div>
    <div class="sensor-card-footer">
     <button class="btn btn-primary" onclick="registrarCalibracion('Sensor de Flujo 06')">Registrar calibracion</button>
     <button class="btn btn-outline" onclick="marcarReparado('Sensor de Flujo 06')">Marcar reparado</button>
     <button class="btn btn-outline" onclick="marcarDefectuoso('Sensor de Flujo 06')">Marcar defectuoso</button>
     <div class="form-group" style="width:100%;margin-top:12px">
      <textarea class="form-input" rows="2" placeholder="Escribe una observacion..." style="resize:vertical"></textarea>
      <button class="btn btn-outline" style="margin-top:8px" onclick="agregarObservacionSensor(this)">Agregar observaciones</button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <div class="modal-overlay hidden" id="modalCalibrar">
  <div class="modal">
   <div class="modal-header">
    <h2>Registrar calibracion</h2>
    <button class="modal-close" onclick="cerrarModal('modalCalibrar')">&times;</button>
   </div>
   <div class="modal-body">
    <div class="form-group">
     <label class="form-label">Fecha de calibracion</label>
     <input type="date" class="form-input">
    </div>
    <div class="form-group">
     <label class="form-label">Notas</label>
     <textarea class="form-input" rows="4" placeholder="Observaciones de la calibracion..."></textarea>
    </div>
   </div>
   <div class="modal-footer">
    <button class="btn btn-outline" onclick="cerrarModal('modalCalibrar')">Cancelar</button>
    <button class="btn btn-primary" onclick="guardarCalibracion()">Registrar calibracion</button>
   </div>
  </div>
 </div>
</div>

<script src="js/tecnico.js"></script>
</body>
</html>
