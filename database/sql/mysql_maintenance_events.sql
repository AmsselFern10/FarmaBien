-- =====================================================================
-- FarmaBien v2.0 - Scripts de Mantenimiento y Event Scheduler para MySQL
-- Rol: Administrador de Base de Datos (DBA) / Arquitecto de Datos
-- =====================================================================

-- 1. Habilitar el Programador de Eventos (Event Scheduler) en MySQL
SET GLOBAL event_scheduler = ON;

-- 2. Creación del Evento Recurrente de Depuración Automática de Logs
-- Se ejecuta todos los días a las 02:30 AM para eliminar logs con más de 30 días
DROP EVENT IF EXISTS `ev_purgar_logs_auditoria_30d`;

DELIMITER $$

CREATE EVENT `ev_purgar_logs_auditoria_30d`
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURRENT_DATE) + INTERVAL 2 HOUR + INTERVAL 30 MINUTE)
COMMENT 'Purga diaria automatizada de logs de accesos y auditoría mayores a 30 días'
DO
BEGIN
    -- Eliminar logs de inicio de sesión antiguos
    DELETE FROM `login_logs` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Opcional: Eliminar tokens expirados de Sanctum / Personal Access Tokens
    DELETE FROM `personal_access_tokens`
    WHERE `expires_at` IS NOT NULL AND `expires_at` < NOW();

    -- Optimizar y recuperar espacio en disco
    OPTIMIZE TABLE `login_logs`;
END$$

DELIMITER ;

-- =====================================================================
-- 3. Procedimiento Almacenado para Mantenimiento y Diagnóstico Completo
-- =====================================================================
DROP PROCEDURE IF EXISTS `sp_mantenimiento_farmabien_dba`;

DELIMITER $$

CREATE PROCEDURE `sp_mantenimiento_farmabien_dba`()
BEGIN
    -- Desfragmentar y actualizar estadísticas de tablas principales
    ANALYZE TABLE `productos`, `lotes`, `ventas`, `detalle_venta`, `compras`, `detalle_compra`, `clientes`, `proveedores`, `movimientos_inventario`, `sesiones_caja`, `movimientos_caja`;
    
    -- Optimizar tablas de alta concurrencia
    OPTIMIZE TABLE `login_logs`, `movimientos_inventario`, `detalle_venta`;
    
    SELECT 'Mantenimiento preventivo y actualización de índices completados exitosamente.' AS `Resultado`;
END$$

DELIMITER ;
