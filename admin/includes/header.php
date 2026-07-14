<?php
if (!isset($pageTitle)) $pageTitle = 'Panel de Administración';
if (!isset($pageSubtitle)) $pageSubtitle = 'Resumen general del sistema';
?>
<header class="header">
 <div class="header-left">
  <div class="header-greeting">¡Hola, <?php echo $_SESSION['nombre'] ?? 'Administrador'; ?>!</div>
  <div class="header-subtitle"><?php echo $pageSubtitle; ?></div>
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
    <a class="user-menu-item" href="reportes.php">
     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
     <span>Reportes</span>
    </a>
   </div>
  </div>
 </div>
</header>
