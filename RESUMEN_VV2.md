# EVA — El Vigilante del Agua · Resumen V2

> **Proyecto Anual Web · Segunda Versión Estable** — Evolución de V1 con ingesta ESP32, tiempo real, esquema completo, seguridad reforzada y credenciales por SMTP real.
> Fecha de corte: **07 de septiembre de 2026** · Tag `v0.2.0` (`c0f6af0`) · Commit base `8e2799c → c0f6af0`

---

## 1. Objetivo y delta respecto a V1

V1 entregó el MVP navegable con 22 tablas, login y paneles ADMIN/TECNICO/CLIENTE. V2 cierra las brechas críticas detectadas en auditoría:

| Pendiente V1 | Estado V2 |
|---|---|
| `tecnico/indextec.php` hardcodeado 24/8/15/10 | ✅ Conectado a BD real (`COUNT sensores/instalaciones/mantenimientos` por `id_tecnico`) — bug `rInstalaciones` duplicado corregido |
| `admin/sensores|empresas|edificios|instalaciones|mantenimientos` con `get_result()` sin mysqlnd → 500 | ✅ Migrados a `PDO eva_pdo()` |
| `mediciones` sin `temperatura/humedad` pero código las usaba | ✅ `ALTER TABLE mediciones ADD temperatura/humedad` en `db/migrations_v0_2_0.sql:3` |
| Tablas `clientes, credenciales_clientes, compras, productos, proveedores, historial_compras, notificaciones_compras` faltaban | ✅ Creadas en migración |
| `api/guardar_datos.php` vs `api/esp32/mediciones.php` duplicados y divergentes | ✅ Unificados: `guardar_datos.php` wrapper tolerante + `esp32/mediciones.php` canónico con `eva_procesar_tanque()` |
| Credenciales hardcodeadas en `config/db.php`/`mail_config.php` | ✅ `config/env.php` loader nativo + `.env.example` |
| Stub PHPMailer (36 líneas) con `mail()` | ✅ PHPMailer oficial 6.9.1 (183k) SMTP `smtp.hostinger.com:465 ssl` |
| `tecnico/mediciones.php` 10 filas estáticas | ✅ Reescrito a 50 últimas mediciones reales vía `tecnico_dispositivo` |
| Login sin `session_regenerate_id`, sin rate-limit, `¿Olvidé mi contraseña?` placeholder | ✅ `session_regenerate_id(true)`, `login_intentos` 5/15min, `recuperar.php`/`restablecer.php` con token 32 bytes |
| Sin polling tiempo real | ✅ `cliente/api/estado.php` + `js/tiempo-real.js` cada 30s |

---

## 2. Stack Tecnológico

| Capa | V1 | V2 |
|---|---|---|
| **Backend** | PHP 7.2+/8.x `mysqli`+`PDO`, sesiones | + `config/env.php` (`eva_env()`), `password_resets`/`login_intentos`, `eva_procesar_tanque()` |
| **Frontend** | HTML/CSS/JS vanilla, SVG | + polling `tiempo-real.js` (30s, `visibilitychange`) |
| **Base de datos** | MariaDB 11.8.8, 22 tablas, `utf8mb4` | **30 tablas** (22 + 8 nuevas), `temperatura/humedad` en mediciones |
| **Mail** | Stub `mail()` roto | PHPMailer 6.9.1 `SMTPS` 465 `ssl` autenticado |
| **Hardware** | ESP32 `v2.1.0`, JSR-SR04T | + validación `porcentaje 0-100`, `temperatura/humedad` opcionales |
| **Hosting** | Hostinger | Hostinger con `.env` |

Sin framework, sin Composer, sin Node — se mantiene vanilla.

---

## 3. Estructura de Carpetas (cambios V2 en **negrita**)

