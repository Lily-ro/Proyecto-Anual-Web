<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'instalaciones';
$pageSubtitle = 'Gestión de instalaciones';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Instalaciones</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Instalaciones</h2>
   <p class="page-desc">Gestión de instalaciones de sistemas EVA.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" placeholder="Buscar instalación...">
     </div>
     <div class="filter-group">
      <select class="filter-input">
       <option value="">Todos los estados</option>
       <option value="programada">Programada</option>
       <option value="en_curso">En curso</option>
       <option value="completada">Completada</option>
       <option value="cancelada">Cancelada</option>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary">+ Nueva Instalación</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead>
      <tr>
       <th>ID</th>
       <th>Descripción</th>
       <th>Edificio</th>
       <th>Tanque</th>
       <th>Fecha programada</th>
       <th>Técnico</th>
       <th>Estado</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <tr>
       <td>INS-001</td>
       <td>Instalación sensor ultrasónico</td>
       <td>Sede Principal Norte</td>
       <td>Tanque Norte</td>
       <td>16/07/2026</td>
       <td>Juan Pérez</td>
       <td><span class="badge en_camino">En curso</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>INS-002</td>
       <td>Instalación sistema completo EVA</td>
       <td>Planta Industrial Sur</td>
       <td>Tanque Sur</td>
       <td>20/07/2026</td>
       <td>Roberto Sánchez</td>
       <td><span class="badge pendiente">Programada</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>INS-003</td>
       <td>Instalación sensor de presión</td>
       <td>Oficinas Centrales</td>
       <td>Tanque Centro</td>
       <td>01/07/2026</td>
       <td>María Gómez</td>
       <td><span class="badge completada">Completada</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
      <tr>
       <td>INS-004</td>
       <td>Instalación sensor de temperatura</td>
       <td>Sede Principal Norte</td>
       <td>Tanque Norte</td>
       <td>15/06/2026</td>
       <td>Juan Pérez</td>
       <td><span class="badge completada">Completada</span></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
       </td>
      </tr>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>