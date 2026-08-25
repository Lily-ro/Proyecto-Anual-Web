-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 25-08-2026 a las 21:43:38
-- Versión del servidor: 11.8.8-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u156482620_EVAelvigilante`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `id_alerta` bigint(20) NOT NULL,
  `id_tanque` int(11) NOT NULL,
  `tipo` enum('NIVEL_BAJO','NIVEL_ALTO','SIN_CONEXION','FALLA_SENSOR','CONSUMO_ANORMAL') DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `estado` enum('PENDIENTE','ATENDIDA','CERRADA') DEFAULT 'PENDIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alertas`
--

INSERT INTO `alertas` (`id_alerta`, `id_tanque`, `tipo`, `descripcion`, `fecha_hora`, `estado`) VALUES
(1, 1, 'NIVEL_BAJO', 'El nivel del tanque cayó por debajo del 20% configurado.', '2026-07-25 08:00:01', 'ATENDIDA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id_archivo` bigint(20) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `id_tanque` int(11) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_alertas`
--

CREATE TABLE `configuracion_alertas` (
  `id_config` int(11) NOT NULL,
  `id_tanque` int(11) NOT NULL,
  `nivel_minimo` decimal(5,2) DEFAULT 15.00,
  `nivel_maximo` decimal(5,2) DEFAULT 95.00,
  `notificar_email` tinyint(1) DEFAULT 1,
  `notificar_sistema` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_alertas`
--

INSERT INTO `configuracion_alertas` (`id_config`, `id_tanque`, `nivel_minimo`, `nivel_maximo`, `notificar_email`, `notificar_sistema`) VALUES
(1, 1, 20.00, 90.00, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consumos`
--

CREATE TABLE `consumos` (
  `id_consumo` bigint(20) NOT NULL,
  `id_tanque` int(11) DEFAULT NULL,
  `litros_consumidos` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `consumos`
--

INSERT INTO `consumos` (`id_consumo`, `id_tanque`, `litros_consumidos`, `fecha`) VALUES
(1, 1, 3200.50, '2026-07-24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id_dispositivo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `mac_address` varchar(50) DEFAULT NULL,
  `ip_local` varchar(50) DEFAULT NULL,
  `direccion_instalacion` varchar(255) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `firmware` varchar(50) DEFAULT NULL,
  `fecha_instalacion` datetime DEFAULT NULL,
  `ultima_conexion` datetime DEFAULT NULL,
  `estado` enum('ONLINE','OFFLINE','MANTENIMIENTO') DEFAULT 'OFFLINE',
  `id_tanque` int(11) NOT NULL,
  `id_firmware` int(11) DEFAULT NULL,
  `bateria` decimal(5,2) DEFAULT 100.00,
  `intensidad_senal` int(11) DEFAULT 100,
  `frecuencia_envio` int(11) DEFAULT 60,
  `ultima_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id_dispositivo`, `nombre`, `mac_address`, `ip_local`, `direccion_instalacion`, `latitud`, `longitud`, `firmware`, `fecha_instalacion`, `ultima_conexion`, `estado`, `id_tanque`, `id_firmware`, `bateria`, `intensidad_senal`, `frecuencia_envio`, `ultima_actualizacion`) VALUES
(1, 'Nodo EVA #01 - Olivos', '24:6F:28:AB:12:34', '192.168.1.105', 'Av. del Libertador 2200 - Terraza', -34.51234500, -58.48123400, 'v2.1.0-ESP32', '2026-01-15 10:30:00', '2026-07-25 14:10:00', 'ONLINE', 1, 1, 98.50, -65, 30, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edificios`
--

CREATE TABLE `edificios` (
  `id_edificio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `fecha_alta` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `codigo` varchar(30) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `edificios`
--

INSERT INTO `edificios` (`id_edificio`, `nombre`, `direccion`, `ciudad`, `provincia`, `pais`, `fecha_alta`, `id_usuario`, `codigo`, `telefono`, `responsable`) VALUES
(1, 'Edificio Olivos I', 'Av. del Libertador 2200', 'Vicente López', 'Buenos Aires', 'Argentina', '2026-07-25 17:17:41', 3, 'EDIF-OLV-01', '011-4799-8800', 'Roberto Gómez');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `fecha_alta` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `nombre`, `cuit`, `telefono`, `email`, `direccion`, `ciudad`, `provincia`, `pais`, `fecha_alta`, `activo`) VALUES
(1, 'Consorcios Norte S.A.', '30-71234567-8', '011-4790-1122', 'contacto@consorciosnorte.com', 'Av. Maipú 1500', 'Vicente López', 'Buenos Aires', 'Argentina', '2026-07-25 17:17:41', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `firmware`
--

CREATE TABLE `firmware` (
  `id_firmware` int(11) NOT NULL,
  `version` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_publicacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `firmware`
--

INSERT INTO `firmware` (`id_firmware`, `version`, `descripcion`, `fecha_publicacion`) VALUES
(1, 'v2.1.0-ESP32', 'Firmware estable con reconexión automática WiFi y lectura ultrasónica optimizada', '2026-03-01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_estado_dispositivo`
--

CREATE TABLE `historial_estado_dispositivo` (
  `id_historial` bigint(20) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `estado` enum('ONLINE','OFFLINE','MANTENIMIENTO','FALLA') DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instalaciones`
--

CREATE TABLE `instalaciones` (
  `id_instalacion` bigint(20) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `id_tecnico` int(11) NOT NULL,
  `fecha_instalacion` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_item` int(11) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `categoria` enum('SENSOR','DISPOSITIVO','BATERIA','ANTENA','CABLE','REPUESTO','OTRO') DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_item`, `nombre`, `categoria`, `modelo`, `stock`, `stock_minimo`, `ubicacion`, `fecha_actualizacion`) VALUES
(1, 'Sensor Ultrasónico Impermeable', 'SENSOR', 'JSR-SR04T', 8, 3, 'Depósito Central - Estante A2', '2026-07-25 17:17:41'),
(2, 'Microcontrolador ESP32-WROOM-32', 'DISPOSITIVO', 'ESP32-DEV-V1', 12, 5, 'Depósito Central - Estante B1', '2026-07-25 17:17:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_actividad`
--

CREATE TABLE `log_actividad` (
  `id_log` bigint(20) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `log_actividad`
--

INSERT INTO `log_actividad` (`id_log`, `id_usuario`, `accion`, `detalle`, `ip`, `fecha_hora`) VALUES
(1, 1, 'CREATE', 'Creó el usuario zoe@eva.com', '181.104.119.237', '2026-08-05 23:35:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id_mantenimiento` bigint(20) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `id_tecnico` int(11) NOT NULL,
  `tipo` enum('PREVENTIVO','CORRECTIVO','PREDICTIVO') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_programada` datetime DEFAULT NULL,
  `fecha_realizada` datetime DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `estado` enum('PENDIENTE','EN_PROCESO','FINALIZADO','CANCELADO') DEFAULT 'PENDIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mantenimientos`
--

INSERT INTO `mantenimientos` (`id_mantenimiento`, `id_dispositivo`, `id_tecnico`, `tipo`, `descripcion`, `fecha_programada`, `fecha_realizada`, `costo`, `observaciones`, `estado`) VALUES
(1, 1, 2, 'PREVENTIVO', 'Verificación de estanqueidad, calibración del ultrasónico y chequeo de señal WiFi.', '2026-06-10 10:00:00', '2026-08-19 23:46:19', 0.00, 'Equipo funcionando en óptimas condiciones.', 'FINALIZADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mediciones`
--

CREATE TABLE `mediciones` (
  `id_medicion` bigint(20) NOT NULL,
  `id_sensor` int(11) NOT NULL,
  `distancia_cm` decimal(10,2) DEFAULT NULL,
  `nivel_cm` decimal(10,2) DEFAULT NULL,
  `porcentaje` decimal(5,2) DEFAULT NULL,
  `litros` decimal(10,2) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mediciones`
--

INSERT INTO `mediciones` (`id_medicion`, `id_sensor`, `distancia_cm`, `nivel_cm`, `porcentaje`, `litros`, `fecha_hora`) VALUES
(1, 1, 160.00, 40.00, 20.00, 1000.00, '2026-07-25 08:00:00'),
(2, 1, 120.00, 80.00, 40.00, 2000.00, '2026-07-25 09:00:00'),
(3, 1, 60.00, 140.00, 70.00, 3500.00, '2026-07-25 10:30:00'),
(4, 1, 30.00, 170.00, 85.00, 4250.00, '2026-07-25 12:00:00'),
(5, 1, 25.00, 175.00, 87.50, 4375.00, '2026-07-25 14:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id_movimiento` bigint(20) NOT NULL,
  `id_item` int(11) NOT NULL,
  `tipo` enum('ENTRADA','SALIDA') DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` bigint(20) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `mensaje`, `leida`, `fecha_hora`) VALUES
(1, 3, 'Alerta detectada: Nivel Bajo en Tanque Principal Copete (20%).', 1, '2026-07-25 08:00:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'ADMIN', 'Control total del sistema'),
(2, 'TECNICO', 'Instalacion y mantenimiento'),
(3, 'USUARIO', 'Usuario final del sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sensores`
--

CREATE TABLE `sensores` (
  `id_sensor` int(11) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `fecha_instalacion` date DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO','FALLA') DEFAULT 'ACTIVO',
  `id_dispositivo` int(11) NOT NULL,
  `fabricante` varchar(100) DEFAULT NULL,
  `precision_sensor` decimal(5,2) DEFAULT NULL,
  `rango_min` decimal(10,2) DEFAULT NULL,
  `rango_max` decimal(10,2) DEFAULT NULL,
  `fecha_calibracion` date DEFAULT NULL,
  `calibrado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sensores`
--

INSERT INTO `sensores` (`id_sensor`, `modelo`, `numero_serie`, `fecha_instalacion`, `estado`, `id_dispositivo`, `fabricante`, `precision_sensor`, `rango_min`, `rango_max`, `fecha_calibracion`, `calibrado`) VALUES
(1, 'JSR-SR04T (Sumergible/Estanco)', 'SN-HC04T-202601', '2026-01-15', 'ACTIVO', 1, 'Ultrasonic Tech', 0.50, 20.00, 450.00, '2026-01-15', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tanques`
--

CREATE TABLE `tanques` (
  `id_tanque` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `capacidad_litros` decimal(10,2) NOT NULL,
  `altura_cm` decimal(10,2) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_instalacion` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `id_edificio` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `diametro` decimal(10,2) DEFAULT NULL,
  `volumen_util` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tanques`
--

INSERT INTO `tanques` (`id_tanque`, `nombre`, `capacidad_litros`, `altura_cm`, `ubicacion`, `descripcion`, `fecha_instalacion`, `activo`, `id_edificio`, `tipo`, `material`, `diametro`, `volumen_util`) VALUES
(1, 'Tanque Principal Copete', 5000.00, 200.00, 'Terraza / Azotea', 'Tanque elevador de agua potable', '2026-01-15', 1, 1, 'Elevado', 'Hormigón Armado', 180.00, 4500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnico_dispositivo`
--

CREATE TABLE `tecnico_dispositivo` (
  `id_tecnico` int(11) NOT NULL,
  `id_dispositivo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `ultimo_acceso` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `id_rol` int(11) NOT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `email`, `password_hash`, `telefono`, `fecha_registro`, `ultimo_acceso`, `activo`, `id_rol`, `id_empresa`, `foto`, `dni`, `cargo`) VALUES
(1, 'Administrador', 'Sistema', 'admin@eva.com', '$2y$10$WgiwZTTDhqFUBtiZLLWY0um7g9j3ZMIxTvS2cfWP2mFDuqcc1E/bi', '1111111111', '2026-06-17 23:13:59', '2026-08-25 21:24:13', 1, 1, NULL, NULL, NULL, NULL),
(2, 'Carlos', 'Tecnico', 'tecnico@eva.com', '$2y$10$m2C2JAAlzjJOqey.nFMjr.RxJHuJtdS9oCfsONaqnejbZDrSAzxXm', '2222222222', '2026-06-17 23:13:59', '2026-08-19 23:45:43', 1, 2, NULL, NULL, NULL, NULL),
(3, 'Juan', 'Usuario', 'usuario@eva.com', '$2y$10$6n/cEPhWiziCqWh2GKc3Ve1EkFwU/1xcl5Btbdel/L2XOoj4Xefhq', '3333333333', '2026-06-17 23:13:59', '2026-08-24 17:24:07', 1, 3, NULL, NULL, NULL, NULL),
(4, 'Zoe', '', 'zoe@eva.com', '$2y$10$M5akS8PMTCmUzs29xeeNy.ogtGw7AYPJ5XopwfYRJZRHea7lZ00be', '', '2026-08-05 23:35:19', NULL, 1, 2, NULL, NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id_alerta`),
  ADD KEY `id_tanque` (`id_tanque`),
  ADD KEY `idx_alertas_fecha` (`fecha_hora`),
  ADD KEY `idx_alertas_estado` (`estado`);

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id_archivo`),
  ADD KEY `id_tanque` (`id_tanque`);

--
-- Indices de la tabla `configuracion_alertas`
--
ALTER TABLE `configuracion_alertas`
  ADD PRIMARY KEY (`id_config`),
  ADD KEY `id_tanque` (`id_tanque`);

--
-- Indices de la tabla `consumos`
--
ALTER TABLE `consumos`
  ADD PRIMARY KEY (`id_consumo`),
  ADD KEY `id_tanque` (`id_tanque`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id_dispositivo`),
  ADD UNIQUE KEY `mac_address` (`mac_address`),
  ADD KEY `id_tanque` (`id_tanque`),
  ADD KEY `idx_dispositivos_estado` (`estado`),
  ADD KEY `fk_dispositivo_firmware` (`id_firmware`);

--
-- Indices de la tabla `edificios`
--
ALTER TABLE `edificios`
  ADD PRIMARY KEY (`id_edificio`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `firmware`
--
ALTER TABLE `firmware`
  ADD PRIMARY KEY (`id_firmware`);

--
-- Indices de la tabla `historial_estado_dispositivo`
--
ALTER TABLE `historial_estado_dispositivo`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD PRIMARY KEY (`id_instalacion`),
  ADD KEY `id_dispositivo` (`id_dispositivo`),
  ADD KEY `id_tecnico` (`id_tecnico`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_item`);

--
-- Indices de la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `id_dispositivo` (`id_dispositivo`),
  ADD KEY `id_tecnico` (`id_tecnico`);

--
-- Indices de la tabla `mediciones`
--
ALTER TABLE `mediciones`
  ADD PRIMARY KEY (`id_medicion`),
  ADD KEY `idx_mediciones_fecha` (`fecha_hora`),
  ADD KEY `idx_mediciones_sensor` (`id_sensor`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_item` (`id_item`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `sensores`
--
ALTER TABLE `sensores`
  ADD PRIMARY KEY (`id_sensor`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `tanques`
--
ALTER TABLE `tanques`
  ADD PRIMARY KEY (`id_tanque`),
  ADD KEY `id_edificio` (`id_edificio`);

--
-- Indices de la tabla `tecnico_dispositivo`
--
ALTER TABLE `tecnico_dispositivo`
  ADD PRIMARY KEY (`id_tecnico`,`id_dispositivo`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `idx_usuarios_email` (`email`),
  ADD KEY `fk_usuario_empresa` (`id_empresa`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id_alerta` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id_archivo` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_alertas`
--
ALTER TABLE `configuracion_alertas`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `consumos`
--
ALTER TABLE `consumos`
  MODIFY `id_consumo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id_dispositivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `edificios`
--
ALTER TABLE `edificios`
  MODIFY `id_edificio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `firmware`
--
ALTER TABLE `firmware`
  MODIFY `id_firmware` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `historial_estado_dispositivo`
--
ALTER TABLE `historial_estado_dispositivo`
  MODIFY `id_historial` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  MODIFY `id_instalacion` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  MODIFY `id_log` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id_mantenimiento` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `mediciones`
--
ALTER TABLE `mediciones`
  MODIFY `id_medicion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id_movimiento` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sensores`
--
ALTER TABLE `sensores`
  MODIFY `id_sensor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tanques`
--
ALTER TABLE `tanques`
  MODIFY `id_tanque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`id_tanque`) REFERENCES `tanques` (`id_tanque`);

--
-- Filtros para la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD CONSTRAINT `archivos_ibfk_1` FOREIGN KEY (`id_tanque`) REFERENCES `tanques` (`id_tanque`);

--
-- Filtros para la tabla `configuracion_alertas`
--
ALTER TABLE `configuracion_alertas`
  ADD CONSTRAINT `configuracion_alertas_ibfk_1` FOREIGN KEY (`id_tanque`) REFERENCES `tanques` (`id_tanque`);

--
-- Filtros para la tabla `consumos`
--
ALTER TABLE `consumos`
  ADD CONSTRAINT `consumos_ibfk_1` FOREIGN KEY (`id_tanque`) REFERENCES `tanques` (`id_tanque`);

--
-- Filtros para la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `dispositivos_ibfk_1` FOREIGN KEY (`id_tanque`) REFERENCES `tanques` (`id_tanque`),
  ADD CONSTRAINT `fk_dispositivo_firmware` FOREIGN KEY (`id_firmware`) REFERENCES `firmware` (`id_firmware`);

--
-- Filtros para la tabla `edificios`
--
ALTER TABLE `edificios`
  ADD CONSTRAINT `edificios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `historial_estado_dispositivo`
--
ALTER TABLE `historial_estado_dispositivo`
  ADD CONSTRAINT `historial_estado_dispositivo_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos` (`id_dispositivo`);

--
-- Filtros para la tabla `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD CONSTRAINT `instalaciones_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos` (`id_dispositivo`),
  ADD CONSTRAINT `instalaciones_ibfk_2` FOREIGN KEY (`id_tecnico`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  ADD CONSTRAINT `log_actividad_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos` (`id_dispositivo`),
  ADD CONSTRAINT `mantenimientos_ibfk_2` FOREIGN KEY (`id_tecnico`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `mediciones`
--
ALTER TABLE `mediciones`
  ADD CONSTRAINT `mediciones_ibfk_1` FOREIGN KEY (`id_sensor`) REFERENCES `sensores` (`id_sensor`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`id_item`) REFERENCES `inventario` (`id_item`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `sensores`
--
ALTER TABLE `sensores`
  ADD CONSTRAINT `sensores_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos` (`id_dispositivo`);

--
-- Filtros para la tabla `tanques`
--
ALTER TABLE `tanques`
  ADD CONSTRAINT `tanques_ibfk_1` FOREIGN KEY (`id_edificio`) REFERENCES `edificios` (`id_edificio`);

--
-- Filtros para la tabla `tecnico_dispositivo`
--
ALTER TABLE `tecnico_dispositivo`
  ADD CONSTRAINT `tecnico_dispositivo_ibfk_1` FOREIGN KEY (`id_tecnico`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `tecnico_dispositivo_ibfk_2` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos` (`id_dispositivo`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
