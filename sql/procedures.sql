-- TolCare — stored procedures (from the original mcdougall database, Jan 2026).
-- exportar_calendario: exports the agenda as a pivot table (employee x dates).
-- generar_agenda: generates agenda rows for a date range.
-- NOTE: both procedures were written for the ORIGINAL Spanish-named tables
-- (empleados, agenda, puestos, turnos). They do NOT run against the current
-- tolcare.sql schema (employees, agendas, positions...) without adaptation.
-- Kept here as reference for the logic, not for direct import.

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `exportar_calendario` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE)   BEGIN
    -- declaraciones al inicio
    DECLARE v_sql TEXT;
    DECLARE v_day DATE;

    -- inicio de la consulta dinámica
    SET v_sql = 'SELECT CONCAT(e.nombre, '' '', e.apellido, '' ('', p.nombre_puesto, '')'') AS empleado';

    SET v_day = p_fecha_inicio;
    WHILE v_day <= p_fecha_fin DO
        SET v_sql = CONCAT(v_sql, ',
            MAX(CASE
                WHEN a.fecha = ''', DATE_FORMAT(v_day, '%Y-%m-%d'), ''' AND t.nombre_turno = ''Day'' THEN ''DAY''
                WHEN a.fecha = ''', DATE_FORMAT(v_day, '%Y-%m-%d'), ''' AND t.nombre_turno = ''Night'' THEN ''NIGHT''
                ELSE ''''
            END) AS `', DATE_FORMAT(v_day, '%Y-%m-%d'), '`');
        SET v_day = DATE_ADD(v_day, INTERVAL 1 DAY);
    END WHILE;

    SET v_sql = CONCAT(v_sql, '
        FROM agenda a
        JOIN empleados e ON a.empleado_id = e.empleado_id
        JOIN puestos p   ON e.puesto_id = p.puesto_id
        JOIN turnos t    ON a.turno_id = t.turno_id
        WHERE a.fecha BETWEEN ''', DATE_FORMAT(p_fecha_inicio, '%Y-%m-%d'), ''' AND ''', DATE_FORMAT(p_fecha_fin, '%Y-%m-%d'), '''
        GROUP BY e.empleado_id, e.nombre, e.apellido, p.nombre_puesto
        ORDER BY a.grupo_id, t.turno_id, p.puesto_id');

    -- PREPARE requiere una variable de usuario (@)
    SET @final_sql = v_sql;
    PREPARE stmt FROM @final_sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generar_agenda` (IN `fecha_inicio` DATE, IN `fecha_fin` DATE)   BEGIN
    DECLARE d DATE;
    DECLARE semana INT;
    DECLARE dia_semana VARCHAR(15);
    DECLARE grupo_trabajo INT;

    SET d = fecha_inicio;

    WHILE d <= fecha_fin DO
        -- Determinar semana (impar = semana 1, par = semana 2)
        SET semana = WEEK(d, 1) % 2 + 1;
        -- Debo recibir el dia lunes de la semana que deseo que sea la semana 1
        -- SET semana = (TIMESTAMPDIFF(WEEK, 'YYYY-MM-DD', d) % 2) + 1;

        -- Día de la semana (ej: 'Lunes')
        SET dia_semana = DAYNAME(d);

        -- Determinar grupo que trabaja ese día
        SET grupo_trabajo = NULL;

        IF semana = 1 THEN
            -- Semana 1: Grupo 1 trabaja L, M, V, S, D | Grupo 2 trabaja X, J
            IF dia_semana IN ('Monday','Tuesday','Friday','Saturday','Sunday') THEN
                SET grupo_trabajo = 1;
            ELSEIF dia_semana IN ('Wednesday','Thursday') THEN
                SET grupo_trabajo = 2;
            END IF;
        ELSE
            -- Semana 2: Grupo 2 trabaja L, M, V, S, D | Grupo 1 trabaja X, J
            IF dia_semana IN ('Monday','Tuesday','Friday','Saturday','Sunday') THEN
                SET grupo_trabajo = 2;
            ELSEIF dia_semana IN ('Wednesday','Thursday') THEN
                SET grupo_trabajo = 1;
            END IF;
        END IF;

        -- Si hay grupo asignado para este día, generar agenda
        IF grupo_trabajo IS NOT NULL THEN

            -- ========================
            -- TURNO MATUTINO (1)
            -- ========================
            -- Cocinero
            INSERT INTO agenda (empleado_id, grupo_id, turno_id, fecha)
            SELECT e.empleado_id, grupo_trabajo, 1, d
            FROM empleados e
            WHERE e.grupo_id = grupo_trabajo AND e.turno = 1 AND e.puesto_id = 1
            AND NOT EXISTS (
              SELECT 1 FROM agenda a
              WHERE a.empleado_id = e.empleado_id AND a.turno_id = 1 AND a.fecha = d AND a.grupo_id = grupo_trabajo 
            )
            LIMIT 1;

            -- Medicinas (matutino)
            INSERT INTO agenda (empleado_id, grupo_id, turno_id, fecha)
            SELECT e.empleado_id, grupo_trabajo, 1, d
            FROM empleados e
            WHERE e.grupo_id = grupo_trabajo AND e.turno = 1 AND e.puesto_id = 2
            AND NOT EXISTS (
              SELECT 1 FROM agenda a
              WHERE a.empleado_id = e.empleado_id AND a.turno_id = 1 AND a.fecha = d AND a.grupo_id = grupo_trabajo 
            ) 
            LIMIT 1;

            -- Atención (3 matutino)
            INSERT INTO agenda (empleado_id, grupo_id, turno_id, fecha)
            SELECT e.empleado_id, grupo_trabajo, 1, d
            FROM empleados e
            WHERE e.grupo_id = grupo_trabajo AND e.turno = 1 AND e.puesto_id = 3
            AND NOT EXISTS (
              SELECT 1 FROM agenda a
              WHERE a.empleado_id = e.empleado_id AND a.turno_id = 1 AND a.fecha = d AND a.grupo_id = grupo_trabajo 
            ) 
            LIMIT 3;

            -- ========================
            -- TURNO NOCTURNO (2)
            -- ========================
            -- Medicinas (nocturno)
            INSERT INTO agenda (empleado_id, grupo_id, turno_id, fecha)
            SELECT e.empleado_id, grupo_trabajo, 2, d
            FROM empleados e
            WHERE e.grupo_id = grupo_trabajo AND e.turno = 2 AND e.puesto_id = 2
            AND NOT EXISTS (
              SELECT 1 FROM agenda a
              WHERE a.empleado_id = e.empleado_id AND a.turno_id = 2 AND a.fecha = d AND a.grupo_id = grupo_trabajo 
            )
            LIMIT 1;

            -- Atención (2 nocturno)
            INSERT INTO agenda (empleado_id, grupo_id, turno_id, fecha)
            SELECT e.empleado_id, grupo_trabajo, 2, d
            FROM empleados e
            WHERE e.grupo_id = grupo_trabajo AND e.turno = 2 AND e.puesto_id = 3
            AND NOT EXISTS (
              SELECT 1 FROM agenda a
              WHERE a.empleado_id = e.empleado_id AND a.turno_id = 2 AND a.fecha = d AND a.grupo_id = grupo_trabajo 
            )
            LIMIT 2;
        END IF;

        -- Pasar al siguiente día
        SET d = DATE_ADD(d, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER ;
