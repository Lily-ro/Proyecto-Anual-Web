# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [0.1.0] - 2026-08-26

> **Primera versión funcional del repositorio** — Tag `v0.1.0` (`36335ae`). Corte tomado en `a2e0aae` (merge del 26/08/2026). Esta versión establece la arquitectura base del sistema **EVA — El Vigilante del Agua** con tres roles, 22 tablas y 61 archivos nuevos respecto al commit inicial.

### Resumen

La `v0.1.0` es el MVP navegable y conectado a base de datos real (MariaDB `u156482620_EVAelvigilante`). Incluye login centralizado, paneles ADMIN / TÉCNICO / CLIENTE, CRUDs base, visualización de nivel de tanque con SVG, gestión de alertas y umbrales, y dump SQL completo con datos demo. Es la base sobre la cual se iterará hacia `v0.2.0` (ingesta ESP32 + tiempo real).

- **61 archivos añadidos**, `+12997` inserciones netas desde `v0.1.0`.
- **Stack**: PHP 7.2+/8.x nativo (mysqli + PDO), HTML/CSS/JS vanilla, SVG, MariaDB 11.8, ESP32.
- **Commits en el rango**: 27 commits (`745e170` → `a2e0aae`) de 4 autores.

---

### Añadido

#### Infraestructura y configuración
- `config/db.php` — conexión dual `mysqli $conn` + `PDO $pdo` con `eva_pdo(): PDO` centralizado, `utf8mb4`, `ERRMODE_EXCEPTION`.
- `config/auth.php` — guard genérico de sesión.
- `config/logout.php` — `session_unset/destroy` + redirect.
- `cliente/includes/helpers.php` — 233 líneas, helpers `h()`, `eva_current_user_id()`, `eva_csrf_token/validate()`, `eva_tanques_for_user()`, `eva_latest_medicion()` (3 estrategias), `eva_consumo_*`, `eva_device_status()`, `eva_estado_texto()`, `eva_log_actividad()`.
- `db/u156482620_EVAelvigilante (1).sql` — dump completo: 22 tablas, índices, FKs, `AUTO_INCREMENT` y datos demo (1 edificio, 1 tanque 5000L, 1 dispositivo ONLINE, 1 sensor JSR-SR04T, 5 mediciones, 1 alerta, 1 consumo, inventario, mantenimiento).
- `.gitignore` + `LICENSE` (MIT 2026) + `cliente/uploads/.gitkeep`.
- Documentación de campo: `carpeta de campo/CARPETA DE CAMPO.pdf` (4.5 MB) y `kit de fortalecimiento/Complementos .pdf` (23 MB).

#### Autenticación
- `index.php` (197 líneas) — login único con `password_verify()`, validación `activo=1`, `UPDATE ultimo_acceso`, redirección por `ADMIN→admin/index.php`, `TECNICO→tecnico/indextec.php`, `USUARIO→cliente/indexcli.php`, layout split + responsive.

#### Panel ADMIN — `admin/` (14 vistas + 2 includes)
- `admin/index.php` — dashboard con 7 stat-cards (Clientes/Edificios/Dispositivos/Instalaciones/Tanques/Técnicos/Sensores), donut estado dispositivos, gráfico actividad 7 días, tablas (dispositivos/alertas/mantenimientos), 3 charts nivel/consumo.
- `admin/usuarios.php` — CRUD completo: crear/editar/toggle/eliminar, cambio de contraseña (bcrypt), validación email único, protección auto-eliminación, filtros búsqueda/rol/estado.
- `admin/roles.php` — listado roles y matriz de permisos.
- `admin/empresas.php` — CRUD empresas (nombre, CUIT, contacto).
- `admin/edificios.php` — CRUD edificios (nombre, dirección, ciudad, responsable).
- `admin/tanques.php` — CRUD tanques (capacidad, altura, tipo, material, diámetro).
- `admin/sensores.php` — CRUD sensores (modelo, serie, dispositivo, rango, calibración).
- `admin/dispositivos.php` — gestión nodos EVA (MAC, IP, estado, batería, señal, firmware).
- `admin/instalaciones.php` — listado instalaciones (técnico, fecha, lat/lng).
- `admin/mantenimientos.php` — listado mantenimientos con estados PENDIENTE/EN_PROCESO/FINALIZADO/CANCELADO.
- `admin/inventario.php` + `admin/compras.php` — stock por categoría y movimientos ENTRADA/SALIDA.
- `admin/alertas.php` — monitor global de alertas (tipo/estado/tanque).
- `admin/auditorias.php` — visor `log_actividad` (usuario, acción, IP, fecha).
- `admin/reportes.php` — reportes y gráficos agregados.
- `admin/tecnicos.php` — listado técnicos.
- `admin/includes/sidebar.php` + `header.php` — navegación con submenús colapsables, saludo, campana, toggle tema.
- `admin/css/admin.css` (470 líneas) + `admin/js/admin.js` (565 líneas) — variables CSS, `anim-bounce`, donuts animados, line charts, filtros, `localStorage` tema.

