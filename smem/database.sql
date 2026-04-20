-- =====================================================
-- SMEM - Sistema de Monitoreo de Ecosistemas Marinos
-- Base de Datos - Ejecutar una sola vez en phpMyAdmin
-- =====================================================

CREATE DATABASE IF NOT EXISTS smem_db 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE smem_db;

-- =====================================================
-- TABLA: registros (datos oceanográficos)
-- =====================================================
CREATE TABLE IF NOT EXISTS registros (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  codigo      VARCHAR(20) NOT NULL UNIQUE,
  fecha       DATE NOT NULL,
  zona        VARCHAR(50) NOT NULL,
  latitud     VARCHAR(30) DEFAULT '0.0000° N',
  longitud    VARCHAR(30) DEFAULT '0.0000° W',
  temperatura DECIMAL(5,2) NOT NULL DEFAULT 19.0,
  ph          DECIMAL(4,2) NOT NULL DEFAULT 8.0,
  salinidad   DECIMAL(5,2) NOT NULL DEFAULT 35.0,
  oxigeno     DECIMAL(5,2) DEFAULT 7.0,
  corriente   DECIMAL(4,2) DEFAULT 2.0,
  tecnico     VARCHAR(100) NOT NULL,
  estado      ENUM('Validado','Pendiente','En Revisión') NOT NULL DEFAULT 'Pendiente',
  notas       TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLA: alertas
-- =====================================================
CREATE TABLE IF NOT EXISTS alertas (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  tipo        ENUM('warn','info','success','danger') NOT NULL DEFAULT 'info',
  mensaje     VARCHAR(255) NOT NULL,
  zona        VARCHAR(50),
  leida       TINYINT(1) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLA: especies
-- =====================================================
CREATE TABLE IF NOT EXISTS especies (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nombre          VARCHAR(100) NOT NULL,
  nombre_cientifico VARCHAR(150),
  estado_conservacion ENUM('Vulnerable','En Peligro Crítico','Preocupación Menor','Casi Amenazada','En Peligro') DEFAULT 'Vulnerable',
  avistamientos   INT DEFAULT 0,
  tendencia       ENUM('up','down','stable') DEFAULT 'stable',
  zona            VARCHAR(50),
  ultima_vez      DATE,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLA: puntos_monitoreo
-- =====================================================
CREATE TABLE IF NOT EXISTS puntos_monitoreo (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80) NOT NULL,
  latitud     DECIMAL(10,6) NOT NULL,
  longitud    DECIMAL(10,6) NOT NULL,
  estado      ENUM('active','warn','inactive') DEFAULT 'active',
  descripcion VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(100) NOT NULL,
  rol         VARCHAR(60) DEFAULT 'Técnico',
  email       VARCHAR(150) UNIQUE,
  avatar_iniciales CHAR(3),
  activo      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DATOS INICIALES
-- =====================================================

INSERT IGNORE INTO usuarios (nombre, rol, email, avatar_iniciales) VALUES
('Dr. María González',    'Investigadora Senior', 'mgonzalez@smem.org', 'MG'),
('Ing. Carlos Ruiz',      'Técnico de Campo',     'cruiz@smem.org',     'CR'),
('Dra. Ana Martínez',     'Oceanógrafa',           'amartinez@smem.org', 'AM'),
('Dr. Pedro Sánchez',     'Biólogo Marino',        'psanchez@smem.org',  'PS'),
('Ing. Laura Torres',     'Analista de Datos',     'ltorres@smem.org',   'LT');

INSERT IGNORE INTO puntos_monitoreo (nombre, latitud, longitud, estado, descripcion) VALUES
('Punto Norte A',   40.7128,  -74.0060, 'active',   'Zona costera norte, alta biodiversidad'),
('Punto Central B', 34.0522, -118.2437, 'warn',     'Zona central, monitoreo de contaminación'),
('Punto Este C',    41.8781,  -87.6298, 'active',   'Zona este, corrientes oceánicas'),
('Punto Oeste D',   37.7749, -122.4194, 'active',   'Zona oeste, zona de reproducción'),
('Punto Sur E',     39.7392, -104.9903, 'inactive', 'Zona sur, en mantenimiento');

INSERT IGNORE INTO registros (codigo,fecha,zona,latitud,longitud,temperatura,ph,salinidad,oxigeno,corriente,tecnico,estado) VALUES
('REC-001','2026-03-07','Zona Norte','40.7128° N','74.0060° W',  19.2,8.1,35.2,7.4,2.1,'Dr. María González',  'Validado'),
('REC-002','2026-03-07','Zona Sur',  '34.0522° N','118.2437° W', 20.5,8.0,35.4,7.2,1.8,'Ing. Carlos Ruiz',    'Validado'),
('REC-003','2026-03-06','Zona Este', '41.8781° N','87.6298° W',  18.8,7.9,35.1,6.9,2.5,'Dra. Ana Martínez',   'Pendiente'),
('REC-004','2026-03-06','Zona Oeste','37.7749° N','122.4194° W', 19.5,8.2,35.3,7.6,1.5,'Dr. Pedro Sánchez',   'Validado'),
('REC-005','2026-03-05','Zona Central','39.7392° N','104.9903° W',19.0,8.0,35.2,7.1,2.0,'Ing. Laura Torres',  'En Revisión'),
('REC-006','2026-03-04','Zona Norte','40.7128° N','74.0060° W',  19.8,8.15,35.5,7.5,2.3,'Dr. María González', 'Validado'),
('REC-007','2026-03-03','Zona Sur',  '34.0522° N','118.2437° W', 20.1,8.05,35.3,7.3,1.9,'Ing. Carlos Ruiz',   'Validado'),
('REC-008','2026-03-02','Zona Este', '41.8781° N','87.6298° W',  18.5,8.18,35.0,7.0,2.7,'Dra. Ana Martínez',  'Validado'),
('REC-009','2026-03-01','Zona Norte','40.7128° N','74.0060° W',  19.0,8.1, 35.1,7.2,2.0,'Dr. Pedro Sánchez',  'Pendiente'),
('REC-010','2026-02-28','Zona Central','39.7392° N','104.9903° W',18.9,7.95,35.2,6.8,2.2,'Ing. Laura Torres',  'Validado');

INSERT IGNORE INTO alertas (tipo, mensaje, zona) VALUES
('warn',    'pH bajo detectado en Punto Este C',             'Zona Este'),
('info',    'Aumento de temperatura en Zona Norte',          'Zona Norte'),
('success', 'Avistamiento de tortugas verdes confirmado',    'Zona Sur'),
('info',    'Nuevo registro ingresado en Zona Oeste',        'Zona Oeste'),
('warn',    'Salinidad fuera de rango en Punto Central B',   'Zona Central');

INSERT IGNORE INTO especies (nombre, nombre_cientifico, estado_conservacion, avistamientos, tendencia, zona) VALUES
('Tortuga Verde',       'Chelonia mydas',           'Vulnerable',           234, 'up',     'Zona Sur'),
('Delfín Mular',        'Tursiops truncatus',       'Preocupación Menor',    89, 'stable', 'Zona Norte'),
('Tiburón Martillo',    'Sphyrna mokarran',         'En Peligro Crítico',    45, 'down',   'Zona Este'),
('Mantarraya Gigante',  'Mobula birostris',         'Vulnerable',            67, 'up',     'Zona Oeste'),
('Caballito de Mar',    'Hippocampus hippocampus',  'Vulnerable',            28, 'down',   'Zona Central');

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
SELECT 'Base de datos SMEM creada exitosamente ✅' AS resultado;
