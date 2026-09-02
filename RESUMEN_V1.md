# EVA — El Vigilante del Agua · Resumen V1

> **Proyecto Anual Web · Primera Versión** — Sistema inteligente de monitoreo de tanques de agua con roles **ADMIN / TÉCNICO / USUARIO (Cliente)**, dispositivos ESP32, sensores ultrasónicos y dashboard en tiempo real.
> Fecha de corte: **25-26 de agosto de 2026** · Repositorio local `Proyecto-Anual-Web`

---

## 1. Objetivo del Sistema

**EVA** resuelve la falta de visibilidad sobre el nivel, consumo y mantenimiento de tanques elevados (ej. consorcios). Cada edificio tiene uno o más tanques; cada tanque tiene un **dispositivo EVA (ESP32)** + **sensor ultrasónico JSR-SR04T** que reporta mediciones periódicas. El sistema:

- Mide nivel/litros/% y lo visualiza con gauges y gráficas.
- Genera **alertas** (nivel bajo/alto, sin conexión, falla sensor, consumo anormal).
- Gestiona **mantenimientos** e **instalaciones** a cargo de técnicos.
- Da trazabilidad vía **auditorías, consumos y historial**.
- Separa responsabilidades por **3 roles** con autenticación y sesión PHP.

Documentación de campo incluida en `carpeta de campo/CARPETA DE CAMPO.pdf` y `kit de fortalecimiento/`.

---

## 2. Stack Tecnológico

| Capa | Tecnología |
|---|---|
| **Backend** | PHP 7.2+ / 8.x, `mysqli` + `PDO` (dual), sesiones nativas, `password_hash` (bcrypt) |
| **Frontend** | HTML5 + CSS3 puro (`admin/css/admin.css`, `cliente/css/style.css`, `tecnico/css/tecnico.css`) + JS vanilla (`admin/js/admin.js`, `cliente/js/script.js`, `tecnico/js/tecnico.js`), SVG inline (donuts, gauges, tanques) |
| **Base de datos** | MariaDB 11.8.8 / MySQL 5.7+ (`u156482620_EVAelvigilante`), `utf8mb4` |
| **Hosting** | Hostinger (credenciales en `config/db.php` — ver §9) |
| **Hardware** | ESP32-WROOM-32 (`firmware v2.1.0-ESP32`), sensor JSR-SR04T, WiFi + batería |
| **Control de versiones** | Git |

No hay framework (ni Composer, ni Node). Todo es PHP nativo + JS vanilla.

---

## 3. Estructura de Carpetas

