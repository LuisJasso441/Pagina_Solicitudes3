<?php
/**
 * Sistema de Documentos Colaborativos
 * Funciones para gestión de documentos SSC
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/verificar_sesion.php';
require_once __DIR__ . '/notificaciones.php';

/**
 * Generar folio automático
 * Formato: SSC-DD/MM/YYYY-NNN
 */
function generar_folio_documento() {
    try {
        $pdo = conectarDB();
        
        // Obtener fecha actual
        $fecha_actual = date('d/m/Y');
        $prefijo = "SSC-{$fecha_actual}-";
        
        // Buscar el último folio de hoy
        $stmt = $pdo->prepare("
            SELECT folio FROM documentos_colaborativos 
            WHERE folio LIKE ? 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute(["{$prefijo}%"]);
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ultimo) {
            // Extraer el número y incrementar
            preg_match('/(\d+)$/', $ultimo['folio'], $matches);
            $numero = intval($matches[1]) + 1;
        } else {
            $numero = 1;
        }
        
        // Formato: 001, 002, etc.
        $numero_formateado = str_pad($numero, 3, '0', STR_PAD_LEFT);
        
        return $prefijo . $numero_formateado;
        
    } catch (Exception $e) {
        error_log("Error al generar folio: " . $e->getMessage());
        return "SSC-" . date('d/m/Y') . "-ERROR";
    }
}

/**
 * Obtener documento por ID
 */
function obtener_documento($documento_id) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM documentos_colaborativos WHERE id = ?");
        $stmt->execute([$documento_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error al obtener documento: " . $e->getMessage());
        return false;
    }
}

/**
 * ⭐ MODIFICADO: Verificar permisos de edición y comentarios
 * AHORA PERMITE COMENTAR EN DOCUMENTOS COMPLETADOS
 */
function verificar_permisos_edicion($usuario_id, $departamento, $documento) {
    $permisos = [
        'apartado1' => false,
        'apartado2' => false,
        'puede_comentar' => false,
        'es_creador' => false,
        'es_seguimiento' => false
    ];
    
    // Verificar si es el creador
    if ($documento['usuario_creador_id'] == $usuario_id) {
        $permisos['es_creador'] = true;
        
        // Solo puede editar Apartado 1 si no está completado
        if ($documento['estado'] != 'completado') {
            $permisos['apartado1'] = true;
        }
    }
    
    // Verificar si es de Laboratorio
    if (strtolower($departamento) == 'laboratorio') {
        // Solo puede editar Apartado 2 si no está completado
        if ($documento['estado'] != 'completado') {
            $permisos['apartado2'] = true;
        }
        
        // Si ya está asignado a este usuario específico
        if ($documento['usuario_seguimiento_id'] == $usuario_id) {
            $permisos['es_seguimiento'] = true;
        }
    }
    
    // ⭐ MODIFICADO: Permitir comentarios a departamentos colaborativos
    // incluso en documentos completados
    $departamentos_colaborativos = ['ventas', 'normatividad', 'laboratorio', 'ti_sistemas'];
    $dept_lower = strtolower($departamento);
    
    if (in_array($dept_lower, $departamentos_colaborativos)) {
        $permisos['puede_comentar'] = true;
    }
    
    return $permisos;
}

/**
 * Crear nuevo documento colaborativo
 */
