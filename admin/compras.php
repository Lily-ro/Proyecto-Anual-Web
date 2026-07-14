<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'compras';
$pageSubtitle = 'Gestión de compras del sistema';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Compras</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Compras</h2>
   <p class="page-desc">Visualizar, aprobar, cancelar y gestionar estados de compras.</p>
  </div>

  <div class="stats-row stats-row-4">
   <div class="stat-card anim-bounce0">
    <div class="stat-card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Pendientes</div>
     <div class="stat-card-value">7</div>
     <div class="stat-card-sub">Requieren aprobación</div>
    </div>
   </div>
   <div class="stat-card anim-bounce1">
    <div class="stat-card-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Aprobadas</div>
     <div class="stat-card-value">23</div>
     <div class="stat-card-sub">Este mes</div>
    </div>
   </div>
   <div class="stat-card anim-bounce2">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">En entrega</div>
     <div class="stat-card-value">5</div>
     <div class="stat-card-sub">En camino</div>
    </div>
   </div>
   <div class="stat-card anim-bounce3">
    <div class="stat-card-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Completadas</div>
     <div class="stat-card-value">45</div>
     <div class="stat-card-sub">Total</div>
    </div>
   </div>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" id="compraSearch" placeholder="Buscar por ID, proveedor...">
     </div>
     <div class="filter-group">
      <select class="filter-input" id="compraEstado">
       <option value="">Todos los estados</option>
       <option value="pendiente">Pendiente</option>
       <option value="aprobada">Aprobada</option>
       <option value="en_camino">En entrega</option>
       <option value="completada">Completada</option>
       <option value="cancelada">Cancelada</option>
      </select>
     </div>
     <div class="filter-group">
      <input type="date" class="filter-input" id="compraFecha">
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary">+ Nueva Compra</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead>
      <tr>
       <th>ID</th>
       <th>Fecha</th>
       <th>Proveedor</th>
       <th>Artículo</th>
       <th>Cantidad</th>
       <th>Total</th>
       <th>Solicitante</th>
       <th>Estado</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <tr>
       <td>COMP-001</td>
       <td>01/07/2026</td>
       <td>TechSensors SA</td>
       <td>Sensor ultrasónico US-200</td>
       <td>10</td>
       <td>$4,500.00</td>
       <td>Juan Pérez</td>
       <td><span class="badge pendiente">Pendiente</span></td>
       <td class="actions-cell">
        <button class="btn btn-sm btn-success" onclick="aprobarCompra('COMP-001')">Aprobar</button>
        <button class="btn btn-sm btn-danger" onclick="cancelarCompra('COMP-001')">Cancelar</button>
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>COMP-002</td>
       <td>30/06/2026</td>
       <td>FlowTech</td>
       <td>Medidor de flujo MF-100</td>
       <td>5</td>
       <td>$2,800.00</td>
       <td>María Gómez</td>
       <td><span class="badge pendiente">Pendiente</span></td>
       <td class="actions-cell">
        <button class="btn btn-sm btn-success" onclick="aprobarCompra('COMP-002')">Aprobar</button>
        <button class="btn btn-sm btn-danger" onclick="cancelarCompra('COMP-002')">Cancelar</button>
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>COMP-003</td>
       <td>28/06/2026</td>
       <td>PressurePro</td>
       <td>Sensor de presión SP-300</td>
       <td>8</td>
       <td>$3,200.00</td>
       <td>Carlos Ruiz</td>
       <td><span class="badge aprobada">Aprobada</span></td>
       <td class="actions-cell">
        <button class="btn btn-sm btn-info" onclick="cambiarEstado('COMP-003', 'en_camino')">Marcar en entrega</button>
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>COMP-004</td>
       <td>25/06/2026</td>
       <td>TechSensors SA</td>
       <td>Sensor de nivel SN-150</td>
       <td>15</td>
       <td>$6,750.00</td>
       <td>Juan Pérez</td>
       <td><span class="badge en_camino">En entrega</span></td>
       <td class="actions-cell">
        <button class="btn btn-sm btn-success" onclick="cambiarEstado('COMP-004', 'completada')">Marcar completada</button>
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>COMP-005</td>
       <td>20/06/2026</td>
       <td>TempSense</td>
       <td>Sensor de temperatura ST-50</td>
       <td>12</td>
       <td>$1,800.00</td>
       <td>Laura Díaz</td>
       <td><span class="badge completada">Completada</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>COMP-006</td>
       <td>18/06/2026</td>
       <td>FlowTech</td>
       <td>Válvula de control VC-200</td>
       <td>3</td>
       <td>$900.00</td>
       <td>Roberto Sánchez</td>
       <td><span class="badge cancelada">Cancelada</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>COMP-007</td>
       <td>15/06/2026</td>
       <td>TechSensors SA</td>
       <td>Cable blindado CB-10</td>
       <td>100</td>
       <td>$1,200.00</td>
       <td>Juan Pérez</td>
       <td><span class="badge completada">Completada</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
     </tbody>
    </table>
   </div>
   <div class="table-footer">
    <div class="table-info">Mostrando 7 de 35 registros</div>
    <div class="pagination">
     <button class="btn btn-page" disabled>&laquo; Anterior</button>
     <button class="btn btn-page btn-page-active">1</button>
     <button class="btn btn-page">2</button>
     <button class="btn btn-page">3</button>
     <button class="btn btn-page">4</button>
     <button class="btn btn-page">5</button>
     <button class="btn btn-page">Siguiente &raquo;</button>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
