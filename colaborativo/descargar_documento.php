<?php
/**
 * Descarga de documentos colaborativos
 * Solo accesible para departamentos colaborativos y TI
 */

session_start();
require_once __DIR__ . '/../config/config.php';

// Verificar sesión activa
if (!sesion_activa()) {
    establecer_alerta('error', 'Debe iniciar sesión para descargar documentos');
    redirigir_login();
}

// Verificar que el usuario sea de departamento colaborativo o TI
if (!es_usuario_colaborativo() && !es_usuario_ti()) {
    establecer_alerta('error', 'No tiene acceso a documentos colaborativos');
    redirigir_dashboard();
}

// Obtener parámetros
$archivo = isset($_GET['archivo']) ? limpiar_dato($_GET['archivo']) : '';

// Validar parámetro
if (empty($archivo)) {
    establecer_alerta('error', 'Archivo no especificado');
    redirigir(URL_BASE . 'colaborativo/documentos.php');
}

// Obtener ruta del archivo
$ruta_archivo = obtener_ruta_archivo($archivo, 'colaborativo');

// Verificar que el archivo existe
if (!$ruta_archivo) {
    establecer_alerta('error', 'El documento no existe o ha sido eliminado');
    redirigir(URL_BASE . 'colaborativo/documentos.php');
}

// Prevenir directory traversal
$ruta_real = realpath($ruta_archivo);
$directorio_colaborativo = realpath(DIR_UPLOADS_COLABORATIVO);

if (strpos($ruta_real, $directorio_colaborativo) !== 0) {
    establecer_alerta('error', 'Acceso no autorizado');
    redirigir_dashboard();
}

// Obtener información del archivo
$nombre_descarga = basename($archivo);
$tamano = filesize($ruta_archivo);
$extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

// Determinar Content-Type
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'txt' => 'text/plain'
];

$content_type = isset($content_types[$extension]) ? $content_types[$extension] : 'application/octet-stream';

// Limpiar buffer de salida
if (ob_get_level()) {
    ob_end_clean();
}

// Establecer headers para descarga
header('Content-Description: File Transfer');
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $tamano);

// Leer y enviar archivo
readfile($ruta_archivo);

// TODO: Registrar descarga en base de datos cuando esté disponible
/*
$pdo = conectarDB();
$stmt = $pdo->prepare("INSERT INTO descargas_colaborativas_log (usuario_id, archivo, departamento, fecha_descarga) VALUES (?, ?, ?, NOW())");
$stmt->execute([$_SESSION['usuario_id'], $archivo, $_SESSION['departamento']]);
*/

exit();

?>