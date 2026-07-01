<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Mi Tanque</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<!--BARRA LATERAL-->
<aside class="sidebar">
 <a href="indexcli.php" class="sidebar-logo anim-float">
  <svg class="logo-svg" width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg">
   <circle cx="26.9785" cy="43.3208" r="3" fill="#3C75C6"/>
   <path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#3C75C6"/>
  </svg>
  <span>EVA</span>
 </a>
 <nav>
  <ul>
   <li class="anim-slide1"><a href="indexcli.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Resumen</span></a></li>
   <li class="active anim-slide2"><a href="mitanque.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="4" y1="18" x2="20" y2="18"/><rect x="7" y="12" width="10" height="6" rx="1" fill="currentColor" opacity="0.3"/></svg><span>Mi Tanque</span></a></li>
   <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
   <li class="anim-slide4"><a href="configuracion.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg><span>Configuración</span></a></li>
  </ul>
 </nav>
 <div class="device-status">
  <h4>Dispositivo</h4>
  <div class="status-row">
   <svg class="wifi-icon anim-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
   <span class="status-text">Conectado</span>
  </div>
 </div>
</aside>

<div class="main">
 <header class="header">
  <div class="header-left">
   <button class="menu-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
  </div>
  <div class="header-right">
   <button class="theme-btn" id="themeToggle" title="Cambiar tema">
    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    <svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
   </button>
    <div class="user-dropdown" id="userDropdown">
     <div class="user-info">
      <div class="user-details"><div class="user-name"><?php echo $_SESSION['nombre'] ?? 'Usuario'; ?></div><div class="user-role">Cliente</div></div>
      <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 2)); ?></div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
     </div>
      <div class="user-menu hidden" id="userMenu">
       <a class="user-menu-item" href="perfil.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Mi perfil</span></a>
        <a class="user-menu-item" href="configuracion.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg><span>Configuración</span></a>
        <a class="user-menu-item" href="../config/logout.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Cerrar sesión</span></a>
       </div>
    </div>
  </div>
 </header>

 <div class="view active" id="viewTanque">
  <div class="tank-view-grid">
   <div class="card tank-card anim-bounce0">
    <div class="card-title">Mi tanque Principal</div>
    <div class="tank-visual">
     <div class="tank-body-wrapper anim-glow">
      <svg class="tank-svg" viewBox="0 0 140 220">
       <defs>
        <clipPath id="tankClip"><path d="M16 34c0-4.4 3.6-8 8-8h92c4.4 0 8 3.6 8 8v162c0 4.4-3.6 8-8 8H24c-4.4 0-8-3.6-8-8z"/></clipPath>
        <linearGradient id="waterGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#4fc3f7"/><stop offset="30%" stop-color="#29b6f6"/><stop offset="70%" stop-color="#0288d1"/><stop offset="100%" stop-color="#01579b"/></linearGradient>
        <linearGradient id="glassGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="rgba(255,255,255,0.12)"/><stop offset="40%" stop-color="rgba(255,255,255,0.02)"/><stop offset="100%" stop-color="rgba(255,255,255,0.06)"/></linearGradient>
        <linearGradient id="glassEdge" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="rgba(255,255,255,0.15)"/><stop offset="15%" stop-color="rgba(255,255,255,0.02)"/><stop offset="85%" stop-color="rgba(255,255,255,0.02)"/><stop offset="100%" stop-color="rgba(255,255,255,0.1)"/></linearGradient>
        <filter id="tankShadow"><feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="rgba(0,0,0,0.35)"/></filter>
       </defs>
       <path d="M16 34c0-4.4 3.6-8 8-8h92c4.4 0 8 3.6 8 8v162c0 4.4-3.6 8-8 8H24c-4.4 0-8-3.6-8-8z" fill="#1e2433" stroke="#3a4158" stroke-width="2.5" filter="url(#tankShadow)"/>
       <g clip-path="url(#tankClip)">
        <rect id="waterRect" x="16" y="80" width="108" height="120" fill="url(#waterGrad)"><animate attributeName="y" values="80;78;80" dur="3s" repeatCount="indefinite"/></rect>
        <path d="M16 82 Q40 74 64 82 T112 82 T160 82" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="2"><animate attributeName="d" values="M16 82 Q40 74 64 82 T112 82 T160 82;M16 80 Q40 88 64 80 T112 80 T160 80;M16 78 Q44 70 70 78 T118 78 T160 78;M16 80 Q40 88 64 80 T112 80 T160 80;M16 82 Q40 74 64 82 T112 82 T160 82" dur="4s" repeatCount="indefinite"/></path>
        <rect id="waterShine" x="16" y="78" width="108" height="6" fill="rgba(255,255,255,0.1)" rx="3"><animate attributeName="y" values="78;76;78" dur="3s" repeatCount="indefinite"/><animate attributeName="opacity" values="0.1;0.05;0.1" dur="2s" repeatCount="indefinite"/></rect>
        <circle cx="40" cy="160" r="3.5" fill="rgba(255,255,255,0.2)"><animate attributeName="cy" values="160;60" dur="5s" repeatCount="indefinite"/><animate attributeName="opacity" values="0.2;0" dur="5s" repeatCount="indefinite"/></circle>
        <circle cx="60" cy="180" r="2" fill="rgba(255,255,255,0.15)"><animate attributeName="cy" values="180;55" dur="6s" repeatCount="indefinite"/><animate attributeName="opacity" values="0.15;0" dur="6s" repeatCount="indefinite"/></circle>
        <circle cx="85" cy="150" r="2.5" fill="rgba(255,255,255,0.18)"><animate attributeName="cy" values="150;50" dur="4.5s" repeatCount="indefinite"/><animate attributeName="opacity" values="0.18;0" dur="4.5s" repeatCount="indefinite"/></circle>
        <circle cx="105" cy="170" r="1.8" fill="rgba(255,255,255,0.12)"><animate attributeName="cy" values="170;65" dur="5.5s" repeatCount="indefinite"/><animate attributeName="opacity" values="0.12;0" dur="5.5s" repeatCount="indefinite"/></circle>
        <circle cx="95" cy="160" r="2.8" fill="rgba(79,195,247,0.15)"><animate attributeName="cy" values="160;55" dur="6.5s" repeatCount="indefinite"/><animate attributeName="opacity" values="0.15;0" dur="6.5s" repeatCount="indefinite"/></circle>
       </g>
       <path d="M16 34c0-4.4 3.6-8 8-8h92c4.4 0 8 3.6 8 8v162c0 4.4-3.6 8-8 8H24c-4.4 0-8-3.6-8-8z" fill="url(#glassEdge)" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
       <path d="M20 38c0-2.2 1.8-4 4-4h88c2.2 0 4 1.8 4 4v154c0 2.2-1.8 4-4 4H24c-2.2 0-4-1.8-4-4z" fill="url(#glassGrad)"/>
       <rect x="28" y="16" width="84" height="16" rx="4" fill="#2a3042" stroke="#3a4158" stroke-width="1.5" filter="url(#tankShadow)"/>
       <rect x="50" y="6" width="40" height="14" rx="5" fill="#2a3042" stroke="#3a4158" stroke-width="1.5"/>
       <rect x="56" y="0" width="28" height="8" rx="3" fill="#3a4158" stroke="#4a5068" stroke-width="0.8"/>
       <circle cx="70" cy="10" r="3" fill="#4a5068"/>
       <circle cx="70" cy="10" r="1.5" fill="#7a829a"/>
       <rect x="56" y="22" width="28" height="3" rx="1.5" fill="#4a5068"/>
      </svg>
     </div>
     <div class="tank-data">
      <div class="tank-percent anim-float-slow" id="tankPercent">78%</div>
      <div class="tank-label">Nivel actual</div>
      <div class="tank-volume" id="tankVolume">3.120 L</div>
      <div class="tank-capacity">de 4.000 L</div>
     </div>
    </div>
    <div class="estado-box"><h4>Estado</h4><div class="estado-text" id="estadoText">Normal</div><div class="estado-desc" id="estadoDesc">Todo funciona correctamente</div></div>
    <div class="last-update">Última actualización: <span id="lastUpdate">Hoy: --:--</span></div>
   </div>
   <div class="card gauge-card anim-bounce4">
    <div class="card-title">Temperatuta del agua</div>
    <div class="gauge-wrapper anim-glow-slow">
     <svg class="gauge-svg" viewBox="0 0 260 160">
      <defs><linearGradient id="gaugeGrad" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#42a5f5"/><stop offset="30%" stop-color="#26c6da"/><stop offset="60%" stop-color="#66bb6a"/><stop offset="80%" stop-color="#ffa726"/><stop offset="100%" stop-color="#ef5350"/></linearGradient></defs>
      <path d="M 30 140 A 100 100 0 0 1 230 140" fill="none" stroke="#2a3042" stroke-width="18" stroke-linecap="round"/>
      <path id="gaugeArc" d="M 30 140 A 100 100 0 0 1 230 140" fill="none" stroke="url(#gaugeGrad)" stroke-width="14" stroke-linecap="round" stroke-dasharray="0 314"/>
      <g fill="#7a829a" font-size="9" text-anchor="middle" font-family="Inter, sans-serif"><text x="26" y="142">0</text><text x="32" y="102">10</text><text x="50" y="68">20</text><text x="78" y="44">30</text><text x="110" y="32">40</text><text x="130" y="28">50</text><text x="150" y="32">60</text><text x="182" y="44">70</text><text x="210" y="68">80</text><text x="228" y="102">90</text><text x="234" y="142">100</text></g>
      <g id="gaugeNeedle" transform="rotate(-50 130 140)"><line x1="130" y1="140" x2="130" y2="55" stroke="#e0e4ea" stroke-width="2.5" stroke-linecap="round"/><circle cx="130" cy="140" r="6" fill="#2a3042" stroke="#4a5068" stroke-width="1.5"/><circle cx="130" cy="140" r="3" fill="#e0e4ea"/></g>
     </svg>
     <div class="gauge-value-text" id="gaugeValue">70°</div>
    </div>
   </div>
   <div class="card chart-card anim-bounce5">
    <div class="card-title">Promedio de Temperatura</div>
    <div class="chart-container">
     <div class="chart-y-axis"><span>250</span><span>200</span><span>150</span><span>100</span><span>50</span><span>0</span></div>
     <div class="chart-bars" id="chartBars"></div>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/script.js"></script>
</body>
</html>
