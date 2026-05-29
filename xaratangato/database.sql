-- ============================================================
--  XARATANGATO - Base de Datos
-- ============================================================

CREATE DATABASE IF NOT EXISTS xaratangato CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xaratangato;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol ENUM('admin', 'cliente') DEFAULT 'cliente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de habitaciones
CREATE TABLE IF NOT EXISTS habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    capacidad INT NOT NULL,
    precio_noche DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255),
    disponible TINYINT(1) DEFAULT 1,
    tipo ENUM('sencilla','doble','suite','cabaña','glamping') DEFAULT 'sencilla'
);

-- Tabla de actividades
CREATE TABLE IF NOT EXISTS actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    duracion VARCHAR(50),
    capacidad_max INT DEFAULT 10,
    imagen VARCHAR(255),
    disponible TINYINT(1) DEFAULT 1
);

-- Tabla de equipo/equipamiento
CREATE TABLE IF NOT EXISTS equipo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('bicicleta','cuatrimoto','kayak','otro') NOT NULL,
    descripcion TEXT,
    precio_hora DECIMAL(10,2),
    precio_dia DECIMAL(10,2),
    imagen VARCHAR(255),
    cantidad_total INT DEFAULT 1,
    disponible TINYINT(1) DEFAULT 1
);

-- Tabla de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('habitacion','actividad','equipo') NOT NULL,
    item_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    hora_inicio TIME,
    hora_fin TIME,
    cantidad INT DEFAULT 1,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
--  DATOS INICIALES
-- ============================================================

-- Admin por defecto (password: admin123)
INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
('Admin', 'Xaratangato', 'admin@xaratangato.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Habitaciones
INSERT INTO habitaciones (nombre, descripcion, capacidad, precio_noche, tipo) VALUES
('Cabaña Bosque', 'Cabaña rodeada de pinos con chimenea y vista al bosque. Incluye desayuno.', 2, 1800.00, 'cabaña'),
('Glamping Estrella', 'Tienda glamping de lujo con cama king, jacuzzi privado y cielo estrellado.', 2, 2500.00, 'glamping'),
('Suite Xaratangato', 'Suite premium con terraza privada, sala y vista panorámica a la montaña.', 4, 3200.00, 'suite'),
('Habitación Doble Jardín', 'Cómoda habitación con acceso directo al jardín y área de descanso.', 2, 1200.00, 'doble'),
('Cabaña Familiar', 'Cabaña espaciosa para toda la familia con sala, cocina y 2 recámaras.', 6, 2800.00, 'cabaña'),
('Habitación Sencilla', 'Habitación acogedora ideal para viajero solo o pareja.', 2, 950.00, 'sencilla');

-- Actividades
INSERT INTO actividades (nombre, descripcion, precio, duracion, capacidad_max) VALUES
('Senderismo al Cerro', 'Caminata guiada al cerro principal con vistas espectaculares. Incluye equipo y guía.', 350.00, '4 horas', 12),
('Tirolesa Extrema', 'Adrenalina pura cruzando el cañón en tirolesa profesional de 800m.', 450.00, '2 horas', 8),
('Taller de Cocina Regional', 'Aprende a preparar platillos típicos queretanos con chef local.', 500.00, '3 horas', 10),
('Noche de Astronomía', 'Observación de estrellas con telescopios profesionales y guía especializado.', 280.00, '2 horas', 15),
('Tour en Jeep', 'Recorrido todoterreno por los parajes naturales de la zona.', 600.00, '3 horas', 6),
('Pesca Deportiva', 'Sesión de pesca en lago privado con todo el equipo incluido.', 400.00, '4 horas', 8),
('Yoga al Amanecer', 'Sesión de yoga en plataforma con vista a la montaña al amanecer.', 200.00, '1.5 horas', 12);

-- Equipo
INSERT INTO equipo (nombre, tipo, descripcion, precio_hora, precio_dia, cantidad_total) VALUES
('Bicicleta de Montaña Básica', 'bicicleta', 'Bicicleta MTB 21 velocidades, perfecta para los senderos del parque.', 80.00, 400.00, 5),
('Bicicleta Eléctrica', 'bicicleta', 'Bicicleta eléctrica con batería de larga duración, ideal para recorridos largos.', 150.00, 700.00, 3),
('Cuatrimoto 250cc', 'cuatrimoto', 'Cuatrimoto automática 250cc, fácil de manejar, apta para principiantes.', 350.00, 1500.00, 4),
('Cuatrimoto Sport 450cc', 'cuatrimoto', 'Cuatrimoto sport de alto rendimiento para aventureros experimentados.', 500.00, 2200.00, 2),
('Kayak Individual', 'kayak', 'Kayak individual estable, ideal para explorar el lago del parque.', 120.00, 500.00, 6),
('Kayak Doble', 'kayak', 'Kayak para dos personas, perfecto para parejas o amigos.', 180.00, 750.00, 4);