```
Proyecto-Anual-Web/
├── index.php                      # Login único + redirección por rol (ADMIN/TECNICO/USUARIO)
├── config/
│   ├── db.php                     # Conexión dual mysqli ($conn) + PDO ($pdo/eva_pdo())
│   ├── auth.php                   # Guard genérico de sesión
│   └── logout.php                 # session_destroy + redirect
├── db/
│   └── u156482620_EVAelvigilante (1).sql  # Dump completo (22 tablas, datos demo)
├── admin/                         # Panel ADMIN (14 vistas)
│   ├── index.php                  # Dashboard (stats, donuts, tablas, gráficos)
│   ├── usuarios.php               # CRUD usuarios + cambio de password + toggle activo
│   ├── roles.php                  # Listado roles y permisos
│   ├── empresas.php               # CRUD empresas
│   ├── edificios.php              # CRUD edificios (asociado a usuario + empresa)
│   ├── tanques.php                # CRUD tanques
│   ├── sensores.php               # CRUD sensores (asociado a dispositivo)
│   ├── dispositivos.php           # Gestión de dispositivos EVA
│   ├── instalaciones.php          # Listado instalaciones
│   ├── mantenimientos.php         # Listado mantenimientos
│   ├── inventario.php + compras.php # Stock y movimientos
│   ├── alertas.php                # Monitor de alertas global
│   ├── auditorias.php             # log_actividad
│   ├── reportes.php               # Reportes/estadísticas
│   ├── includes/sidebar.php       # Sidebar con submenús (usuarios, dispositivos)
│   ├── includes/header.php        # Header con saludo + campana + tema
│   ├── css/admin.css
│   └── js/admin.js                # Donut, line chart, filtros, tema, sidebar toggle
├── cliente/                       # Panel USUARIO / Cliente (6 vistas + API)
│   ├── indexcli.php               # Resumen (gauge nivel + resumen rápido + consumo semanal)
│   ├── mitanque.php               # Visual SVG del tanque + temperatura + barras
│   ├── alertas.php                # Alertas del tanque del usuario (filtros activas/resueltas)
│   ├── configuracion.php          # Umbrales nivel bajo (10-50%) y alto (50-100%) + CSRF
│   ├── historial.php              # Histórico de mediciones/consumos
│   ├── mantenimiento.php          # Solicitud de mantenimiento (form tanque + descripción)
│   ├── perfil.php                 # Perfil + foto upload
│   ├── api/
│   │   ├── resumen.php            # JSON pct/temp/capacidad/serie ?period=semana|mes|anio
│   │   ├── tanque.php             # JSON detalle tanque
│   │   ├── alertas.php            # JSON alertas
│   │   ├── historial.php          # JSON historial mediciones
│   │   └── configuracion.php      # POST umbrales
│   ├── includes/helpers.php       # Helpers centrales (eva_pdo, eva_tanques_for_user, etc.)
│   ├── css/style.css
│   ├── js/script.js               # Gauges, charts, filtros alertas, tema, fetch APIs
│   └── uploads/.gitkeep
├── tecnico/                       # Panel TÉCNICO (13 vistas)
│   ├── indextec.php               # Dashboard técnico (sensores/instalaciones/mantenimientos)
│   ├── misdispositivos.php        # Dispositivos asignados al técnico
│   ├── mitanques.php              # Tanques asignados
│   ├── sensores.php               # Gestión sensores
│   ├── mediciones.php             # Lecturas por sensor
│   ├── alertas.php                # Alertas técnicas
│   ├── mantenimientos.php         # CRUD mantenimientos
│   ├── mantagregar.php / manteliminar.php
│   ├── instalaciones.php + instprogramar.php + instalar.php
│   ├── historialtecnico.php       # Histórico intervenciones
│   ├── notificaciones.php
│   ├── perfil.php
│   ├── css/tecnico.css
│   └── js/tecnico.js
├── carpeta de campo/              # PDF de relevamiento en campo
├── kit de fortalecimiento/        # PDF complementos
└── .gitignore / LICENSE
```

---

## 4. Autenticación y Seguridad

- **Login central** `index.php:21-65` — `SELECT u.* JOIN roles`, `password_verify()`, valida `activo=1`, setea `$_SESSION{id_usuario,nombre,apellido,email,rol}`, `UPDATE ultimo_acceso = NOW()`, redirección por rol.
- **Guards**: cada `admin/*.php`, `cliente/*.php`, `tecnico/*.php` valida `$_SESSION['rol']` al inicio (ej. `admin/index.php:2-6`, `cliente/indexcli.php:2-6`).
- **Passwords**: `password_hash(PASSWORD_DEFAULT)` en `admin/usuarios.php:36,99`. Requiere ≥6 caracteres.
- **CSRF**: `eva_csrf_token()` / `eva_csrf_validate()` en `cliente/includes/helpers.php:22-32`, usado en `cliente/configuracion.php:80-84`.
- **SQL Injection**: `mysqli::prepare + bind_param` (admin) y `PDO prepared` (cliente). Helpers hacen detección dinámica de columnas con `SHOW COLUMNS` para compatibilidad de esquema.
- **XSS**: helper `h()` (`htmlspecialchars`) usado sistemáticamente en cliente (`helpers.php:9-12`).
- **Logout**: `config/logout.php:1-6` — `session_unset + session_destroy`.

> **Pendiente V1**: rate-limit login, recuperación de contraseña (`¿Olvidé mi contraseña?` es placeholder), remember-me sin implementar, credenciales hardcodeadas en `config/db.php`.