#### Panel CLIENTE — `cliente/` (6 vistas + 5 APIs)
- `cliente/indexcli.php` — resumen con gauge semicircular SVG, estado, temperatura, resumen rápido (capacidad/disponible/consumo), mini-chart semanal (inyecta `window.EVA_RESUMEN`).
- `cliente/mitanque.php` — tanque SVG realista con clipPath, ondas, burbujas, nivel %, litros, estado, última actualización, gauge temperatura 0–100, barras histórico 7 días.
- `cliente/alertas.php` — listado 100 últimas alertas con filtros `todas/activas/resueltas`, mapeo `eva_alert_tipo_map`, fecha "Hoy HH:MM".
- `cliente/configuracion.php` — sliders umbrales bajo 10–50% / alto 50–100%, detección dinámica `SHOW COLUMNS`, validación `low < high`, upsert, CSRF, `eva_log_actividad`.
- `cliente/historial.php` — histórico mediciones/consumos con tabla y gráfico.
- `cliente/mantenimiento.php` — solicitud de mantenimiento (tanque + descripción 500 chars → `mantenimientos` PENDIENTE).
- `cliente/perfil.php` — edición perfil + upload foto.
- `cliente/api/resumen.php` — `GET ?period=semana|mes|anio` → `{pct,temp,capacidad,disponible,consumoHoy,promedio,serie}`.
- `cliente/api/tanque.php` + `alertas.php` + `historial.php` + `configuracion.php` — endpoints JSON autenticados `USUARIO`.
- `cliente/css/style.css` + `cliente/js/script.js` (534 líneas) — gauges, charts, fetch APIs, filtros, tema.

#### Panel TÉCNICO — `tecnico/` (13 vistas)
- `tecnico/indextec.php` — dashboard técnico (4 stat-cards, donut sensores, mantenimientos próximos, actividad reciente, acciones rápidas).
- `tecnico/misdispositivos.php` + `mitanques.php` + `sensores.php` + `mediciones.php` + `alertas.php` + `notificaciones.php` + `perfil.php` + `historialtecnico.php`.
- `tecnico/mantenimientos.php` + `mantagregar.php` + `manteliminar.php` — CRUD mantenimientos.
- `tecnico/instalaciones.php` + `instprogramar.php` + `instalar.php` — flujo instalación.
- `tecnico/css/tecnico.css` + `tecnico/js/tecnico.js` (514 líneas).

---

### Cambiado / Modificado

- **Migración de HTML estático → PHP dinámico** ( `9650580` — "Diva" ): `cliente/*.html` → `cliente/*.php` (`alertas`, `configuracion`, `indexcli`, `mitanque`), `tecnico/*.html` → `*.php` (`indextec`, `instalar`, `instprogramar`, `mantagregar`, `manteliminar`, `perfil`, `senconfig`), inyección de `require_once config/db.php`, guards de sesión y `eva_pdo()`.
- **Login** (`1a36f61` → `ba288a2`): de `index.html` estático a `index.php` con autenticación real, `password_hash`, `ultimo_acceso`, estilos split-view y luego refactorizado a diseño oscuro `#0b1120 / #111c30`.
- **Cliente funcional** (`6c76e30` — "arreglos cliente funcionalidad"): `cliente/*.php` pasan de mock a datos reales (sin valores ficticios), `helpers.php` añade fallbacks multi-esquema, `config/db.php` añade PDO central, `js/script.js` reescrito para consumir `api/*` y `window.EVA_*`.
- **Admin dashboard** (`42dd859` / `bbc4f0f` — "indexADMINPHP / PHPADMININDEX"): `admin/index.php` pasa de maquetado estático a consultas reales (`SELECT COUNT(*)`, donuts con `data-*`, tablas con badges).
- **Admin CRUDs** (`bbc4f0f`, `28a5415`, `3e449ca`): `usuarios.php` (465 líneas), `empresas.php` (370 líneas), `edificios.php` (323 líneas), `sensores.php` (387 líneas) — de esqueleto a `PREPARED STATEMENTS` + modales + filtros JS.
- **Estilos** (`143b303`, `6177421`, `45df98f`): `css/style.css` → `cliente/css/style.css` y `tecnico/css/tecnico.css` con variables, animaciones `anim-bounce/slide/pulse`, responsive 900/480 px.
- **Rutas** (`875e5c6` — "cambio de rutas"): normalización de hrefs relativos (`../config`, `css/`, `js/`).
- **Mantenimiento cliente** (`1b99d46` / `6177421`): añadidas `cliente/historial.php` + `mantenimiento.php` y pulido estético.
- **Base de datos** (`bbc4f0f`): incorporación del dump `u156482620_EVAelvigilante (1).sql` (895 líneas) que reemplaza inserts manuales previos.

