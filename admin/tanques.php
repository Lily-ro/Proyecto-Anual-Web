<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'tanques';
$pageSubtitle = 'Gestión completa de tanques';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Tanques</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Tanques</h2>
   <p class="page-desc">ABM completo de tanques del sistema.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" placeholder="Buscar tanque...">
     </div>
     <div class="filter-group">
      <select class="filter-input">
       <option value="">Todas las ubicaciones</option>
       <option value="norte">Norte</option>
       <option value="centro">Centro</option>
       <option value="sur">Sur</option>
       <option value="este">Este</option>
       <option value="oeste">Oeste</option>
      </select>
     </div>
     <div class="filter-group">
      <select class="filter-input">
       <option value="">Todos los estados</option>
       <option value="activo">Activo</option>
       <option value="mantenimiento">En mantenimiento</option>
       <option value="inactivo">Inactivo</option>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary" onclick="abrirModalTanque()">+ Agregar Tanque</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead>
      <tr>
       <th>ID</th>
       <th>Nombre</th>
       <th>Ubicación</th>
       <th>Capacidad (L)</th>
       <th>Nivel actual</th>
       <th>Estado</th>
       <th>Sensores</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <tr>
       <td>T-001</td>
       <td>Tanque Norte Principal</td>
       <td>Zona Norte</td>
       <td>50,000</td>
       <td>
        <div class="level-bar">
         <div class="level-fill" style="width:85%"></div>
        </div>
        <span class="level-text">85%</span>
       </td>
       <td><span class="badge activo">Activo</span></td>
       <td>4</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar" onclick="editarTanque('T-001')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarTanque('T-001')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>T-002</td>
       <td>Tanque Centro</td>
       <td>Zona Centro</td>
       <td>35,000</td>
       <td>
        <div class="level-bar">
         <div class="level-fill level-medio" style="width:60%"></div>
        </div>
        <span class="level-text">60%</span>
       </td>
       <td><span class="badge activo">Activo</span></td>
       <td>3</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar" onclick="editarTanque('T-002')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarTanque('T-002')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>T-003</td>
       <td>Tanque Sur</td>
       <td>Zona Sur</td>
       <td>40,000</td>
       <td>
        <div class="level-bar">
         <div class="level-fill level-bajo" style="width:25%"></div>
        </div>
        <span class="level-text">25%</span>
       </td>
       <td><span class="badge advertencia">Advertencia</span></td>
       <td>3</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar" onclick="editarTanque('T-003')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarTanque('T-003')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>T-004</td>
       <td>Tanque Este Reserva</td>
       <td>Zona Este</td>
       <td>20,000</td>
       <td>
        <div class="level-bar">
         <div class="level-fill" style="width:0%"></div>
        </div>
        <span class="level-text">0%</span>
       </td>
       <td><span class="badge inactivo">Inactivo</span></td>
       <td>0</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar" onclick="editarTanque('T-004')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarTanque('T-004')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>T-005</td>
       <td>Tanque Oeste Industrial</td>
       <td>Zona Oeste</td>
       <td>60,000</td>
       <td>
        <div class="level-bar">
         <div class="level-fill" style="width:92%"></div>
        </div>
        <span class="level-text">92%</span>
       </td>
       <td><span class="badge activo">Activo</span></td>
       <td>5</td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar" onclick="editarTanque('T-005')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarTanque('T-005')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>

<!-- Modal Agregar/Editar Tanque -->
<div class="modal-overlay" id="modalTanque">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title" id="modalTanqueTitle">Agregar Tanque</h3>
   <button class="modal-close" onclick="cerrarModalTanque()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formTanque">
    <input type="hidden" id="tanqueId" value="">
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Nombre</label>
      <input type="text" class="form-input" id="tanqueNombre" placeholder="Ej: Tanque Norte Principal" required>
     </div>
     <div class="form-group">
      <label class="form-label">Ubicación</label>
      <select class="form-input" id="tanqueUbicacion" required>
       <option value="">Seleccionar...</option>
       <option value="Zona Norte">Zona Norte</option>
       <option value="Zona Centro">Zona Centro</option>
       <option value="Zona Sur">Zona Sur</option>
       <option value="Zona Este">Zona Este</option>
       <option value="Zona Oeste">Zona Oeste</option>
      </select>
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Capacidad (litros)</label>
      <input type="number" class="form-input" id="tanqueCapacidad" placeholder="Ej: 50000" required>
     </div>
     <div class="form-group">
      <label class="form-label">Estado</label>
      <select class="form-input" id="tanqueEstado" required>
       <option value="activo">Activo</option>
       <option value="mantenimiento">En mantenimiento</option>
       <option value="inactivo">Inactivo</option>
      </select>
     </div>
    </div>
    <div class="form-group">
     <label class="form-label">Descripción</label>
     <textarea class="form-input" id="tanqueDescripcion" rows="3" placeholder="Descripción del tanque..."></textarea>
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalTanque()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarTanque()">Guardar</button>
  </div>
 </div>
</div>

<script src="js/admin.js"></script>
</body>
</html>