---

## 5. Base de Datos — Modelo Relacional (22 tablas)

Dump: `db/u156482620_EVAelvigilante (1).sql` — `ENGINE=InnoDB, utf8mb4_unicode_ci`

### Diagrama simplificado

```
roles 1──∞ usuarios ──∞ edificios ──∞ tanques ──∞ dispositivos ──∞ sensores ──∞ mediciones
                  │              │           │            │              └─ configuracion_alertas
                  │              │           │            ├─ firmware (FK)
                  │              │           │            ├─ historial_estado_dispositivo
                  │              │           │            ├─ instalaciones (→ usuarios técnico)
                  │              │           │            ├─ mantenimientos (→ usuarios técnico)
                  │              │           │            └─ tecnico_dispositivo (M:N técnico-dispositivo)
                  │              │           └─ alertas / consumos / archivos
                  │              └─ empresas ──∞ edificios
                  └─ log_actividad / notificaciones
inventario ──∞ movimientos_inventario
```

### Tablas y propósito

| Tabla | PK | Descripción | Campos clave |
|---|---|---|---|
| `roles` | `id_rol` | ADMIN / TECNICO / USUARIO | `nombre UNIQUE` |
| `usuarios` | `id_usuario` | Personas del sistema | `email UNIQUE`, `password_hash`, `activo`, `id_rol FK`, `id_empresa FK`, `foto`, `dni`, `telefono` |
| `empresas` | `id_empresa` | Consorcios / administradoras | `nombre, cuit, telefono, email` |
| `edificios` | `id_edificio` | Edificios físicos | `nombre, direccion, ciudad, id_usuario FK` |
| `tanques` | `id_tanque` | Tanques por edificio | `nombre, capacidad_litros, altura_cm, tipo, material, diametro, id_edificio FK` |
| `dispositivos` | `id_dispositivo` | Nodo EVA (ESP32) | `nombre, mac_address UNIQUE, ip_local, estado(ONLINE/OFFLINE/MANTENIMIENTO), id_tanque FK, bateria, intensidad_senal, frecuencia_envio` |
| `sensores` | `id_sensor` | Ultrasónico por dispositivo | `modelo, numero_serie, estado(ACTIVO/INACTIVO/FALLA), id_dispositivo FK, rango_min/max, precision` |
| `mediciones` | `id_medicion` | Lecturas periódicas | `id_sensor FK, distancia_cm, nivel_cm, porcentaje, litros, fecha_hora` |
| `consumos` | `id_consumo` | Consumo diario agregado | `id_tanque FK, litros_consumidos, fecha` |
| `alertas` | `id_alerta` | Alertas generadas | `id_tanque FK, tipo(NIVEL_BAJO/NIVEL_ALTO/SIN_CONEXION/FALLA_SENSOR/CONSUMO_ANORMAL), estado(PENDIENTE/ATENDIDA/CERRADA)` |
| `configuracion_alertas` | `id_config` | Umbrales por tanque | `id_tanque FK, nivel_minimo(15), nivel_maximo(95), notificar_email/sistema` |
| `dispositivos` | — | — | — |
| `mantenimientos` | `id_mantenimiento` | Intervenciones | `id_dispositivo FK, id_tecnico FK, tipo(PREVENTIVO/CORRECTIVO/PREDICTIVO), estado(PENDIENTE/EN_PROCESO/FINALIZADO/CANCELADO), costo` |
| `instalaciones` | `id_instalacion` | Instalaciones | `id_dispositivo FK, id_tecnico FK, fecha_instalacion, lat/lng` |
| `inventario` | `id_item` | Stock | `nombre, categoria(SENSOR/DISPOSITIVO/BATERIA/ANTENA/CABLE/REPUESTO/OTRO), stock, stock_minimo` |
| `movimientos_inventario` | `id_movimiento` | Entradas/salidas | `id_item FK, tipo(ENTRADA/SALIDA), cantidad` |
| `firmware` | `id_firmware` | Versiones ESP32 | `version, descripcion, fecha_publicacion` |
| `notificaciones` | `id_notificacion` | Notifs por usuario | `id_usuario FK, mensaje, leida` |
| `log_actividad` | `id_log` | Auditoría | `id_usuario FK, accion, detalle, ip, fecha_hora` |
| `archivos` | `id_archivo` | Docs por tanque | `id_tanque FK, nombre, ruta, tipo` |
| `historial_estado_dispositivo` | `id_historial` | Cambios de estado | `id_dispositivo FK, estado, fecha` |
| `tecnico_dispositivo` | `(id_tecnico, id_dispositivo)` | Asignación M:N | PK compuesta |

