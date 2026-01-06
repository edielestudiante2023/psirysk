-- Script para agregar la columna detailed_report a la tabla csv_imports
-- Ejecuta este script en phpMyAdmin o en tu cliente MySQL

USE psyrisk;

ALTER TABLE csv_imports
ADD COLUMN detailed_report TEXT NULL
COMMENT 'Informe detallado JSON con errores categorizados'
AFTER error_log;

-- Verificar que la columna se agregó correctamente
DESCRIBE csv_imports;
