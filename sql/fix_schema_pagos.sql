USE u914095763_g7;

-- 1. Quitar la llave foránea de la tabla 'pagos'
-- (Necesitamos quitarla para poder borrar la columna)
-- El nombre 'pagos_ibfk_1' es el default, si da error, ignóralo o revisa el nombre en la pestaña "Estructura"
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
ALTER TABLE pagos DROP FOREIGN KEY IF EXISTS pagos_ibfk_1;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

-- 2. Quitar la columna 'id_reserva' de 'pagos'
-- (Ya no la necesitamos, 'pagos' solo guardará el monto total)
ALTER TABLE pagos DROP COLUMN IF EXISTS id_reserva;

-- 3. Añadir la columna 'id_pago' a 'reservas'
-- (Así, múltiples reservas pueden apuntar a un solo pago)
ALTER TABLE reservas ADD COLUMN IF NOT EXISTS id_pago INT NULL AFTER precio_final;

-- 4. (Opcional pero recomendado) Añadir la nueva llave foránea
-- (Ignoramos el error si ya existe)
ALTER TABLE reservas 
ADD CONSTRAINT fk_reservas_pagos 
FOREIGN KEY (id_pago) REFERENCES pagos(id_pago);