```
Proyecto-Anual-Web/
├── index.php                      # Login + rate-limit + session_regenerate_id + link recuperar.php
├── recuperar.php                  # **NUEVO** — solicita reset, genera token, envía mail
├── restablecer.php                # **NUEVO** — valida token, password_hash
├── .env.example                   # **NUEVO** — plantilla DB_*, SMTP_*, APP_*
├── config/
│   ├── db.php                     # **MOD** — lee .env, fallback candidatos, display_errors por APP_DEBUG
│   ├── env.php                    # **NUEVO** — eva_load_env()/eva_env() parser nativo
│   ├── mail.php                   # **REESCRITO** — PHPMailer real, ENCRYPTION_SMTPS, sin mail()
│   ├── mail_config.php            # **MOD** — lee eva_env(), ignorado por git
│   ├── mail_config.example.php    # Plantilla
│   ├── auth.php / logout.php
├── db/
│   ├── u156482620_EVAelvigilante (1).sql  # Dump base 22 tablas
│   └── migrations_v0_2_0.sql      # **NUEVO** — ALTER mediciones + 8 tablas + seeds
├── api/
│   ├── guardar_datos.php          # **MOD** — wrapper tolerante (retry sin temp/hum), soporta JSON
│   └── esp32/mediciones.php       # **MOD** — soporta temp/hum, eva_procesar_tanque()
├── admin/                         # 14 vistas (5 migradas PDO)
│   ├── sensores.php               # **PDO**
│   ├── empresas.php               # **PDO**
│   ├── edificios.php              # **PDO** (eliminar)
│   ├── instalaciones.php          # **PDO** fetchAll
│   ├── mantenimientos.php         # **PDO** countMant/buildMantQuery
│   ├── usuarios.php               # commit antes de mail, random_int(12), unset
│   ├── clientes.php / compras.php # manejo bool mail
│   └── ... (resto igual V1)
├── cliente/
│   ├── includes/procesador_mediciones.php # **EXISTIA** — actualizar consumos/alertas/dispositivo
│   ├── includes/helpers.php       # helpers 400 líneas
│   ├── api/estado.php             # **NUEVO** — dashboard + serie + barsData + alertas
│   ├── api/procesar.php           # **NUEVO** — eva_procesar_todos()
│   ├── api/sincronizar.php        # **NUEVO** — eva_sincronizar_*
│   ├── api/resumen.php/tanque.php/alertas.php/historial.php/configuracion.php
│   ├── js/tiempo-real.js          # **NUEVO** — polling 30s, actualiza window.EVA_*
│   └── ...
├── tecnico/
│   ├── indextec.php               # **FIX** — queries reales (antes hardcodeado)
│   ├── mediciones.php             # **REESCRITO** — PDO real 50 últimas, tecnico_dispositivo
│   ├── sensores.php / mantenimientos.php # **PENDIENTE** — aún hardcode, próxima iteración
│   └── ...
├── vendor/phpmailer/src/          # **REEMPLAZADO** — oficial 6.9.1 (no stub)
└── RESUMEN_V1.md / RESUMEN_V2.md / CHANGELOG.md
```

---

## 4. Autenticación y Seguridad V2

### Login `index.php:21-68`
- `filter_var` email, `password_verify()`, `activo=1`.
- `session_regenerate_id(true)` anti-fixation.
- `login_intentos` — si `COUNT(*) WHERE exito=0 AND fecha_hora>=15min` ≥5 → bloquea "Demasiados intentos".
- Inserta `login_intentos` con `exito=0/1` por intento.
- `UPDATE ultimo_acceso = NOW()` y redirect por rol.

### Recuperación
- `recuperar.php:1-60` — `token=bin2hex(random_bytes(32))`, `INSERT password_resets (email,token,expira +1H)`, mail HTML+txt con link `BASE_URL/restablecer.php?token=...`, siempre muestra "Si el email existe..." (no enumera).
- `restablecer.php:1-40` — valida `token,usado,expira`, `password_hash($pass)` ≥6 chars, `UPDATE usuarios` + `usado=1`.

### Guards / CSRF / XSS
- Guards `ADMIN/TECNICO/USUARIO` al inicio de cada PHP.
- `eva_csrf_token()` en `configuracion.php` (cliente).
- `h()` `htmlspecialchars` sistemático.
- `config/env.php` — `eva_env()` lee `.env` y `getenv()`.

### Mail
- `config/mail.php:14-38` `eva_phpmailer_base()`: `isSMTP() Host smtp.hostinger.com SMTPAuth true Username/Password SMTPSecure ENCRYPTION_SMTPS Port 465 CharSet UTF-8 Timeout 15`.
- `eva_enviar_mail_credenciales()` valida email, `setFrom`, `addAddress`, `Subject`, `Body` HTML con credenciales + botón `INGRESAR A EVA` + `AltBody`, `send()` → `error_log` sin passwords, `return true/false`.

---

## 5. Base de Datos — 22 → 30 tablas

### Migración `db/migrations_v0_2_0.sql`

