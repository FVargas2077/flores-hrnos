-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS db_buses;
USE db_buses;

-- Tabla de Roles
CREATE TABLE roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(200),
    permisos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    id_rol INT,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(8) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(15),
    direccion VARCHAR(200),
    fecha_nacimiento DATE,
    genero ENUM('M', 'F', 'O'),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_sesion DATETIME,
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

-- Tabla de Tipos de Trabajador
CREATE TABLE tipos_trabajador (
    id_tipo INT PRIMARY KEY AUTO_INCREMENT,
    nombre_tipo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(200)
);

-- Tabla de Trabajadores (información específica de empleados)
CREATE TABLE trabajadores (
    id_trabajador INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT,
    id_tipo INT,
    fecha_contratacion DATE NOT NULL,
    salario DECIMAL(10,2),
    licencia_conducir VARCHAR(15),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_tipo) REFERENCES tipos_trabajador(id_tipo)
);

-- Tabla de Buses
CREATE TABLE buses (
    id_bus INT PRIMARY KEY AUTO_INCREMENT,
    placa VARCHAR(10) NOT NULL UNIQUE,
    modelo VARCHAR(100),
    tipo_servicio ENUM('BUS CAMA', 'DOR VIP 160', 'ECONOCAMA', 'PLATINUM') NOT NULL,
    capacidad_piso1 INT NOT NULL,
    capacidad_piso2 INT,
    año_fabricacion INT,
    estado ENUM('activo', 'mantenimiento', 'fuera_servicio') DEFAULT 'activo'
);

-- Tabla de Rutas
CREATE TABLE rutas (
    id_ruta INT PRIMARY KEY AUTO_INCREMENT,
    origen VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,
    distancia DECIMAL(10,2),
    duracion_estimada TIME,
    precio_base DECIMAL(10,2)
);

-- Tabla de Viajes
CREATE TABLE viajes (
    id_viaje INT PRIMARY KEY AUTO_INCREMENT,
    id_ruta INT,
    id_bus INT,
    id_conductor INT,
    id_copiloto INT,
    fecha_salida DATETIME,
    fecha_llegada DATETIME,
    estado ENUM('programado', 'en_curso', 'finalizado', 'cancelado') DEFAULT 'programado',
    FOREIGN KEY (id_ruta) REFERENCES rutas(id_ruta),
    FOREIGN KEY (id_bus) REFERENCES buses(id_bus),
    FOREIGN KEY (id_conductor) REFERENCES trabajadores(id_trabajador),
    FOREIGN KEY (id_copiloto) REFERENCES trabajadores(id_trabajador)
);

-- Tabla de Asientos
CREATE TABLE asientos (
    id_asiento INT PRIMARY KEY AUTO_INCREMENT,
    id_bus INT,
    numero_asiento INT NOT NULL,
    piso INT NOT NULL DEFAULT 1,
    precio DECIMAL(10,2) NOT NULL,
    ubicacion VARCHAR(50),
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_bus) REFERENCES buses(id_bus)
);

-- Tabla de Reservas
CREATE TABLE reservas (
    id_reserva INT PRIMARY KEY AUTO_INCREMENT,
    id_viaje INT,
    id_usuario INT,
    id_asiento INT,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    precio_final DECIMAL(10,2),
    FOREIGN KEY (id_viaje) REFERENCES viajes(id_viaje),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_asiento) REFERENCES asientos(id_asiento)
);

-- Tabla de Pagos
CREATE TABLE pagos (
    id_pago INT PRIMARY KEY AUTO_INCREMENT,
    id_reserva INT,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    estado ENUM('pendiente', 'completado', 'reembolsado') DEFAULT 'pendiente',
    FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva)
);

-- Tabla de Incidencias
CREATE TABLE incidencias (
    id_incidencia INT PRIMARY KEY AUTO_INCREMENT,
    id_viaje INT,
    id_trabajador INT,
    tipo_incidencia VARCHAR(100),
    descripcion TEXT,
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('reportada', 'en_proceso', 'resuelta') DEFAULT 'reportada',
    FOREIGN KEY (id_viaje) REFERENCES viajes(id_viaje),
    FOREIGN KEY (id_trabajador) REFERENCES trabajadores(id_trabajador)
);