**Datos demo incluidos**: 1 edificio (Olivos I), 1 tanque (5000 L, 200 cm), 1 dispositivo ONLINE (-65 dBm, 98.5% batería), 1 sensor JSR-SR04T, 5 mediciones (20%-87%), 1 alerta NIVEL_BAJO, 1 consumo (3200 L), 2 items inventario, 1 mantenimiento FINALIZADO.

---

## 6. Configuración Central

### `config/db.php:1-45`

- **Dual driver**: `mysqli $conn` (usado en `admin/*`) + `PDO $pdo` (usado en `cliente/*` vía `eva_pdo()`).
- `eva_pdo(): PDO` — singleton que lanza `RuntimeException` si PDO no está disponible.
- Charset `utf8mb4`, `ERRMODE_EXCEPTION`, `EMULATE_PREPARES=false`.

### `cliente/includes/helpers.php:1-233`

Helpers reutilizables (PDO + `declare(strict_types=1)`):

- `h()` — escape HTML.
- `eva_current_user_id()` — `$_SESSION['id_usuario']`.
- `eva_csrf_token / eva_csrf_validate` — token 32 bytes.
- `eva_tanques_for_user(PDO, uid)` — resuelve tanques por usuario probando `usuarios.id_empresa → empresas → edificios → tanques` y fallback a `tanques activos LIMIT 20`.
- `eva_first_tanque()` — primer tanque del usuario.
- `eva_latest_medicion()` — 3 estrategias: `mediciones.id_tanque`, `mediciones→sensores→dispositivos→tanques`, `mediciones→dispositivos`.
- `eva_consumo_hoy / eva_consumo_promedio / eva_consumo_serie(period=semana|mes|anio)` — agregaciones sobre `consumos` con manejo de columnas `litros_consumidos/cantidad/litros`.
- `eva_device_status()` — ONLINE/OFFLINE según `dispositivos.estado` y `ultima_conexion < 10 min`.
- `eva_estado_texto(pct)` — Crítico ≤10%, Bajo ≤25%, Sobrecarga ≥90%, Normal resto.
- `eva_log_actividad()` — inserta en `log_actividad`.

---

## 7. Paneles por Rol

### 7.1 Login (`index.php:68-197`)

- Layout split: izquierda marca EVA gigante, derecha card login (460 px, `#111c30`, border-radius 20 px).
- Campos email + password con toggle ojo, checkbox Recordarme (no implementado), link olvidé contraseña (placeholder), validación y mensajes `error-msg`.
- Responsive: <900 px oculta columna izquierda; <480 px compacta.

### 7.2 ADMIN (`admin/`)

**Dashboard `admin/index.php:1-372`** — 7 stat-cards (Clientes, Edificios, Dispositivos, Instalaciones, Tanques, Técnicos, Sensores) + donut estado dispositivos (ONLINE/MANTENIMIENTO/OFFLINE) + tabla mantenimientos pendientes + gráfico actividad 7 días (SVG) + tabla usuarios recientes + acciones rápidas + 3 charts (nivel por día, por edificio, consumo semanal) + 3 tablas (dispositivos, alertas, mantenimientos). Usa `mysqli` directo.

**Módulos CRUD** (todos con `sidebar.php` + `header.php` + modal + filtros JS):

