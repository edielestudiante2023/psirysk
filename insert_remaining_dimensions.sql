-- Script SQL para agregar las dimensiones restantes intralaborales
-- Ejecutar con: mysql -u root psyrisk < insert_remaining_dimensions.sql

USE psyrisk;

-- Verificar dimensiones actuales
SELECT COUNT(*) as 'Dimensiones actuales' FROM action_plans;

-- Las 7 dimensiones intralaborales pendientes se agregarán mediante seeders PHP
-- Este archivo es solo para referencia

-- Lista de dimensiones a agregar:
-- 1. influencia_trabajo_entorno_extralaboral (texto3.txt)
-- 2. exigencias_responsabilidad_cargo (texto3.txt)
-- 3. demandas_carga_mental (texto3.txt)
-- 4. consistencia_rol (texto4.txt)
-- 5. demandas_jornada_trabajo (texto4.txt)
-- 6. recompensas_pertenencia_organizacion (texto4.txt)
-- 7. reconocimiento_compensacion (texto4.txt)

SELECT 'Usar seeders PHP para agregar las dimensiones' as 'NOTA';