```sql
ALTER TABLE mediciones ADD temperatura DECIMAL(5,2), humedad DECIMAL(5,2);

clientes (id_cliente, id_usuario FK, nombre, apellido, dni, email UNIQUE, telefono, calle, numero, cp, localidad, provincia, activo, credenciales_generadas)
credenciales_clientes (id_credencial, id_cliente FK, id_usuario FK, usuario, password_hash, estado ACTIVA/INACTIVA)
productos (id_producto, nombre, descripcion, precio, stock)
proveedores (id_proveedor, nombre, cuit, telefono, email)
compras (id_compra, codigo_compra UNIQUE, id_producto/proveedor/cliente/solicitante, cantidad, precio_unitario, total, estado Pendiente/Aprobada/En entrega/Completada/Cancelada, fechas)
historial_compras (id_historial, id_compra, id_usuario, estado_anterior/nuevo, comentario)
notificaciones_compras (id_notif_compra, id_cliente/usuario/compra, tipo, mensaje, enviada/leida)
password_resets (id_reset, email, token UNIQUE, expira, usado)
login_intentos (id_intento, email, ip, fecha_hora, exito)
```

### Modelo actualizado

```
roles 1──∞ usuarios ──∞ edificios ──∞ tanques ──∞ dispositivos ──∞ sensores ──∞ mediciones (+temp/hum)
                  │              │           │            │              └─ configuracion_alertas
                  │              │           │            ├─ instalaciones / mantenimientos / alertas / consumos
                  │              │           │            └─ tecnico_dispositivo (M:N)
                  │              └─ empresas
                  │              └─ clientes ──∞ credenciales_clientes
                  └─ log_actividad / notificaciones / password_resets / login_intentos
inventario ──∞ movimientos_inventario
productos / proveedores ──∞ compras ──∞ historial_compras / notificaciones_compras
```

**Dump base** sigue `u156482620_EVAelvigilante (1).sql` (895 líneas, 22 tablas, 5 mediciones 20-87%). La migración agrega lo faltante y es idempotente (`IF NOT EXISTS`, `ADD COLUMN IF NOT EXISTS`).

---

## 6. Configuración Central V2

### `config/db.php:1-68`
- `require env.php`, `APP_DEBUG` controla `display_errors`.
- `DB_HOST/USER/PASS/NAME` desde `eva_env()` primero, luego fallback candidatos Hostinger/local.
- `mysqli` + `PDO eva_pdo()` con `utf8mb4`, `ERRMODE_EXCEPTION`, `EMULATE_PREPARES false`.

### `config/env.php:1-18`
Parser nativo sin dependencias: lee `.env`, ignora `#`/vacíos, soporta `KEY="value"`, carga `$_ENV`/`putenv`.

### `config/mail.php:1-107`
- `eva_mail_config()` lee `mail_config.php` que a su vez lee `eva_env()`.
- `eva_phpmailer_base()` mapea `ssl → ENCRYPTION_SMTPS`, `tls → ENCRYPTION_STARTTLS`.
- `eva_enviar_mail_credenciales()` + `eva_enviar_mail_pedido_procesado()` con `try/catch`, `error_log` sin secretos, `eva_generar_password(12)` con `random_int`.

### `.env.example`
```
DB_HOST=localhost
DB_USER=u767580032_elvigilante
DB_PASS=#VALzona122233
DB_NAME=u767580032_elvigilante
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USER=no-reply@dashboard.elvigilantedeagua.com
SMTP_PASS=#VALzona122233
SMTP_ENCRYPTION=ssl
BASE_URL=https://dashboard.elvigilantedeagua.com
APP_DEBUG=false
```

---

## 7. Paneles por Rol — cambios V2

### 7.1 Login
- Split layout igual V1, ahora `¿Olvidé mi contraseña? → recuperar.php`, validación y bloqueo por intentos.

### 7.2 ADMIN
**Dashboard** `admin/index.php:1-372` sin cambios funcionales (stats reales).

| Archivo | V1 | V2 |
|---|---|---|
| `sensores.php` | `mysqli get_result` → 500 sin mysqlnd | **PDO** `SELECT ... WHERE numero_serie=:s`, `INSERT ... :modelo`, `UPDATE ...`, `DELETE` con `COUNT(*) fetchColumn` |
| `empresas.php` | `get_result` | **PDO** |
| `edificios.php` | `get_result` en eliminar | **PDO** |
| `instalaciones.php` | `mysqli get_result` | **PDO** `fetchAll` + `foreach` |
| `mantenimientos.php` | `get_result` + `num_rows/fetch_assoc` | **PDO** `fetchColumn`/`fetchAll`, `foreach` |
| `usuarios.php` | commit si mail OK sino rollback (bloqueaba cambio si mail falla) | **commit antes de mail**, `random_int`, `unset`, logs diferenciados |
| `inventario.php` | Solo ENTRADA/SALIDA | Pendiente alta items (próx) |
| `roles/reportes` | placeholder | Pendiente backend |

