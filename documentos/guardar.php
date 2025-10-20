<?php
/**
 * Guardar documento (borrador o en proceso)
 * Guarda cambios sin cambiar el estado
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notificaciones.php';

header('Content-Type: application/json');

if (!sesion_activa()) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $pdo = conectarDB();
    $usuario_id = $_SESSION['usuario_id'];
    $departamento = $_SESSION['departamento'];
    
    // Obtener datos del formulario
    $documento_id = $_POST['documento_id'] ?? null;
    $apartado = $_POST['apartado'] ?? 1;
    
    // Si es un documento nuevo (Apartado 1)
    if ($apartado == 1 && !$documento_id) {
        
        // Validar campos requeridos Apartado 1
        if (empty($_POST['nombre_recibe']) || empty($_POST['fecha_recibido']) || empty($_POST['area_solicitante'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
            exit;
        }
        
        // Generar folio temporal
        $folio = 'DRAFT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Insertar nuevo documento
        $stmt = $pdo->prepare("
            INSERT INTO documentos_colaborativos (
                folio, departamento, fase, estado,
                usuario_creador,
                nombre_recibe, fecha_recibido, area_solicitante,
                fecha_creacion
            ) VALUES (?, ?, 'apartado_1', 'borrador', ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $folio,
            $departamento,
            $usuario_id,
            $_POST['nombre_recibe'],
            $_POST['fecha_recibido'],
            $_POST['area_solicitante']
        ]);
        
        $documento_id = $pdo->lastInsertId();
        
        // Notificar a departamento que se creó un nuevo documento
        $usuarios_depto = obtener_usuarios_departamento($departamento);
        foreach ($usuarios_depto as $usuario) {
            if ($usuario['id'] != $usuario_id) {
                crear_notificacion(
                    'documento_creado',
                    'Nuevo documento creado',
                    $_SESSION['nombre_completo'] . ' creó un nuevo documento en borrador',
                    $usuario['id'],
                    ['documento_id' => $documento_id, 'folio' => $folio]
                );
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Documento guardado como borrador',
            'documento_id' => $documento_id,
            'folio' => $folio,
            'redirect' => URL_BASE . 'documentos/ver.php?id=' . $documento_id
        ]);
        exit;
    }
    
    // Si es actualización de documento existente
    if ($documento_id) {
        
        // Verificar permisos
        $stmt = $pdo->prepare("SELECT * FROM documentos_colaborativos WHERE id = ?");
        $stmt->execute([$documento_id]);
        $documento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$documento) {
            echo json_encode(['success' => false, 'message' => 'Documento no encontrado']);
            exit;
        }
        
        // Verificar que el usuario tiene permisos para editar
        if ($apartado == 1 && $documento['usuario_creador'] != $usuario_id) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para editar este apartado']);
            exit;
        }
        
        if ($apartado == 2 && $documento['usuario_completador'] != $usuario_id) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para editar este apartado']);
            exit;
        }
        
        // Actualizar según apartado
        if ($apartado == 1) {
            $stmt = $pdo->prepare("
                UPDATE documentos_colaborativos 
                SET nombre_recibe = ?,
                    fecha_recibido = ?,
                    area_solicitante = ?,
                    fecha_modificacion = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['nombre_recibe'],
                $_POST['fecha_recibido'],
                $_POST['area_solicitante'],
                $documento_id
            ]);
        } else {
            // Apartado 2
            $stmt = $pdo->prepare("
                UPDATE documentos_colaborativos 
                SET servicio_tratamiento_agua = ?,
                    servicio_evaluacion_quimicos = ?,
                    servicio_calibracion = ?,
                    servicio_otro = ?,
                    servicio_otro_especificar = ?,
                    prioridad = ?,
                    descripcion_servicio = ?,
                    resumen_resultados = ?,
                    fecha_entrega = ?,
                    hora_entrega = ?,
                    fecha_modificacion = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                isset($_POST['servicio_tratamiento_agua']) ? 1 : 0,
                isset($_POST['servicio_evaluacion_quimicos']) ? 1 : 0,
                isset($_POST['servicio_calibracion']) ? 1 : 0,
                isset($_POST['servicio_otro']) ? 1 : 0,
                $_POST['servicio_otro_especificar'] ?? null,
                $_POST['prioridad'],
                $_POST['descripcion_servicio'],
                $_POST['resumen_resultados'],
                $_POST['fecha_entrega'],
                $_POST['hora_entrega'],
                $documento_id
            ]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cambios guardados correctamente',
            'documento_id' => $documento_id
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    
} catch (Exception $e) {
    error_log("Error en guardar.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}

// Función auxiliar para obtener usuarios del departamento
function obtener_usuarios_departamento($departamento) {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT id, nombre_completo FROM usuarios WHERE departamento = ?");
    $stmt->execute([$departamento]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}