---

### Eliminado / Removido

- `index.html` (235 líneas, `8a6e182`) — reemplazado por `index.php` dinámico.
- `css/style.css` y `css/` raíz (286 líneas, `143b303`) — movido a `cliente/css/style.css` + `tecnico/css/tecnico.css`; eliminado en `6c76e30` (`-225` líneas).
- `js/app.js` (178 líneas, `9f653b3`) — reemplazado por `cliente/js/script.js` y `tecnico/js/tecnico.js`.
- `cliente/*.html` intermedios (`alertas.html`, `configuracion.html`, `index.html`, `mitanque.html`) — renombrados a `.php` en `9650580`.
- `tecnico/*.html` intermedios (`historialtecnico.html`, `indextec.html`, `instalar.html`, `instprogramar.html`, `mantagregar.html`, `manteliminar.html`, `perfil.html`) — renombrados a `.php`.
- `tecnico/senconfig.html` / `.php` (65 líneas) — eliminado en `45df98f` (reemplazado por `tecnico/sensores.php` + `mediciones.php`).
- Placeholders de "Diva" y estilos temporales (`5927661` — "fatal diva").
- Mock data hardcodeado en `cliente/*` (reemplazado por `helpers.php` + `api/*` en `6c76e30`).

---

### Corregido

- Fugas de HTML sin escape → sistemático `h()` en cliente.
- `config/db.php` charset incorrecto → `utf8mb4` + `set_charset`.
- Sidebar admin con rutas rotas (`cambio de rutas`).
- Inconsistencia de roles `USUARIO` vs `CLIENTE` en guards y badges (unificado a `USUARIO` en BD, "Cliente" en UI).
- Validación de contraseñas en `admin/usuarios.php` (mínimo 6 caracteres, confirmación).
- Detección de columnas `configuracion_alertas` para compatibilidad entre esquemas (`SHOW COLUMNS`).

---

### Seguridad

- Passwords con `password_hash(PASSWORD_DEFAULT)` / `password_verify`.
- `mysqli::prepare + bind_param` y `PDO prepared` en todos los POST.
- CSRF tokens en `cliente/configuracion.php`.
- Guards de sesión por rol en todos los entrypoints (`admin/*`, `cliente/*`, `tecnico/*`).

---

### Notas de migración desde `v0.1.0` inicial

El tag `v0.1.0` (`36335ae`) solo contenía `LICENSE`. Esta entrada documenta el salto `36335ae..a2e0aae` (27 commits). No hay breaking changes para consumidores porque es la primera entrega funcional. Para actualizar desde el tag:

```bash
git fetch --tags
git checkout v0.1.0
# o al HEAD actual
git checkout main
mysql -u root -p < db/"u156482620_EVAelvigilante (1).sql"
# configurar config/db.php con credenciales locales
php -S localhost:8000
```

### Próximos pasos — `v0.2.0` (no incluido)

- Endpoint `POST /api/mediciones` para ESP32 (API key por `mac_address`).
- Polling/WebSocket tiempo real.
- Notificaciones email/push.
- Variables de entorno (`.env`) para credenciales.
- Tests y seeders.

---

[0.1.0]: https://github.com/Lily-ro/Proyecto-Anual-Web/releases/tag/v0.1.0