| Archivo | Funcionalidad | Acciones POST |
|---|---|---|
| `usuarios.php:1-465` | Lista usuarios con avatar degradado, rol badge, estado, último acceso; filtros búsqueda/rol/estado; paginación | `crear`, `editar`, `cambiar_password`, `toggle_estado`, `eliminar` (protege auto-eliminación) |
| `roles.php` | Matriz roles/permisos (solo lectura V1) | — |
| `empresas.php` | CRUD empresas (nombre, CUIT, contacto) | crear/editar/eliminar |
| `edificios.php` | CRUD edificios (nombre, dirección, ciudad, usuario responsable) | crear/editar/eliminar |
| `tanques.php` | CRUD tanques (nombre, ubicación, capacidad, estado) | crear/editar/eliminar |
| `sensores.php` | CRUD sensores (modelo, serie, dispositivo, estado, rango, calibración) | crear/editar/eliminar |
| `dispositivos.php` | Gestión dispositivos (nombre, MAC, IP, estado, batería, señal) | crear/editar/eliminar + asignar técnico |
| `instalaciones.php` | Lista instalaciones con técnico/fecha/obs | crear |
| `mantenimientos.php` | Lista mantenimientos con filtros estado | crear/editar/cambiar estado |
| `inventario.php` | Stock por categoría con alertas stock mínimo | entrada/salida |
| `compras.php` | Órdenes de compra (placeholder) | — |
| `alertas.php` | Monitor global alertas (tipo/estado/tanque/fecha) | atender/cerrar |
| `auditorias.php` | `log_actividad` con IP y detalle | — |
| `reportes.php` | Gráficos agregados (consumo, nivel, actividad) | exportar |

**Includes** `admin/includes/sidebar.php:1-112` (logo, rol Administrador, menú con submenús colapsables Usuarios/Dispositivos, toggle arrow) y `header.php:1-37` (saludo, campana con badge 3, toggle tema sol/luna, dropdown usuario).

**JS/CSS** `admin/css/admin.css` (variables `--tx4`, `anim-bounce`, donut, tables, badges `activo/inactivo/pendiente/media/baja`), `admin/js/admin.js` (sidebar toggle, donut SVG animado `stroke-dasharray`, lineChart, filtrado, tema `localStorage`).

### 7.3 CLIENTE / Usuario (`cliente/`)

**Resumen `indexcli.php:1-215`** — Gauge semicircular (SVG 200x130, gradiente azul) + estado del tanque + temp + resumen rápido (capacidad/disponible/consumo hoy/promedio) + mini-chart consumo (select semana/mes/año, datos inyectados `window.EVA_RESUMEN` desde PHP). Sin datos → muestra 0 y "No hay datos disponibles" (no mock).

**Mi Tanque `mitanque.php:1-220`** — SVG tanque realista con clipPath + gradiente agua + ondas animadas + burbujas + nivel % + litros + estado + última actualización + gauge temperatura (0-100) + barras promedio temperatura (7 días reales).

**Alertas `alertas.php:1-195`** — Filtros `todas/activas/resueltas`, lista inyectada `window.EVA_ALERTAS` (100 últimas, orden `fecha DESC`), mapeo `eva_alert_tipo_map`, formato fecha "Hoy HH:MM" o "d de M".

**Configuración `configuracion.php:1-256`** — Sliders `nivel_bajo 10-50%` y `nivel_alto 50-100%`, detección dinámica de columnas `SHOW COLUMNS FROM configuracion_alertas` (soporta esquemas con `tipo/valor`, `nivel_bajo/alto`, etc.), validación `low < high`, upsert + `eva_log_actividad`, CSRF, mensajes success/error.

**Historial `historial.php`** — Tabla mediciones con paginación y gráfico histórico (usa `api/historial.php`).

**Mantenimiento `mantenimiento.php`** — Form solicitud (select tanque + textarea 500 chars) → inserta en `mantenimientos` PENDIENTE.

**Perfil `perfil.php`** — Edición nombre/apellido/email/teléfono + upload foto (`uploads/`).

