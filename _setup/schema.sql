-- Base de datos y tablas del sistema de ventas navideño
CREATE DATABASE IF NOT EXISTS tienda_navidad
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda_navidad;

-- Usuarios del sistema (por ahora un solo administrador)
CREATE TABLE IF NOT EXISTS usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    usuario       VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creado_en     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catálogo de productos
CREATE TABLE IF NOT EXISTS productos (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100)   NOT NULL,
    precio     DECIMAL(10,2)  NOT NULL DEFAULT 0,
    disponible TINYINT(1)     NOT NULL DEFAULT 1,
    creado_en  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ventas (una fila = una línea vendida)
CREATE TABLE IF NOT EXISTS ventas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT            NOT NULL,
    producto_id     INT            NOT NULL,
    producto_nombre VARCHAR(100)   NOT NULL, -- copia del nombre al momento de vender
    cantidad        INT            NOT NULL,
    precio_unitario DECIMAL(10,2)  NOT NULL,
    total           DECIMAL(10,2)  NOT NULL,
    fecha           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha),
    CONSTRAINT fk_ventas_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id),
    CONSTRAINT fk_ventas_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;