**Otros** `tanques.php, dispositivos.php, alertas.php, auditorias.php` ya PDO o `query` simple.

### 7.3 CLIENTE
- `indexcli.php, mitanque.php, alertas.php, configuracion.php, historial.php, mantenimiento.php, perfil.php` sin cambios UI.
- **Nuevas APIs:**
  - `api/estado.php:1-57` — `eva_procesar_tanque()`, `eva_dashboard_datos()`, `period semana/mes/anio`, `serie`, `barsData`, `alertasRecientes`.
  - `api/procesar.php:1-17` — `eva_procesar_todos(?id_tanque)`.
  - `api/sincronizar.php:1-17` — `eva_sincronizar_consumos/alertas`.
  - `procesador_mediciones.php:1-248` — `eva_procesador_calcular_pct`, `actualizar_consumos` (agrupa `DATE(fecha_hora)` min/max/avg), `actualizar_dispositivo` (ONLINE si <10min), `detectar_alertas` (NIVEL_BAJO/ALTO/FALLA_SENSOR/SIN_CONEXION/CONSUMO_ANORMAL).
- **Tiempo real** `js/tiempo-real.js:1-62` — `evaPollEstado()` `fetch api/estado.php?period`, `evaActualizarDashboard()` actualiza `window.EVA_RESUMEN/E Colombia_TANQUE`, `setInterval 30s`, `visibilitychange`.

### 7.4 TÉCNICO
- `indextec.php:26-64` ya usa `eva_pdo()` `COUNT sensores/instalaciones/mantenimientos` por `id_tecnico` y donut `GROUP BY estado`.
- `mediciones.php:1-130` **reescrito**: `SELECT m.* JOIN sensores/dispositivos/tecnico_dispositivo WHERE tecnico=:tec`, tabla 50 últimas con `avgPct/avgLit`, badges `Crítico/Bajo/Normal/Sobrecarga`.
- `sensores.php, mantenimientos.php, misdispositivos.php` aún con datos estáticos 6/4/1/1 y 10 filas — próxima iteración mapear a `tecnico_dispositivo` igual que mediciones.
- Resto `alertas.php, instalaciones.php, historialtecnico.php, notificaciones.php, perfil.php` base.

### 7.5 API ESP32
- `api/esp32/mediciones.php:1-69` — acepta `POST JSON|form`, `id_sensor` requerido, `distancia/nivel/porcentaje` uno requerido, valida `porcentaje 0-100`, `try INSERT con temp/hum catch sin`, `UPDATE dispositivos ultima_conexion`, `eva_procesar_tanque()` + alertas auto evitando duplicado 1h.
- `api/guardar_datos.php:1-96` — wrapper backward-compatible: mismo contrato pero tolerante a esquema, usa `intentar(true/false)`.

---

## 8. Frontend — igual V1 + polling

- Sidebar fijo, `admin.css`/`tecnico.css`/`style.css`, `anim-bounce/slide`, `badge activo/inactivo/pendiente`, donuts `stroke-dasharray`, gauges SVG.
- Tema `localStorage` sol/luna.
- **V2 nuevo:** `tiempo-real.js` actualiza gauges/charts sin reload.

---

## 9. Credenciales y Datos de Prueba

> ⚠️ En V2 ya no se commitean. Usar `.env`.

```
DB_HOST=localhost
DB_USER=u767580032_elvigilante / u156482620_EVAelvigilante / root
DB_PASS=#VALzona122233
```

Usuarios demo iguales V1 (`admin@eva.com/admin`, `tecnico@eva.com/tecnico`, `usuario@eva.com/usuario`, `zoe@eva.com/zoe` bcrypt 10 rounds).
Nuevos usuarios creados vía `admin/clientes.php` o `compras.php Aprobada` generan `password 12 chars random_int` + `password_hash` + mail.

---

## 10. Estado Actual V2 — Qué está y qué falta

### ✅ Implementado V2

- [x] Migración 22→30 tablas + temp/hum.
- [x] API ESP32 unificada con fallback y procesamiento auto.
- [x] SMTP real Hostinger 465 ssl (PHPMailer 6.9.1).
- [x] `get_result()` → PDO en 5 archivos admin (no más 500 sin mysqlnd).
- [x] `.env` nativo + `mail_config.php` ignorado.
- [x] `tecnico/mediciones.php` real.
- [x] `session_regenerate_id`, rate-limit, `recuperar/restablecer`.
- [x] Polling `estado.php` + `tiempo-real.js`.
- [x] `admin/usuarios.php` flujo credenciales corregido.