-- Insertar roles básicos
INSERT INTO roles (nombre_rol, descripcion, permisos) VALUES
('admin', 'Administrador del sistema', '{"all": true}'),
('cliente', 'Cliente regular', '{"reservas": true, "perfil": true}'),
('conductor', 'Conductor de bus', '{"viajes": true, "reportes": true}'),
('vendedor', 'Vendedor de boletos', '{"ventas": true, "reservas": true}'),
('mecánico', 'Mecánico de mantenimiento', '{"mantenimiento": true}');

-- Insertar usuarios de prueba
INSERT INTO usuarios (id_rol, nombre, apellidos, dni, email, password, telefono, direccion, fecha_nacimiento, genero) VALUES
-- Administradores
(1, 'Juan Carlos', 'Mendoza Ríos', '45678912', 'admin1@flores.com', '123', '987654321', 'Av. Arequipa 123, Lima', '1985-05-15', 'M'),
(1, 'María Elena', 'García López', '45678913', 'admin2@flores.com', '123', '987654322', 'Av. Tacna 456, Lima', '1990-08-20', 'F'),

-- Clientes
(2, 'Pedro', 'Sánchez Díaz', '45678914', 'pedro@gmail.com', '123', '987654323', 'Jr. Huallaga 789, Lima', '1992-03-10', 'M'),
(2, 'Ana María', 'Torres Valle', '45678915', 'ana@gmail.com', '123', '987654324', 'Av. Brasil 321, Lima', '1988-11-25', 'F'),
(2, 'Luis Miguel', 'Pérez Castro', '45678916', 'luis@gmail.com', '123', '987654325', 'Jr. Cusco 654, Lima', '1995-07-30', 'M'),
(2, 'Carmen Rosa', 'Luna Flores', '45678917', 'carmen@gmail.com', '123', '987654326', 'Av. Wilson 987, Lima', '1991-12-05', 'F'),
(2, 'Jorge', 'Ramírez Silva', '45678918', 'jorge@gmail.com', '123', '987654327', 'Jr. Lampa 147, Lima', '1987-04-18', 'M'),
(2, 'Patricia', 'Vargas Ruiz', '45678919', 'patricia@gmail.com', '123', '987654328', 'Av. Abancay 258, Lima', '1993-09-22', 'F'),
(2, 'Roberto', 'Cruz Miranda', '45678920', 'roberto@gmail.com', '123', '987654329', 'Jr. Camaná 369, Lima', '1989-06-15', 'M'),
(2, 'Lucía', 'Benavides Paz', '45678921', 'lucia@gmail.com', '123', '987654330', 'Av. Grau 741, Lima', '1994-01-28', 'F');

-- Insertar tipos de trabajador
INSERT INTO tipos_trabajador (nombre_tipo, descripcion) VALUES
('Conductor', 'Conductor de buses'),
('Copiloto', 'Asistente del conductor'),
('Vendedor', 'Personal de ventas de boletos'),
('Mecánico', 'Personal de mantenimiento'),
('Limpieza', 'Personal de limpieza de buses'),
('Seguridad', 'Personal de seguridad');

-- Insertar buses de prueba
INSERT INTO buses (placa, modelo, tipo_servicio, capacidad_piso1, capacidad_piso2, año_fabricacion) VALUES
('ABC-123', 'Mercedes Benz O-500', 'BUS CAMA', 32, 20, 2023),
('XYZ-789', 'Scania K410', 'DOR VIP 160', 28, 16, 2023),
('DEF-456', 'Volvo 9800', 'BUS CAMA', 32, 20, 2022);

-- Insertar rutas de prueba
INSERT INTO rutas (origen, destino, distancia, duracion_estimada, precio_base) VALUES
('LIMA', 'TACNA', 1293, '21:00:00', 90.00),
('LIMA', 'AREQUIPA', 1012, '18:00:00', 85.00),
('LIMA', 'CUSCO', 1105, '20:00:00', 95.00);

