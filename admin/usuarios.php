<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'usuarios';
$pageSubtitle = 'Administra los usuarios registrados en el sistema EVA.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestionar Usuarios</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestionar usuarios</h2>
   <p class="page-desc">Administra los usuarios registrados en el sistema EVA.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" id="userSearch" placeholder="Buscar por nombre o correo...">
     </div>
     <div class="filter-group">
      <select class="filter-input" id="filterRol">
       <option value="">Todos los roles</option>
       <option value="Cliente">Cliente</option>
       <option value="Tecnico">Técnico</option>
      </select>
     </div>
     <div class="filter-group">
      <select class="filter-input" id="filterEstado">
       <option value="">Todos los estados</option>
       <option value="Activo">Activo</option>
       <option value="Inactivo">Inactivo</option>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary" onclick="abrirModalUsuario()">+ Nuevo usuario</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table" id="tablaUsuarios">
     <thead>
      <tr>
       <th>Foto</th>
       <th>Nombre</th>
       <th>Email</th>
       <th>Rol</th>
       <th>Estado</th>
       <th>Último acceso</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <tr data-rol="Cliente" data-estado="Activo" data-nombre="Carlos Méndez" data-email="carlos.mendez@eva.com">
       <td><div class="user-avatar-sm" style="background:linear-gradient(135deg,#2c6cef,#4fc3f7)">CM</div></td>
       <td>Carlos Méndez</td>
       <td>carlos.mendez@eva.com</td>
       <td><span class="badge rol-cliente">Cliente</span></td>
       <td><span class="badge activo">Activo</span></td>
       <td>24/07/2026 10:30</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Editar usuario" onclick="editarUsuario(1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon" title="Cambiar contraseña" onclick="cambiarContrasena(1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></button>
        <button class="btn-icon" title="Desactivar usuario" onclick="toggleEstado(1,'Activo')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar usuario" onclick="eliminarUsuario(1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr data-rol="Tecnico" data-estado="Activo" data-nombre="Laura Gutiérrez" data-email="laura.gutierrez@eva.com">
       <td><div class="user-avatar-sm" style="background:linear-gradient(135deg,#4caf50,#66bb6a)">LG</div></td>
       <td>Laura Gutiérrez</td>
       <td>laura.gutierrez@eva.com</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge activo">Activo</span></td>
       <td>24/07/2026 09:15</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Editar usuario" onclick="editarUsuario(2)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon" title="Cambiar contraseña" onclick="cambiarContrasena(2)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></button>
        <button class="btn-icon" title="Desactivar usuario" onclick="toggleEstado(2,'Activo')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar usuario" onclick="eliminarUsuario(2)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr data-rol="Cliente" data-estado="Activo" data-nombre="Roberto Silva" data-email="roberto.silva@eva.com">
       <td><div class="user-avatar-sm" style="background:linear-gradient(135deg,#ff9800,#ffb74d)">RS</div></td>
       <td>Roberto Silva</td>
       <td>roberto.silva@eva.com</td>
       <td><span class="badge rol-cliente">Cliente</span></td>
       <td><span class="badge activo">Activo</span></td>
       <td>23/07/2026 18:45</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Editar usuario" onclick="editarUsuario(3)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon" title="Cambiar contraseña" onclick="cambiarContrasena(3)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></button>
        <button class="btn-icon" title="Desactivar usuario" onclick="toggleEstado(3,'Activo')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar usuario" onclick="eliminarUsuario(3)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr data-rol="Tecnico" data-estado="Inactivo" data-nombre="Miguel Torres" data-email="miguel.torres@eva.com">
       <td><div class="user-avatar-sm" style="background:linear-gradient(135deg,#f44336,#ef5350)">MT</div></td>
       <td>Miguel Torres</td>
       <td>miguel.torres@eva.com</td>
       <td><span class="badge rol-tecnico">Técnico</span></td>
       <td><span class="badge inactivo">Inactivo</span></td>
       <td>20/07/2026 14:20</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Editar usuario" onclick="editarUsuario(4)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon" title="Cambiar contraseña" onclick="cambiarContrasena(4)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></button>
        <button class="btn-icon" title="Activar usuario" onclick="toggleEstado(4,'Inactivo')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar usuario" onclick="eliminarUsuario(4)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr data-rol="Cliente" data-estado="Activo" data-nombre="Ana Rodríguez" data-email="ana.rodriguez@eva.com">
       <td><div class="user-avatar-sm" style="background:linear-gradient(135deg,#9c27b0,#ba68c8)">AR</div></td>
       <td>Ana Rodríguez</td>
       <td>ana.rodriguez@eva.com</td>
       <td><span class="badge rol-cliente">Cliente</span></td>
       <td><span class="badge activo">Activo</span></td>
       <td>24/07/2026 08:00</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Editar usuario" onclick="editarUsuario(5)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon" title="Cambiar contraseña" onclick="cambiarContrasena(5)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></button>
        <button class="btn-icon" title="Desactivar usuario" onclick="toggleEstado(5,'Activo')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar usuario" onclick="eliminarUsuario(5)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr data-rol="Cliente" data-estado="Inactivo" data-nombre="Pedro López" data-email="pedro.lopez@eva.com">
       <td><div class="user-avatar-sm" style="background:linear-gradient(135deg,#607d8b,#90a4ae)">PL</div></td>
       <td>Pedro López</td>
       <td>pedro.lopez@eva.com</td>
       <td><span class="badge rol-cliente">Cliente</span></td>
       <td><span class="badge inactivo">Inactivo</span></td>
       <td>15/07/2026 11:30</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Editar usuario" onclick="editarUsuario(6)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon" title="Cambiar contraseña" onclick="cambiarContrasena(6)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></button>
        <button class="btn-icon" title="Activar usuario" onclick="toggleEstado(6,'Inactivo')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar usuario" onclick="eliminarUsuario(6)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
     </tbody>
    </table>
   </div>
   <div class="table-footer">
    <div class="table-info">Mostrando <span id="userCount">6</span> usuarios</div>
    <div class="pagination">
     <button class="btn-page" disabled>&laquo;</button>
     <button class="btn-page btn-page-active">1</button>
     <button class="btn-page" disabled>»</button>
    </div>
   </div>
  </div>
 </div>
</div>

<!-- Modal Agregar/Editar Usuario -->
<div class="modal-overlay" id="modalUsuario">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title" id="modalUsuarioTitle">Nuevo usuario</h3>
   <button class="modal-close" onclick="cerrarModalUsuario()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formUsuario">
    <input type="hidden" id="usuarioId" value="">
    <div class="form-group">
     <label class="form-label">Nombre completo</label>
     <input type="text" class="form-input" id="usuarioNombre" placeholder="Ej: Juan Pérez" required>
    </div>
    <div class="form-group">
     <label class="form-label">Correo electrónico</label>
     <input type="email" class="form-input" id="usuarioEmail" placeholder="Ej: juan@eva.com" required>
    </div>
    <div class="form-group" id="grupoContrasena">
     <label class="form-label">Contraseña</label>
     <input type="password" class="form-input" id="usuarioContrasena" placeholder="Mínimo 6 caracteres" required>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Rol</label>
      <select class="form-input" id="usuarioRol" required>
       <option value="">Seleccionar...</option>
       <option value="Cliente">Cliente</option>
       <option value="Tecnico">Técnico</option>
      </select>
     </div>
     <div class="form-group">
      <label class="form-label">Estado</label>
      <select class="form-input" id="usuarioEstado" required>
       <option value="Activo">Activo</option>
       <option value="Inactivo">Inactivo</option>
      </select>
     </div>
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalUsuario()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarUsuario()">Guardar</button>
  </div>
 </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal-overlay" id="modalContrasena">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title">Cambiar contraseña</h3>
   <button class="modal-close" onclick="cerrarModalContrasena()">&times;</button>
  </div>
  <div class="modal-body">
   <input type="hidden" id="contrasenaUserId" value="">
   <div class="form-group">
    <label class="form-label">Nueva contraseña</label>
    <input type="password" class="form-input" id="nuevaContrasena" placeholder="Mínimo 6 caracteres" required>
   </div>
   <div class="form-group">
    <label class="form-label">Confirmar contraseña</label>
    <input type="password" class="form-input" id="confirmarContrasena" placeholder="Repetir contraseña" required>
   </div>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalContrasena()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarContrasena()">Actualizar contraseña</button>
  </div>
 </div>
</div>

<script src="js/admin.js"></script>
</body>
</html>
