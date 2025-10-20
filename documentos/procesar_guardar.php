<?php
/**
 * PLACEHOLDER - Procesar guardado de documento
 * Este archivo será implementado en la FASE 2
 * Por ahora solo muestra un mensaje
 */

require_once __DIR__ . '/../config/config.php';

if (!sesion_activa()) {
    header('Location: ../index.php');
    exit;
}

// Obtener datos del formulario
$accion = $_POST['accion'] ?? '';

if ($accion === 'guardar') {
    $mensaje = 'Función de GUARDAR BORRADOR';
    $tipo = 'info';
} elseif ($accion === 'enviar') {
    $mensaje = 'Función de ENVIAR APARTADO 1';
    $tipo = 'success';
} else {
    $mensaje = 'Acción no reconocida';
    $tipo = 'warning';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <div class="alert alert-<?php echo $tipo; ?> mb-4">
                            <i class="bi bi-info-circle display-4 d-block mb-3"></i>
                            <h4>Fase 2 - En Desarrollo</h4>
                            <p class="mb-0"><?php echo $mensaje; ?> será implementado en la siguiente fase.</p>
                        </div>
                        
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="mb-3">Datos recibidos del formulario:</h6>
                                <div class="text-start">
                                    <pre class="mb-0" style="max-height: 400px; overflow-y: auto;"><?php 
                                        print_r($_POST); 
                                    ?></pre>
                                </div>
                            </div>
                        </div>
                        
                        <a href="crear.php" class="btn btn-primary mt-4">
                            <i class="bi bi-arrow-left"></i> Volver al formulario
                        </a>
                        <a href="../dashboard/colaborativo.php" class="btn btn-secondary mt-4">
                            <i class="bi bi-house"></i> Ir al dashboard
                        </a>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-4">
                    <strong><i class="bi bi-exclamation-triangle"></i> Nota de desarrollo:</strong>
                    <ul class="mb-0 mt-2">
                        <li>La <strong>Fase 1</strong> incluye toda la interfaz visual y estructura del formulario</li>
                        <li>La <strong>Fase 2</strong> implementará:
                            <ul>
                                <li>Guardado en base de datos</li>
                                <li>Sistema de notificaciones</li>
                                <li>Cambio de fases del documento</li>
                                <li>Generación de folio único</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>