-- Insertar asientos de prueba para el primer bus (ABC-123)
INSERT INTO asientos (id_bus, numero_asiento, piso, precio, ubicacion) VALUES
-- Primer piso
(1, 1, 1, 100.00, 'Ventana'),
(1, 2, 1, 100.00, 'Pasillo'),
(1, 3, 1, 100.00, 'Ventana'),
(1, 4, 1, 100.00, 'Pasillo'),
-- Segundo piso
(1, 5, 2, 90.00, 'Ventana'),
(1, 6, 2, 90.00, 'Pasillo'),
(1, 7, 2, 90.00, 'Ventana'),
(1, 8, 2, 90.00, 'Pasillo');

-- Insertar viajes de prueba
INSERT INTO viajes (id_ruta, id_bus, fecha_salida, fecha_llegada, estado) VALUES
(1, 1, '2025-11-04 10:30:00', '2025-11-05 07:30:00', 'programado'),
(1, 2, '2025-11-04 14:30:00', '2025-11-05 11:30:00', 'programado'),
(2, 3, '2025-11-04 16:00:00', '2025-11-05 10:00:00', 'programado');

-- VISTAS

-- Vista de información completa de usuarios
CREATE VIEW v_usuarios_info AS
SELECT 
    u.id_usuario,
    u.nombre,
    u.apellidos,
    u.dni,
    u.email,
    r.nombre_rol,
    u.estado
FROM usuarios u
JOIN roles r ON u.id_rol = r.id_rol;

-- Vista de viajes programados con detalles completos
CREATE VIEW v_viajes_programados AS
SELECT 
    v.id_viaje as 'N° Viaje',
    b.placa as 'Unidad',
    b.tipo_servicio as 'Servicio',
    r.origen,
    r.destino,
    DATE_FORMAT(v.fecha_salida, '%h:%i%p') as 'Salida',
    DATE_FORMAT(v.fecha_llegada, '%h:%i%p aprox.') as 'Llegada',
    CONCAT(
        HOUR(TIMEDIFF(v.fecha_llegada, v.fecha_salida)), 
        ':00 hrs. aprox.'
    ) as 'Duracion',
    CONCAT('1° Piso    S/ ', MIN(CASE WHEN a.piso = 1 THEN a.precio END)) as 'Precio_Piso1',
    CONCAT('2° Piso    S/ ', MIN(CASE WHEN a.piso = 2 THEN a.precio END)) as 'Precio_Piso2',
    SUM(CASE WHEN a.piso = 1 AND a.estado = TRUE THEN 1 ELSE 0 END) as 'Asientos_Libres_Piso1',
    SUM(CASE WHEN a.piso = 2 AND a.estado = TRUE THEN 1 ELSE 0 END) as 'Asientos_Libres_Piso2',
    v.estado
FROM viajes v
JOIN rutas r ON v.id_ruta = r.id_ruta
JOIN buses b ON v.id_bus = b.id_bus
JOIN asientos a ON b.id_bus = a.id_bus
WHERE v.fecha_salida >= CURRENT_DATE()
GROUP BY v.id_viaje, b.placa, b.tipo_servicio, r.origen, r.destino, 
         v.fecha_salida, v.fecha_llegada, v.estado;

-- PROCEDIMIENTOS ALMACENADOS

-- Procedimiento para login de usuarios
DELIMITER //
CREATE PROCEDURE sp_login_usuario(
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(255),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(100)
)
BEGIN
    DECLARE v_id_usuario INT;
    DECLARE v_rol VARCHAR(50);
    
    -- Buscar usuario
    SELECT u.id_usuario, r.nombre_rol INTO v_id_usuario, v_rol
    FROM usuarios u
    JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.email = p_email AND u.password = p_password AND u.estado = TRUE;
    
    IF v_id_usuario IS NOT NULL THEN
        -- Actualizar última sesión
        UPDATE usuarios SET ultima_sesion = NOW() WHERE id_usuario = v_id_usuario;
        
        -- Retornar éxito y tipo de usuario
        SET p_resultado = v_id_usuario;
        SET p_mensaje = v_rol;
    ELSE
        -- Usuario no encontrado o credenciales incorrectas
        SET p_resultado = 0;
        SET p_mensaje = 'Credenciales incorrectas';
    END IF;