**APIs `cliente/api/*.php`** (JSON, auth `USUARIO`, `Content-Type: application/json`):

- `resumen.php:1-35` → `{pct,temp,capacidad,disponible,consumoHoy,promedio,serie,period}`
- `tanque.php` → detalle tanque + última medición
- `alertas.php` → lista alertas filtrada
- `historial.php` → serie mediciones `?from&to&limit`
- `configuracion.php` → GET umbrales / POST guardar

### 7.4 TÉCNICO (`tecnico/`)

**Dashboard `indextec.php:1-188`** — 4 stat-cards (Sensores activos 24, Instalaciones 8, Mantenimientos 15, Sistemas EVA 10) — *valores hardcodeados V1, pendiente conectar a BD* — + donut sensores + mantenimientos próximos + actividad reciente + acciones rápidas (Agregar/Eliminar sensor, Programar instalación, Instalar EVA).

**Vistas** (`misdispositivos.php`, `mitanques.php`, `sensores.php`, `mediciones.php`, `alertas.php`, `mantenimientos.php` + `mantagregar.php`/`manteliminar.php`, `instalaciones.php` + `instprogramar.php`/`instalar.php`, `historialtecnico.php`, `notificaciones.php`, `perfil.php`) — listados con filtros, formularios y asignación técnico-dispositivo vía `tecnico_dispositivo`. Sidebar técnico con submenús Mantenimiento/Instalación (`data-toggle="mant|inst"`), `tecnico.css` / `tecnico.js` propios.

> **Nota V1**: el panel técnico es mayormente maquetado con datos estáticos; la lógica de `cliente` es la más avanzada en integración real con BD.

---

## 8. Frontend — Patrones Comunes