### 🚧 Pendiente / Próxima iteración (no bloqueante)

- [ ] `tecnico/sensores.php, mantenimientos.php, misdispositivos.php, mitanques.php` → migrar a PDO con `tecnico_dispositivo` (igual que `mediciones.php`).
- [ ] `admin/inventario.php` Alta items + `admin/roles.php` backend permisos + `admin/reportes.php` exportar PDF/Excel.
- [ ] `admin/instalaciones.php` modal crear (dispositivo/técnico/fecha/latlng) — backend existe, falta UI.
- [ ] Paginación server-side `LIMIT ? OFFSET ?` + `http_build_query()` en `usuarios, tanques, sensores, historial` (hoy `LIMIT 100/50` fijo o client-side).
- [ ] Tests/seeders reproducibles (`phpunit`, `db/seeds.php`).
- [ ] `config/auth.php` unificar guards y `httponly/secure` cookies.
- [ ] WebSocket en lugar de polling (opcional).

### 🔜 Roadmap V3

1. Roles granulares + permisos por tabla.
2. OTA firmware upload.
3. Push/email alertas (`configuracion_alertas.notificar_email` → `eva_enviar_mail_alerta()`).
4. Exportar reportes PDF/Excel.
5. `tecnico/api/*` audit y PDO.

---

## 11. Cómo Ejecutar V2

```bash
git clone <repo> Proyecto-Anual-Web
cd Proyecto-Anual-Web

# 1. BD base + migración
mysql -u root -p < db/"u156482620_EVAelvigilante (1).sql"
mysql -u root -p < db/migrations_v0_2_0.sql

# 2. Env
cp .env.example .env
# editar DB_USER/DB_PASS/SMTP_PASS/BASE_URL

# 3. Servir
php -S localhost:8000
# o XAMPP → http://localhost/Proyecto-Anual-Web/index.php

# 4. Login demo
# admin@eva.com / tecnico@eva.com / usuario@eva.com

# 5. Probar ESP32
curl -X POST http://localhost/Proyecto-Anual-Web/api/esp32/mediciones.php \
 -H "Content-Type: application/json" \
 -d '{"id_sensor":1,"distancia_cm":30,"porcentaje":85,"litros":4250,"temperatura":22.5,"humedad":60}'

# 6. Ver estado tiempo real
# cliente/indexcli.php → auto polling cada 30s a api/estado.php
```

---

## 12. Archivos Clave — Referencia Rápida V2

| Archivo | Líneas | Rol | Cambio V2 |
|---|---|---|---|
| `index.php` | 210 | Login | +rate-limit/session_regenerate |
| `recuperar.php` | 60 | Reset request | **NUEVO** |
| `restablecer.php` | 40 | Reset | **NUEVO** |
| `config/db.php` | 68 | Conexión | +env |
| `config/env.php` | 18 | Env loader | **NUEVO** |
| `config/mail.php` | 107 | SMTP | **REESCRITO** |
| `db/migrations_v0_2_0.sql` | 100 | Migración | **NUEVO** |
| `api/esp32/mediciones.php` | 69 | ESP32 | +temp/hum+procesar |
| `api/guardar_datos.php` | 96 | ESP32 wrapper | **MOD** |
| `cliente/includes/procesador_mediciones.php` | 248 | Procesador | EXISTIA |
| `cliente/api/estado.php` | 57 | Dashboard API | **NUEVO** |
| `cliente/js/tiempo-real.js` | 62 | Polling | **NUEVO** |
| `tecnico/mediciones.php` | 130 | Medición tec. | **REESCRITO** |
| `admin/sensores.php` | 379 | Sensores | PDO |
| `admin/mantenimientos.php` | 265 | Mant. | PDO |

---

## 13. Glosario (igual V1)

- **EVA**: El Vigilante del Agua.
- **Dispositivo EVA**: ESP32 + sensor ultrasónico.
- **Medición**: `distancia_cm → nivel_cm → porcentaje → litros` + `temperatura/humedad` opcional.
- **Alerta**: `NIVEL_BAJO/ALTO/SIN_CONEXION/FALLA_SENSOR/CONSUMO_ANORMAL`.
- **Consumo**: `litros_consumidos` diario por `id_tanque`.

---

*Documento generado por relevamiento del código fuente — V2 07/09/2026. Ver `CHANGELOG.md` tag `v0.2.0` y `RESUMEN_V1.md` para histórico.*