function crear_documento_colaborativo($datos, $usuario_id, $departamento) {
    try {
        $pdo = conectarDB();
        
        // Verificar que el usuario tenga permiso (Normatividad o Ventas)
        $dept_lower = strtolower($departamento);
        if (!in_array($dept_lower, ['normatividad', 'ventas'])) {
            return [
                'success' => false,
                'message' => 'Solo Normatividad y Ventas pueden crear documentos colaborativos'
            ];
        }
        
        // Generar folio
        $folio = generar_folio_documento();
        
        // Validar servicio "otro"
        $servicio_otro = null;
        if ($datos['servicio_solicitado'] == 'otro' && !empty($datos['servicio_otro_especificar'])) {
            $servicio_otro = trim($datos['servicio_otro_especificar']);
        }
        
        // Insertar documento
        $stmt = $pdo->prepare("
            INSERT INTO documentos_colaborativos (
                folio, solicitado_por, fecha_solicitud, area_proceso_solicitante,
                servicio_solicitado, servicio_otro_especificar, prioridad, descripcion_servicio,
                usuario_creador_id, departamento_creador, estado, ubicacion,
                fecha_creacion, fecha_ultima_edicion
            ) VALUES (
                ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, 'borrador', 'local', NOW(), NOW()
            )
        ");
        
        $resultado = $stmt->execute([
            $folio,
            $datos['solicitado_por'],
            $datos['area_proceso'],
            $datos['servicio_solicitado'],
            $servicio_otro,
            $datos['prioridad'],
            $datos['descripcion'],
            $usuario_id,
            $departamento
        ]);
        
        if ($resultado) {
            $documento_id = $pdo->lastInsertId();
            
            // Registrar en historial
            registrar_historial_documento(
                $documento_id,
                $folio,
                $usuario_id,
                $datos['solicitado_por'],
                $departamento,
                'documento_creado',
                null,
                'borrador',
                'Documento creado en Base Local'
            );
            
            // Notificar a Laboratorio
            notificar_nuevo_documento($documento_id, $folio, $datos['solicitado_por'], $departamento);
            
            return [
                'success' => true,
                'message' => 'Documento creado exitosamente',
                'documento_id' => $documento_id,
                'folio' => $folio
            ];
        }
        
        return ['success' => false, 'message' => 'Error al crear documento'];
        
    } catch (Exception $e) {
        error_log("Error al crear documento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()];
    }
}

/**
 * Actualizar Apartado 1
 */
function actualizar_apartado1($documento_id, $datos, $usuario_id, $departamento) {
    try {
        $pdo = conectarDB();
        
        // Verificar permisos
        $documento = obtener_documento($documento_id);
        if (!$documento) {
            return ['success' => false, 'message' => 'Documento no encontrado'];
        }
        
        $permisos = verificar_permisos_edicion($usuario_id, $departamento, $documento);
        if (!$permisos['apartado1']) {
            return ['success' => false, 'message' => 'No tienes permiso para editar el Apartado 1'];
        }
        
        // Validar servicio "otro"
        $servicio_otro = null;
        if ($datos['servicio_solicitado'] == 'otro' && !empty($datos['servicio_otro_especificar'])) {
            $servicio_otro = trim($datos['servicio_otro_especificar']);
        }
        
        // Actualizar
        $stmt = $pdo->prepare("
            UPDATE documentos_colaborativos SET
                solicitado_por = ?,
                area_proceso_solicitante = ?,
                servicio_solicitado = ?,
                servicio_otro_especificar = ?,
                prioridad = ?,
                descripcion_servicio = ?,
                fecha_ultima_edicion = NOW()
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([
            $datos['solicitado_por'],
            $datos['area_proceso'],
            $datos['servicio_solicitado'],
            $servicio_otro,
            $datos['prioridad'],
            $datos['descripcion'],
            $documento_id
        ]);
        
        if ($resultado) {
            // Registrar en historial
            registrar_historial_documento(
                $documento_id,
                $documento['folio'],
                $usuario_id,
                $datos['solicitado_por'],
                $departamento,
                'apartado1_editado',
                null,
                null,
                'Apartado 1 actualizado'
            );
            
            return ['success' => true, 'message' => 'Apartado 1 actualizado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar'];
        
    } catch (Exception $e) {
        error_log("Error al actualizar Apartado 1: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error del sistema'];
    }
}

/**
 * Actualizar Apartado 2
 */
function actualizar_apartado2($documento_id, $datos, $usuario_id, $departamento) {
    try {
        $pdo = conectarDB();
        
        // Verificar permisos
        $documento = obtener_documento($documento_id);
        if (!$documento) {
            return ['success' => false, 'message' => 'Documento no encontrado'];
        }
        
        $permisos = verificar_permisos_edicion($usuario_id, $departamento, $documento);
        if (!$permisos['apartado2']) {
            return ['success' => false, 'message' => 'No tienes permiso para editar el Apartado 2'];
        }
        
        // Actualizar
        $stmt = $pdo->prepare("
            UPDATE documentos_colaborativos SET
                recibe_solicitud = ?,
                fecha_hora_recibido = ?,
                resumen_resultados = ?,
                fecha_hora_entrega = ?,
                usuario_seguimiento_id = ?,
                fecha_ultima_edicion = NOW()
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([
            $datos['recibe_solicitud'],
            $datos['fecha_hora_recibido'],
            $datos['resumen_resultados'],
            $datos['fecha_hora_entrega'],
            $usuario_id, // Asignar al usuario actual de Laboratorio
            $documento_id
        ]);
        
        if ($resultado) {
            // Registrar en historial
            registrar_historial_documento(
                $documento_id,
                $documento['folio'],
                $usuario_id,
                $datos['recibe_solicitud'],
                $departamento,
                'apartado2_editado',
                null,
                null,
                'Apartado 2 actualizado'
            );
            
            return ['success' => true, 'message' => 'Apartado 2 actualizado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar'];
        
    } catch (Exception $e) {
        error_log("Error al actualizar Apartado 2: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error del sistema'];
    }
}

/**
 * Completar documento (mover a Base Global)
 */
function completar_documento($documento_id, $usuario_id, $departamento) {
    try {
        $pdo = conectarDB();
        
        // Verificar que el documento existe
        $documento = obtener_documento($documento_id);
        if (!$documento) {
            return ['success' => false, 'message' => 'Documento no encontrado'];
        }
        
        // Verificar que el Apartado 2 esté completo
        if (empty($documento['recibe_solicitud']) || empty($documento['resumen_resultados'])) {
            return ['success' => false, 'message' => 'El Apartado 2 debe estar completo antes de finalizar'];
        }
        
        // Verificar permisos (solo Laboratorio puede completar)
        if (strtolower($departamento) != 'laboratorio') {
            return ['success' => false, 'message' => 'Solo Laboratorio puede completar documentos'];
        }
        
        // Actualizar estado y ubicación
        $stmt = $pdo->prepare("
            UPDATE documentos_colaborativos SET
                estado = 'completado',
                ubicacion = 'global',
                fecha_ultima_edicion = NOW()
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([$documento_id]);
        
        if ($resultado) {
            // Registrar en historial
            registrar_historial_documento(
                $documento_id,
                $documento['folio'],
                $usuario_id,
                $documento['recibe_solicitud'],
                $departamento,
                'documento_completado',
                'borrador',
                'completado',
                'Documento movido a Base Global'
            );
            
            // Notificar al creador
            notificar_documento_completado($documento, $usuario_id);
            
            return [
                'success' => true,
                'message' => 'Documento completado y movido a la Base Global'
            ];
        }
        
        return ['success' => false, 'message' => 'Error al completar documento'];
        
    } catch (Exception $e) {
        error_log("Error al completar documento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error del sistema'];
    }
}

/**
 * Registrar en historial
 */
function registrar_historial_documento($documento_id, $folio, $usuario_id, $usuario_nombre, $departamento, $accion, $estado_anterior, $estado_nuevo, $observaciones = null) {
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            INSERT INTO documentos_historial (
                documento_id, folio_documento, usuario_id, usuario_nombre, 
                departamento_usuario, accion, estado_anterior, estado_nuevo, 
                observaciones, fecha_hora
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $documento_id,
            $folio,
            $usuario_id,
            $usuario_nombre,
            $departamento,
            $accion,
            $estado_anterior,
            $estado_nuevo,
            $observaciones
        ]);
        
    } catch (Exception $e) {
        error_log("Error al registrar historial: " . $e->getMessage());
    }
}

/**
 * Notificar nuevo documento a Laboratorio
 */
function notificar_nuevo_documento($documento_id, $folio, $solicitante, $departamento) {
    try {
        $pdo = conectarDB();
        
        // Obtener usuarios de Laboratorio
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE departamento = 'laboratorio' AND activo = 1");
        $stmt->execute();
        $usuarios_lab = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Crear notificación para cada usuario
        foreach ($usuarios_lab as $usuario_id) {
            crear_notificacion(
                'documento_nuevo',
                "📋 Nuevo documento SSC: {$folio}",
                "Solicitado por: {$solicitante} ({$departamento})",
                $usuario_id,
                [
                    'documento_id' => $documento_id,
                    'folio' => $folio,
                    'solicitante' => $solicitante
                ]
            );
        }
        
    } catch (Exception $e) {
        error_log("Error al notificar nuevo documento: " . $e->getMessage());
    }
}

/**
 * Notificar documento completado
 */
function notificar_documento_completado($documento, $usuario_id) {
    try {
        // Notificar al creador si no es el mismo que lo completa
        if ($documento['usuario_creador_id'] && $documento['usuario_creador_id'] != $usuario_id) {
            crear_notificacion(
                'documento_completado',
                "✅ Documento completado: {$documento['folio']}",
                "El documento ha sido finalizado y movido a la Base Global",
                $documento['usuario_creador_id'],
                [
                    'documento_id' => $documento['id'],
                    'folio' => $documento['folio']
                ]
            );
        }
        
    } catch (Exception $e) {
        error_log("Error al notificar documento completado: " . $e->getMessage());
    }
}

/**
 * Listar documentos con filtros
 */
function listar_documentos($filtros = []) {
    try {
        $pdo = conectarDB();
        
        $where = [];
        $params = [];
        
        // Filtro por ubicación
        if (isset($filtros['ubicacion'])) {
            $where[] = "ubicacion = ?";
            $params[] = $filtros['ubicacion'];
        }
        
        // Filtro por estado
        if (isset($filtros['estado'])) {
            $where[] = "estado = ?";
            $params[] = $filtros['estado'];
        }
        
        // Filtro por departamento
        if (isset($filtros['departamento'])) {
            $where[] = "departamento_creador = ?";
            $params[] = $filtros['departamento'];
        }
        
        // ⭐ NUEVO: Filtro por empleado (usuario_creador_id)
        if (isset($filtros['empleado']) && !empty($filtros['empleado'])) {
            $where[] = "usuario_creador_id = ?";
            $params[] = $filtros['empleado'];
        }
        
        // Filtro por folio
        if (isset($filtros['folio']) && !empty($filtros['folio'])) {
            $where[] = "folio LIKE ?";
            $params[] = "%{$filtros['folio']}%";
        }
        
        // ⭐ NUEVO: Filtro por fecha desde
        if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
            $where[] = "fecha_solicitud >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        
        // ⭐ NUEVO: Filtro por fecha hasta
        if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
            $where[] = "fecha_solicitud <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        $sql = "SELECT * FROM documentos_colaborativos";
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY fecha_ultima_edicion DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error al listar documentos: " . $e->getMessage());
        return [];
    }
}