- **Sidebar fijo** + **main con header**: logo EVA SVG (#3C75C6, gota), navegación con iconos stroke, `active` highlight, `anim-slide1..n` y `anim-bounce`.
- **Tema claro/oscuro**: botón `#themeToggle` con iconos sol/luna, clase `hidden`, persistido en `localStorage` (`script.js` / `admin.js` / `tecnico.js`).
- **Gráficos SVG puros**: donuts (`stroke-dasharray` animado), gauges semicirculares con `linearGradient` y aguja rotada por `pct`, line/bar charts generados por JS.
- **Responsive**: breakpoints 900 px y 480 px.
- **Badges**: `badge activo/inactivo/pendiente/advertencia/media/baja` con colores `#4caf50/#f44336/#ff9800`.

---

## 9. Credenciales y Datos de Prueba

> ⚠️ Credenciales en `config/db.php:6-9` apuntan a Hostinger producción — **rotar antes de publicar el repo**.

```php
host = localhost
user = u156482620_EVAelvigilante
pass = #VALzona122233
db   = u156482620_EVAelvigilante
```

**Usuarios demo** (bcrypt, `usuarios`):

| Email | Rol | Password* | Nombre |
|---|---|---|---|
| `admin@eva.com` | ADMIN | `admin` (hash `$2y$10$Wgiw...`) | Administrador Sistema |
| `tecnico@eva.com` | TECNICO | `tecnico` (hash `$2y$10$m2C2...`) | Carlos Técnico |
| `usuario@eva.com` | USUARIO | `usuario` (hash `$2y$10$6n/c...`) | Juan Usuario |
| `zoe@eva.com` | TECNICO | `zoe` (hash `$2y$10$M5ak...`) | Zoe |

*Verificar con `password_verify()` — hashes son bcrypt 10 rounds.

---

## 10. Estado Actual V1 — Qué está y qué falta

### ✅ Implementado

- Login + sesiones + guards por rol + logout.
- Conexión dual mysqli/PDO centralizada.
- Helpers robustos con fallbacks de esquema (cliente).
- Admin dashboard funcional (contadores reales) + CRUD usuarios completo + sidebar/header + gráficos base.
- Cliente 100% conectado a BD real (sin mocks): resumen, tanque, alertas, configuración con CSRF, APIs JSON.
- Técnico maquetado con navegación y vistas base.
- Dump SQL completo con 22 tablas + índices + FKs + datos demo.

### 🚧 En curso / Pendiente V1

- [ ] Conectar **técnico/indextec.php** a datos reales (hoy hardcodeado 24/8/15/10).
- [ ] CRUD completos en `admin/sensores|dispositivos|tanques|empresas|edificios|instalaciones|mantenimientos` (algunos son solo listado).
- [ ] `cliente/historial.php` y `mantenimiento.php` — completar paginación y notificaciones.
- [ ] Subida real de firmware y OTA (`firmware` tabla sin flujo).
- [ ] Ingesta real ESP32 → `mediciones` (endpoint receptor aún no existe — hoy se inserta manual/SQL).
- [ ] Recuperar contraseña / Recordarme / Validación email.
- [ ] Paginación server-side, búsqueda y ordenamiento en todas las tablas.
- [ ] Tests y seeders reproducibles (hoy solo dump).
- [ ] Variables de entorno para credenciales (hoy hardcodeadas).
- [ ] `config/auth.php:1-6` es guard simple — unificar con `admin/index.php` y `cliente` guards.

### 🔜 Roadmap sugerido V2

1. Endpoint `POST /api/mediciones` para ESP32 (API key por `dispositivos.mac_address`).
2. WebSocket / polling para tiempo real en cliente (hoy reload/API manual).
3. Notificaciones push/email (`configuracion_alertas.notificar_email` sin envío).
4. Roles granulares (permisos por tabla en `admin/roles.php`).
5. Exportar reportes PDF/Excel desde `admin/reportes.php`.
6. Migrar credenciales a `.env` + `vlucas/phpdotenv`.

---

## 11. Cómo Ejecutar Localmente

```bash
# 1. Clonar y colocar en htdocs / www
git clone <repo> Proyecto-Anual-Web

# 2. Importar BD
mysql -u root -p < db/"u156482620_EVAelvigilante (1).sql"

# 3. Configurar credenciales locales en config/db.php
#    $host="localhost"; $user="root"; $pass=""; $db="u156482620_EVAelvigilante";

# 4. Servir
php -S localhost:8000
# o XAMPP/WAMP → http://localhost/Proyecto-Anual-Web/index.php

# 5. Login demo
# admin@eva.com / tecnico@eva.com / usuario@eva.com
```

---

## 12. Archivos Clave — Referencia Rápida

| Archivo | Líneas | Rol |
|---|---|---|
| `index.php` | 197 | Login + redirect rol |
| `config/db.php` | 45 | Conexión dual + `eva_pdo()` |
| `config/auth.php` | 6 | Guard genérico |
| `config/logout.php` | 6 | Logout |
| `cliente/includes/helpers.php` | 233 | Helpers centrales |
| `admin/index.php` | 372 | Dashboard admin |
| `admin/usuarios.php` | 465 | CRUD usuarios |
| `admin/includes/sidebar.php` | 112 | Sidebar admin |
| `cliente/indexcli.php` | 215 | Resumen cliente |
| `cliente/mitanque.php` | 220 | Tanque SVG |
| `cliente/configuracion.php` | 256 | Config alertas |
| `cliente/api/resumen.php` | 35 | API resumen |
| `tecnico/indextec.php` | 188 | Dashboard técnico |

---

## 13. Glosario

- **EVA**: El Vigilante del Agua.
- **Dispositivo EVA**: Nodo ESP32 + sensor ultrasónico instalado en el tanque.
- **Tanque Copete**: Tanque elevado en terraza/azotea (ej. 5000 L).
- **Medición**: `distancia_cm` (sensor→agua) → `nivel_cm` → `porcentaje` → `litros`.
- **Alerta**: Evento `NIVEL_BAJO/ALTO/SIN_CONEXION/FALLA_SENSOR/CONSUMO_ANORMAL`.

---

*Documento generado automáticamente por relevamiento del código fuente — V1 agosto 2026. Para dudas ver `CARPETA DE CAMPO.pdf` y `Complementos.pdf`.*
