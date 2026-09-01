# AGENTS.md — EVA (El Vigilante del Agua)

## Proyecto

Sistema de monitoreo de tanques de agua. PHP vanilla + MariaDB. Sin framework, sin composer, sin npm.

Tres paneles independientes que comparten `config/db.php`:
- `admin/` — ABM completo, dashboard, alertas, inventario
- `tecnico/` — Mantenimientos, instalaciones, mediciones, sensores
- `cliente/` — Consulta de tanques, alertas, historial

## Conexion a la base de datos

`config/db.php` expone dos conexiones:
- `$conn` — MySQLi (ya existente, se mantiene para compatibilidad)
- `$pdo` / `eva_pdo()` — PDO (usar para queries parametrizadas nuevas)

**REGLA CRITICA:** El servidor NO tiene mysqlnd. `$stmt->get_result()` causa error 500. Usar PDO para todo query con parámetros:

```php
$pdo = eva_pdo();
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

Para queries sin input de usuario (contadores hardcoded), `$conn->query()` directo sí funciona.

## Arquitectura de archivos

Cada archivo PHP es autocontenido: arranca con `session_start()`, valida rol, incluye `config/db.php`, define lógica, luego HTML. No hay sistema de rutas ni templating.

Estructura típica de un archivo admin:
```php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
```

## Convenciones de código

- **Idioma**: Todo en español — nombres de variables, funciones, archivos, clases CSS, textos UI. Solo usar inglés para keywords PHP, nombres de columnas de BD y funciones built-in.
- **Sin comentarios** por función ni bloque explicativo. El código debe ser autoexplicativo por nombres.
- **Funciones helper**: Definir al tope del archivo, antes del HTML. Patrón: `badgeEstado()`, `countPorTipo()`, etc.
- **Badges/estados**: Usar arrays de mapeo `['valor_bd' => ['cls'=>'css', 'txt'=>'Legible']]`.
- **Filtros GET**: Sanitizar con whitelist (`in_array`), usar `htmlspecialchars()` en todo output.
- **Paginación**: `LIMIT ? OFFSET ?` con PDO. Botones con `http_build_query()` para preservar filtros.
- **Estadísticas**: Queries directas con `$conn->query()` para valores hardcodeados. No usar prepared statements si no hay input de usuario.

## Base de datos

Dump en `db/u156482620_EVAelvigilante (1).sql`. Tablas principales:
- `usuarios` → `roles` (ADMIN, TECNICO, USUARIO)
- `edificios` → `tanques` → `dispositivos` → `sensores` → `mediciones`
- `alertas` (enum tipo: NIVEL_BAJO, NIVEL_ALTO, SIN_CONEXION, FALLA_SENSOR, CONSUMO_ANORMAL)
- `mantenimientos` (enum tipo: PREVENTIVO, CORRECTIVO, PREDICTIVO; enum estado: PENDIENTE, EN_PROCESO, FINALIZADO, CANCELADO)
- `inventario` (enum categoria: SENSOR, DISPOSITIVO, BATERIA, ANTENA, CABLE, REPUESTO, OTRO)
- `log_actividad` — tabla de auditoría (no tiene `id_usuario` NOT NULL, puede ser NULL)
- `configuracion_alertas` — umbrales por tanque
- `consumos` — litros consumidos por día

## CSS y JS

- `admin/css/admin.css` — estilos compartidos del admin
- `admin/js/admin.js` — scripts del admin (tabs, modales, temas)
- Cada panel tiene sus propias carpetas `css/` y `js/`
- Las clases CSS siguen convención: `.badge.activo`, `.badge.inactivo`, `.badge.pendiente`, `.badge.advertencia`, `.badge.media`, `.badge.baja`, `.badge.alta`
- SVG inline para iconos (no usar librería de iconos externa)

## Errores conocidos

- `index.php` raíz usa `get_result()` para login — funcionar porquĕ mysqlnd puede estar compilado solo para ciertas rutas, pero es riesgo futuro.
- `config/db.php` tiene `display_errors=1` — quitar en producción.
- Credenciales de BD en texto plano en `config/db.php`.