END //
DELIMITER ;

-- Procedimiento para verificar si un usuario es administrador
DELIMITER //
CREATE PROCEDURE sp_verificar_admin(
    IN p_id_usuario INT,
    OUT p_es_admin BOOLEAN
)
BEGIN
    DECLARE v_rol VARCHAR(50);
    
    SELECT r.nombre_rol INTO v_rol
    FROM usuarios u
    JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.id_usuario = p_id_usuario;
    
    IF v_rol = 'admin' THEN
        SET p_es_admin = TRUE;
    ELSE
        SET p_es_admin = FALSE;
    END IF;
END //
DELIMITER ;

-- Procedimiento para crear una nueva reserva
DELIMITER //
CREATE PROCEDURE sp_crear_reserva(
    IN p_id_viaje INT,
    IN p_id_usuario INT,
    IN p_id_asiento INT,
    OUT p_id_reserva INT
)
BEGIN
    DECLARE v_precio_asiento DECIMAL(10,2);
    
    -- Corregido: Obtener el precio directamente de la tabla de asientos
    SELECT precio INTO v_precio_asiento
    FROM asientos
    WHERE id_asiento = p_id_asiento;
    
    -- Crear la reserva con el precio correcto
    INSERT INTO reservas (id_viaje, id_usuario, id_asiento, precio_final, estado)
    VALUES (p_id_viaje, p_id_usuario, p_id_asiento, v_precio_asiento, 'confirmada');
    
    -- Obtener el ID de la reserva recién creada
    SET p_id_reserva = LAST_INSERT_ID();
    
    -- Actualizar estado del asiento (esto ya estaba bien)
    UPDATE asientos SET estado = FALSE WHERE id_asiento = p_id_asiento;
END //
DELIMITER ;

-- TRIGGERS

-- Trigger para verificar disponibilidad de asiento antes de reserva
DELIMITER //
CREATE TRIGGER tr_verificar_asiento_disponible
BEFORE INSERT ON reservas
FOR EACH ROW
BEGIN
    DECLARE v_estado_asiento BOOLEAN;
    
    SELECT estado INTO v_estado_asiento
    FROM asientos
    WHERE id_asiento = NEW.id_asiento;
    
    IF v_estado_asiento = FALSE THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El asiento seleccionado no está disponible';
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar estado de viaje
DELIMITER //
CREATE TRIGGER tr_actualizar_estado_viaje
BEFORE UPDATE ON viajes
FOR EACH ROW
BEGIN
    IF NEW.fecha_salida <= NOW() AND NEW.fecha_llegada >= NOW() THEN
        SET NEW.estado = 'en_curso';
    ELSEIF NEW.fecha_llegada < NOW() THEN
        SET NEW.estado = 'finalizado';
    END IF;
END //
DELIMITER ;

-- FUNCIONES

-- Función para calcular la disponibilidad de asientos en un viaje
DELIMITER //
CREATE FUNCTION fn_asientos_disponibles(p_id_viaje INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_disponibles INT;
    
    SELECT COUNT(*) INTO v_disponibles
    FROM asientos a
    JOIN buses b ON a.id_bus = b.id_bus
    JOIN viajes v ON v.id_bus = b.id_bus
    WHERE v.id_viaje = p_id_viaje AND a.estado = TRUE;
    
    RETURN v_disponibles;
END //
DELIMITER ;

-- Función para calcular el total de ventas por día
DELIMITER //
CREATE FUNCTION fn_total_ventas_dia(p_fecha DATE)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_total DECIMAL(10,2);
    
    SELECT COALESCE(SUM(p.monto), 0) INTO v_total
    FROM pagos p
    WHERE DATE(p.fecha_pago) = p_fecha
    AND p.estado = 'completado';
    
    RETURN v_total;
END //
DELIMITER ;