<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'roles';
$pageSubtitle = 'Configura los permisos disponibles para cada tipo de usuario del sistema.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Roles y Permisos</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Roles y permisos</h2>
   <p class="page-desc">Configura los permisos disponibles para cada tipo de usuario del sistema.</p>
  </div>

  <div class="roles-grid">
   <div class="role-card" id="roleCardCliente" onclick="seleccionarRol('Cliente')">
    <div class="role-card-icon role-card-icon-cliente">
     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <h3 class="role-card-title">Cliente</h3>
    <p class="role-card-desc">Permisos de consulta y visualización</p>
   </div>
   <div class="role-card" id="roleCardTecnico" onclick="seleccionarRol('Tecnico')">
    <div class="role-card-icon role-card-icon-tecnico">
     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
    </div>
    <h3 class="role-card-title">Técnico</h3>
    <p class="role-card-desc">Consulta, mantenimiento e historial</p>
   </div>
  </div>

  <div class="permissions-panel" id="permissionsPanel" style="display:none">
   <div class="permissions-header">
    <h3 class="permissions-title" id="permissionsTitle">Permisos de Cliente</h3>
    <p class="permissions-desc" id="permissionsDesc">Selecciona los permisos que tendrán los usuarios con este rol.</p>
   </div>
   <div class="permissions-grid">
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Ver panel principal</span><input type="checkbox" class="switch-input" data-perm="ver_panel" checked><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Ver nivel de tanques</span><input type="checkbox" class="switch-input" data-perm="ver_tanques" checked><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Recibir alertas</span><input type="checkbox" class="switch-input" data-perm="recibir_alertas" checked><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Gestionar usuarios</span><input type="checkbox" class="switch-input" data-perm="gestionar_usuarios"><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Editar usuarios</span><input type="checkbox" class="switch-input" data-perm="editar_usuarios"><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Eliminar usuarios</span><input type="checkbox" class="switch-input" data-perm="eliminar_usuarios"><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Registrar mantenimiento</span><input type="checkbox" class="switch-input" data-perm="registrar_mant"><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Ver historial</span><input type="checkbox" class="switch-input" data-perm="ver_historial" checked><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Exportar datos</span><input type="checkbox" class="switch-input" data-perm="exportar_datos"><span class="switch-slider"></span></label>
    </div>
    <div class="permission-item">
     <label class="switch-label"><span class="switch-text">Configuración</span><input type="checkbox" class="switch-input" data-perm="configuracion"><span class="switch-slider"></span></label>
    </div>
   </div>
   <div class="permissions-actions">
    <button class="btn btn-primary" onclick="guardarPermisos()">Guardar cambios</button>
   </div>
  </div>
 </div>
</div>

<script>
var permisosPorRol = {
 Cliente: { ver_panel: true, ver_tanques: true, recibir_alertas: true, gestionar_usuarios: false, editar_usuarios: false, eliminar_usuarios: false, registrar_mant: false, ver_historial: true, exportar_datos: false, configuracion: false },
 Tecnico: { ver_panel: true, ver_tanques: true, recibir_alertas: true, gestionar_usuarios: false, editar_usuarios: false, eliminar_usuarios: false, registrar_mant: true, ver_historial: true, exportar_datos: true, configuracion: false }
};
var rolSeleccionado = null;

function seleccionarRol(rol) {
 rolSeleccionado = rol;
 document.querySelectorAll('.role-card').forEach(function(c) { c.classList.remove('active'); });
 document.getElementById('roleCard' + rol).classList.add('active');
 document.getElementById('permissionsTitle').textContent = 'Permisos de ' + (rol === 'Cliente' ? 'Cliente' : 'Técnico');
 document.getElementById('permissionsDesc').textContent = 'Selecciona los permisos que tendrán los usuarios con este rol.';
 document.getElementById('permissionsPanel').style.display = '';
 var permisos = permisosPorRol[rol];
 document.querySelectorAll('.switch-input').forEach(function(sw) {
  sw.checked = permisos[sw.dataset.perm] || false;
 });
}

function guardarPermisos() {
 if (!rolSeleccionado) return;
 var permisos = {};
 document.querySelectorAll('.switch-input').forEach(function(sw) {
  permisos[sw.dataset.perm] = sw.checked;
 });
 permisosPorRol[rolSeleccionado] = permisos;
 alert('Permisos de ' + (rolSeleccionado === 'Cliente' ? 'Cliente' : 'Técnico') + ' guardados (funcionalidad pendiente de backend)');
}
</script>
</body>
</html>
