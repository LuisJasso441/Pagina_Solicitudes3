<?php
/**
 * Endpoint AJAX: Obtener empleados por departamento
 * Usado en los filtros de Base Global
 */

// ⭐ IMPORTANTE: Iniciar sesión ANTES de cualquier output
session_start();

// ⭐ SUPRIMIR WARNINGS PARA EVITAR QUE ROMPAN EL JSON
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

// ⭐ LIMPIAR BUFFER DE SALIDA
if (ob_get_level()) {
    ob_end_clean();
}

// ⭐ Configurar header JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación básica
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Sesión no válida'
    ]);
    exit;
}

// Verificar método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false, 
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Obtener departamento
$departamento = $_GET['departamento'] ?? '';

if (empty($departamento)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Departamento no especificado'
    ]);
    exit;
}

try {
    // Conectar a base de datos
    require_once __DIR__ . '/../config/database.php';
    $pdo = conectarDB();
    
    // ⭐ MODIFICADO: Obtener TODOS los empleados del departamento
    // (sin importar si han creado documentos o no)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nombre_completo
        FROM usuarios
        WHERE LOWER(departamento) = LOWER(?)
        AND activo = 1
        ORDER BY nombre_completo ASC
    ");
    
    $stmt->execute([$departamento]);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'empleados' => $empleados,
        'total' => count($empleados)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error BD en obtener_empleados_departamento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos'
    ]);
    
} catch (Exception $e) {
    // Error general
    error_log("Error en obtener_empleados_departamento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error del sistema'
    ]);
}