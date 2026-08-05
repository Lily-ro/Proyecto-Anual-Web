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
<title>Mantenimientos - EVA</title>
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
   <li class="anim-slide6"><a href="sensores.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4m0 14v4m-7.07-15.07l2.83 2.83m8.48 8.48l2.83 2.83M1 12h4m14 0h4m-15.07 7.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg><span>Sensores</span></a></li>
   <li class="open anim-slide7" data-toggle="mant"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span><span class="arrow">&#9654;</span></li>
   <ul class="sub-menu open"><li><a href="mantenimientos.php" style="font-weight:600">Lista de mantenimientos</a></li></ul>
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
  <div class="header-left"><div class="header-greeting">Mantenimientos</div><div class="header-subtitle">Lista de mantenimientos asignados</div></div>
  <div class="header-right">
   <button class="bell-btn" title="Notificaciones"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span class="bell-badge">3</span></button>
   <button class="theme-btn" id="themeToggle" title="Cambiar tema"><svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg><svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
   <div class="user-dropdown" id="userDropdown"><div class="user-info"><div class="user-details"><div class="user-name">Tecnico</div><div class="user-role">Soporte</div></div><div class="user-avatar">T</div><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div><div class="user-menu hidden" id="userMenu"><a class="user-menu-item" href="perfil.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Mi perfil</span></a></div></div>
  </div>
 </header>

 <div class="content">
  <div class="stats-row anim-bounce0">
   <div class="stat-card">
    <div class="stat-card-icon blue"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">Total</div><div class="stat-card-value">5</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon orange"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">En progreso</div><div class="stat-card-value">2</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">Completados</div><div class="stat-card-value">2</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-card-icon cyan"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
    <div class="stat-card-info"><div class="stat-card-title">Pendientes</div><div class="stat-card-value">1</div></div>
   </div>
  </div>

  <div class="filtros-bar anim-bounce1">
   <input class="form-input" type="text" placeholder="Buscar mantenimiento..." id="busquedaMant" oninput="filtrarMantenimientos()">
   <select class="form-select" id="filtroEstadoMant" onchange="filtrarMantenimientos()">
    <option value="">Todos</option>
    <option value="pendiente">Pendiente</option>
    <option value="en-progreso">En progreso</option>
    <option value="completado">Completado</option>
   </select>
   <select class="form-select" id="filtroTipoMant" onchange="filtrarMantenimientos()">
    <option value="">Todos</option>
    <option value="preventivo">Preventivo</option>
    <option value="correctivo">Correctivo</option>
    <option value="emergencia">Emergencia</option>
   </select>
  </div>

  <div class="mant-lista" id="listaMantenimientos">
   <div class="mant-card anim-bounce2" data-estado="en-progreso" data-tipo="preventivo">
    <div class="mant-card-header" onclick="toggleMantCard(this)">
     <div class="mant-card-info">
      <div class="mant-card-titulo">Sensor Ultrasonico 01</div>
      <div class="mant-card-meta">
       <span>Tanque Norte</span><span>Preventivo</span><span>15/07/2026</span>
      </div>
     </div>
     <span class="badge pendiente">En progreso</span>
    </div>
    <div class="mant-card-body">
     <div class="mant-acciones">
      <button class="btn btn-primary" onclick="iniciarMant('Sensor Ultrasonico 01')">Iniciar</button>
      <button class="btn btn-outline" onclick="pausarMant('Sensor Ultrasonico 01')">Pausar</button>
      <button class="btn btn-outline" onclick="finalizarMant('Sensor Ultrasonico 01')">Finalizar</button>
      <button class="btn btn-outline" onclick="abrirObservacionesMant('Sensor Ultrasonico 01')">Observaciones</button>
     </div>
     <div class="mant-registros">
      <div class="form-row">
       <div class="form-group"><label class="form-label">Tiempo empleado (min)</label><input class="form-input" type="number" placeholder="0"></div>
       <div class="form-group"><label class="form-label">Materiales utilizados</label><input class="form-input" type="text" placeholder="Ej: Juntas, filtros..."></div>
      </div>
      <div class="form-row">
       <div class="form-group"><label class="form-label">Cambio de bateria</label><select class="form-select"><option>No</option><option>Si</option></select></div>
       <div class="form-group"><label class="form-label">Cambio de sensor</label><select class="form-select"><option>No</option><option>Si</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Limpieza del equipo</label><select class="form-select"><option>No realizada</option><option>Realizada</option></select></div>
      <div class="form-group"><label class="form-label">Subir fotos (antes/despues)</label><input class="form-input" type="file" multiple accept="image/*"></div>
      <div class="form-group"><label class="form-label">Dispositivo operativo</label><select class="form-select"><option>Si, quedo operativo</option><option>No, requiere seguimiento</option></select></div>
      <button class="btn btn-primary" onclick="guardarRegistroMant(this)">Guardar registro</button>
     </div>
    </div>
   </div>

   <div class="mant-card anim-bounce3" data-estado="pendiente" data-tipo="correctivo">
    <div class="mant-card-header" onclick="toggleMantCard(this)">
     <div class="mant-card-info">
      <div class="mant-card-titulo">Sensor de Presion 02</div>
      <div class="mant-card-meta">
       <span>Tanque Centro</span><span>Correctivo</span><span>18/07/2026</span>
      </div>
     </div>
     <span class="badge programado">Pendiente</span>
    </div>
    <div class="mant-card-body">
     <div class="mant-acciones">
      <button class="btn btn-primary" onclick="iniciarMant('Sensor de Presion 02')">Iniciar</button>
      <button class="btn btn-outline" onclick="pausarMant('Sensor de Presion 02')">Pausar</button>
      <button class="btn btn-outline" onclick="finalizarMant('Sensor de Presion 02')">Finalizar</button>
      <button class="btn btn-outline" onclick="abrirObservacionesMant('Sensor de Presion 02')">Observaciones</button>
     </div>
     <div class="mant-registros">
      <div class="form-row">
       <div class="form-group"><label class="form-label">Tiempo empleado (min)</label><input class="form-input" type="number" placeholder="0"></div>
       <div class="form-group"><label class="form-label">Materiales utilizados</label><input class="form-input" type="text" placeholder="Ej: Juntas, filtros..."></div>
      </div>
      <div class="form-row">
       <div class="form-group"><label class="form-label">Cambio de bateria</label><select class="form-select"><option>No</option><option>Si</option></select></div>
       <div class="form-group"><label class="form-label">Cambio de sensor</label><select class="form-select"><option>No</option><option>Si</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Limpieza del equipo</label><select class="form-select"><option>No realizada</option><option>Realizada</option></select></div>
      <div class="form-group"><label class="form-label">Subir fotos (antes/despues)</label><input class="form-input" type="file" multiple accept="image/*"></div>
      <div class="form-group"><label class="form-label">Dispositivo operativo</label><select class="form-select"><option>Si, quedo operativo</option><option>No, requiere seguimiento</option></select></div>
      <button class="btn btn-primary" onclick="guardarRegistroMant(this)">Guardar registro</button>
     </div>
    </div>
   </div>

   <div class="mant-card anim-bounce0" data-estado="completado" data-tipo="preventivo">
    <div class="mant-card-header" onclick="toggleMantCard(this)">
     <div class="mant-card-info">
      <div class="mant-card-titulo">Sensor de Nivel 03</div>
      <div class="mant-card-meta">
       <span>Tanque Sur</span><span>Preventivo</span><span>10/07/2026</span>
      </div>
     </div>
     <span class="badge completado">Completado</span>
    </div>
    <div class="mant-card-body">
     <div class="mant-acciones">
      <button class="btn btn-primary" onclick="iniciarMant('Sensor de Nivel 03')">Iniciar</button>
      <button class="btn btn-outline" onclick="pausarMant('Sensor de Nivel 03')">Pausar</button>
      <button class="btn btn-outline" onclick="finalizarMant('Sensor de Nivel 03')">Finalizar</button>
      <button class="btn btn-outline" onclick="abrirObservacionesMant('Sensor de Nivel 03')">Observaciones</button>
     </div>
     <div class="mant-registros">
      <div class="form-row">
       <div class="form-group"><label class="form-label">Tiempo empleado (min)</label><input class="form-input" type="number" placeholder="0"></div>
       <div class="form-group"><label class="form-label">Materiales utilizados</label><input class="form-input" type="text" placeholder="Ej: Juntas, filtros..."></div>
      </div>
      <div class="form-row">
       <div class="form-group"><label class="form-label">Cambio de bateria</label><select class="form-select"><option>No</option><option>Si</option></select></div>
       <div class="form-group"><label class="form-label">Cambio de sensor</label><select class="form-select"><option>No</option><option>Si</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Limpieza del equipo</label><select class="form-select"><option>No realizada</option><option>Realizada</option></select></div>
      <div class="form-group"><label class="form-label">Subir fotos (antes/despues)</label><input class="form-input" type="file" multiple accept="image/*"></div>
      <div class="form-group"><label class="form-label">Dispositivo operativo</label><select class="form-select"><option>Si, quedo operativo</option><option>No, requiere seguimiento</option></select></div>
      <button class="btn btn-primary" onclick="guardarRegistroMant(this)">Guardar registro</button>
     </div>
    </div>
   </div>

   <div class="mant-card anim-bounce1" data-estado="en-progreso" data-tipo="emergencia">
    <div class="mant-card-header" onclick="toggleMantCard(this)">
     <div class="mant-card-info">
      <div class="mant-card-titulo">Sensor de Flujo 06</div>
      <div class="mant-card-meta">
       <span>Tanque Norte</span><span>Emergencia</span><span>12/07/2026</span>
      </div>
     </div>
     <span class="badge pendiente">En progreso</span>
    </div>
    <div class="mant-card-body">
     <div class="mant-acciones">
      <button class="btn btn-primary" onclick="iniciarMant('Sensor de Flujo 06')">Iniciar</button>
      <button class="btn btn-outline" onclick="pausarMant('Sensor de Flujo 06')">Pausar</button>
      <button class="btn btn-outline" onclick="finalizarMant('Sensor de Flujo 06')">Finalizar</button>
      <button class="btn btn-outline" onclick="abrirObservacionesMant('Sensor de Flujo 06')">Observaciones</button>
     </div>
     <div class="mant-registros">
      <div class="form-row">
       <div class="form-group"><label class="form-label">Tiempo empleado (min)</label><input class="form-input" type="number" placeholder="0"></div>
       <div class="form-group"><label class="form-label">Materiales utilizados</label><input class="form-input" type="text" placeholder="Ej: Juntas, filtros..."></div>
      </div>
      <div class="form-row">
       <div class="form-group"><label class="form-label">Cambio de bateria</label><select class="form-select"><option>No</option><option>Si</option></select></div>
       <div class="form-group"><label class="form-label">Cambio de sensor</label><select class="form-select"><option>No</option><option>Si</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Limpieza del equipo</label><select class="form-select"><option>No realizada</option><option>Realizada</option></select></div>
      <div class="form-group"><label class="form-label">Subir fotos (antes/despues)</label><input class="form-input" type="file" multiple accept="image/*"></div>
      <div class="form-group"><label class="form-label">Dispositivo operativo</label><select class="form-select"><option>Si, quedo operativo</option><option>No, requiere seguimiento</option></select></div>
      <button class="btn btn-primary" onclick="guardarRegistroMant(this)">Guardar registro</button>
     </div>
    </div>
   </div>

   <div class="mant-card anim-bounce2" data-estado="completado" data-tipo="preventivo">
    <div class="mant-card-header" onclick="toggleMantCard(this)">
     <div class="mant-card-info">
      <div class="mant-card-titulo">Sensor de Temperatura 04</div>
      <div class="mant-card-meta">
       <span>Tanque Este</span><span>Preventivo</span><span>05/07/2026</span>
      </div>
     </div>
     <span class="badge completado">Completado</span>
    </div>
    <div class="mant-card-body">
     <div class="mant-acciones">
      <button class="btn btn-primary" onclick="iniciarMant('Sensor de Temperatura 04')">Iniciar</button>
      <button class="btn btn-outline" onclick="pausarMant('Sensor de Temperatura 04')">Pausar</button>
      <button class="btn btn-outline" onclick="finalizarMant('Sensor de Temperatura 04')">Finalizar</button>
      <button class="btn btn-outline" onclick="abrirObservacionesMant('Sensor de Temperatura 04')">Observaciones</button>
     </div>
     <div class="mant-registros">
      <div class="form-row">
       <div class="form-group"><label class="form-label">Tiempo empleado (min)</label><input class="form-input" type="number" placeholder="0"></div>
       <div class="form-group"><label class="form-label">Materiales utilizados</label><input class="form-input" type="text" placeholder="Ej: Juntas, filtros..."></div>
      </div>
      <div class="form-row">
       <div class="form-group"><label class="form-label">Cambio de bateria</label><select class="form-select"><option>No</option><option>Si</option></select></div>
       <div class="form-group"><label class="form-label">Cambio de sensor</label><select class="form-select"><option>No</option><option>Si</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Limpieza del equipo</label><select class="form-select"><option>No realizada</option><option>Realizada</option></select></div>
      <div class="form-group"><label class="form-label">Subir fotos (antes/despues)</label><input class="form-input" type="file" multiple accept="image/*"></div>
      <div class="form-group"><label class="form-label">Dispositivo operativo</label><select class="form-select"><option>Si, quedo operativo</option><option>No, requiere seguimiento</option></select></div>
      <button class="btn btn-primary" onclick="guardarRegistroMant(this)">Guardar registro</button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <div class="modal-overlay hidden" id="modalObservacionesMant">
  <div class="modal">
   <div class="modal-header">
    <h2>Observaciones</h2>
    <button class="modal-close" onclick="cerrarModal('modalObservacionesMant')">&times;</button>
   </div>
   <div class="modal-body">
    <div class="form-group">
     <label class="form-label">Observaciones del mantenimiento</label>
     <textarea class="form-input" id="textoObservacionesMant" rows="5" placeholder="Escribe las observaciones..."></textarea>
    </div>
   </div>
   <div class="modal-footer">
    <button class="btn btn-outline" onclick="cerrarModal('modalObservacionesMant')">Cancelar</button>
    <button class="btn btn-primary" onclick="guardarObservacionesMant()">Guardar</button>
   </div>
  </div>
 </div>
</div>

<script src="js/tecnico.js"></script>
</body>
</html>
