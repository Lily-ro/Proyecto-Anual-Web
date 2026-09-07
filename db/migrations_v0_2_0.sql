-- EVA v0.2.0 Migracion
-- Agrega temperatura/humedad a mediciones y crea tablas faltantes

ALTER TABLE `mediciones` ADD COLUMN IF NOT EXISTS `temperatura` DECIMAL(5,2) DEFAULT NULL AFTER `litros`;
ALTER TABLE `mediciones` ADD COLUMN IF NOT EXISTS `humedad` DECIMAL(5,2) DEFAULT NULL AFTER `temperatura`;

CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) DEFAULT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `dni` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `calle` VARCHAR(100) DEFAULT NULL,
  `numero` VARCHAR(20) DEFAULT NULL,
  `codigo_postal` VARCHAR(20) DEFAULT NULL,
  `localidad` VARCHAR(100) DEFAULT NULL,
  `provincia` VARCHAR(100) DEFAULT NULL,
  `pais` VARCHAR(100) DEFAULT 'Argentina',
  `activo` TINYINT(1) DEFAULT 1,
  `credenciales_generadas` TINYINT(1) DEFAULT 0,
  `fecha_credenciales` DATETIME DEFAULT NULL,
  `fecha_alta` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `email` (`email`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `credenciales_clientes` (
  `id_credencial` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` INT(11) DEFAULT NULL,
  `id_usuario` INT(11) DEFAULT NULL,
  `usuario` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `estado` ENUM('ACTIVA','INACTIVA') DEFAULT 'ACTIVA',
  `fecha_generacion` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `fecha_activacion` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_credencial`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `cred_cli_ibfk1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE,
  CONSTRAINT `cred_cli_ibfk2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `precio` DECIMAL(10,2) DEFAULT 0.00,
  `stock` INT(11) DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `proveedores` (
  `id_proveedor` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NOT NULL,
  `cuit` VARCHAR(20) DEFAULT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `compras` (
  `id_compra` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_compra` VARCHAR(30) NOT NULL,
  `id_producto` INT(11) DEFAULT NULL,
  `id_proveedor` INT(11) DEFAULT NULL,
  `id_cliente` INT(11) DEFAULT NULL,
  `id_solicitante` INT(11) DEFAULT NULL,
  `cantidad` INT(11) DEFAULT 1,
  `precio_unitario` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) DEFAULT 0.00,
  `estado` ENUM('Pendiente','Aprobada','En entrega','Completada','Cancelada') DEFAULT 'Pendiente',
  `fecha` DATE DEFAULT NULL,
  `fecha_aprobacion` DATETIME DEFAULT NULL,
  `fecha_entrega` DATETIME DEFAULT NULL,
  `fecha_completada` DATETIME DEFAULT NULL,
  `fecha_cancelacion` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_compra`),
  UNIQUE KEY `codigo_compra` (`codigo_compra`),
  KEY `id_producto` (`id_producto`),
  KEY `id_proveedor` (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `historial_compras` (
  `id_historial` INT(11) NOT NULL AUTO_INCREMENT,
  `id_compra` INT(11) NOT NULL,
  `id_usuario` INT(11) DEFAULT NULL,
  `estado_anterior` VARCHAR(30) DEFAULT NULL,
  `estado_nuevo` VARCHAR(30) DEFAULT NULL,
  `comentario` TEXT DEFAULT NULL,
  `fecha_hora` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_historial`),
  KEY `id_compra` (`id_compra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notificaciones_compras` (
  `id_notif_compra` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` INT(11) DEFAULT NULL,
  `id_usuario` INT(11) DEFAULT NULL,
  `id_compra` INT(11) DEFAULT NULL,
  `tipo` VARCHAR(30) DEFAULT NULL,
  `mensaje` TEXT DEFAULT NULL,
  `enviada` TINYINT(1) DEFAULT 0,
  `leida` TINYINT(1) DEFAULT 0,
  `fecha_envio` DATETIME DEFAULT NULL,
  `fecha_hora` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_notif_compra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id_reset` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `token` VARCHAR(100) NOT NULL,
  `expira` DATETIME NOT NULL,
  `usado` TINYINT(1) DEFAULT 0,
  `creado` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_reset`),
  UNIQUE KEY `token` (`token`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_intentos` (
  `id_intento` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `fecha_hora` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `exito` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_intento`),
  KEY `email` (`email`),
  KEY `fecha_hora` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed productos/proveedores demo si vacios
INSERT INTO `productos` (`nombre`,`descripcion`,`precio`) SELECT 'Sensor JSR-SR04T','Sensor ultrasonico para tanque',18500.00 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM productos LIMIT 1);
INSERT INTO `proveedores` (`nombre`,`cuit`) SELECT 'Proveedor EVA Central','30-00000000-0' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM proveedores LIMIT 1);
