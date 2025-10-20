<?php
/**
 * Página de creación de documentos colaborativos
 * Usuario 1 crea el documento y completa Apartado 1
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar sesión
if (!sesion_activa()) {
    header('Location: ' . URL_BASE . 'index.php');
    exit;
}

// Verificar que el usuario sea de un departamento colaborativo
$departamentos_colaborativos = ['ventas', 'laboratorio', 'normatividad'];
if (!in_array($_SESSION['departamento'], $departamentos_colaborativos)) {
    header('Location: ' . URL_BASE . 'dashboard/departamento.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_completo = $_SESSION['nombre_completo'];
$departamento = $_SESSION['departamento'];
$nombre_usuario = $nombre_completo;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Documento - Sistema TI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>assets/css/documentos.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo URL_BASE; ?>dashboard/colaborativo.php">
                <i class="bi bi-arrow-left"></i> Volver a Dashboard
            </a>
            <div class="navbar-text text-white">
                <span class="badge bg-warning text-dark me-2">BORRADOR</span>
                <strong>Nuevo Documento</strong>
            </div>
            <div class="navbar-text text-white">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nombre_completo); ?>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Columna principal: Formulario (70%) -->
            <div class="col-lg-9 col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <!-- Header del documento -->
                        <div class="document-header mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                    Solicitud de Servicio - Departamento <?php echo ucfirst($departamento); ?>
                                </h4>
                                <div class="text-muted small">
                                    <i class="bi bi-clock"></i> 
                                    Creado: <?php echo date('d/m/Y H:i'); ?>
                                </div>
                            </div>
                            <hr>
                        </div>

                        <!-- Formulario -->
                        <form id="formDocumento" method="POST">
                            <input type="hidden" name="fase" value="apartado_1">
                            <input type="hidden" name="departamento" value="<?php echo $departamento; ?>">
                            <input type="hidden" name="usuario_creador" value="<?php echo $usuario_id; ?>">
                            
                            <!-- APARTADO 1: Información Inicial -->
                            <div class="apartado-container apartado-editable mb-4">
                                <div class="apartado-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-1-circle-fill text-white"></i>
                                        APARTADO 1 - INFORMACIÓN INICIAL
                                    </h5>
                                    <span class="badge bg-success">EDITABLE</span>
                                </div>
                                
                                <div class="apartado-body">
                                    <?php include __DIR__ . '/../includes/documento_form_apartado1.php'; ?>
                                </div>
                            </div>

                            <!-- APARTADO 2: Detalles (BLOQUEADO) -->
                            <div class="apartado-container apartado-bloqueado mb-4">
                                <div class="apartado-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-2-circle-fill text-white"></i>
                                        APARTADO 2 - DETALLES DEL SERVICIO
                                    </h5>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-lock-fill"></i> BLOQUEADO
                                    </span>
                                </div>
                                
                                <div class="apartado-body text-center py-5">
                                    <i class="bi bi-lock display-1 text-muted mb-3"></i>
                                    <p class="text-muted mb-0">
                                        Esta sección estará disponible cuando envíes el Apartado 1
                                    </p>
                                    <small class="text-muted">
                                        Será completado por otro usuario de tu departamento
                                    </small>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="<?php echo URL_BASE; ?>dashboard/colaborativo.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <div>
                                    <button type="button" id="btnGuardar" class="btn btn-secondary me-2">
                                        <i class="bi bi-save"></i> Guardar Borrador
                                    </button>
                                    <button type="button" id="btnEnviar" class="btn btn-primary">
                                        <i class="bi bi-send"></i> Enviar Apartado 1
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información de ayuda -->
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>Importante:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Guardar:</strong> Guarda tu progreso sin enviarlo. Podrás continuar editando después.</li>
                        <li><strong>Enviar:</strong> Bloquea tu información y notifica al departamento. Cualquier usuario podrá tomar y completar el Apartado 2.</li>
                    </ul>
                </div>
            </div>

            <!-- Columna lateral: Comentarios (30%) -->
            <div class="col-lg-3 col-md-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-chat-left-text"></i> Comentarios
                        </h6>
                    </div>
                    <div class="card-body" id="comentariosPanel" style="max-height: 600px; overflow-y: auto;">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-left-dots display-4"></i>
                            <p class="mt-3 mb-0">No hay comentarios aún</p>
                            <small>Los comentarios aparecerán aquí</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Agregar comentario..." disabled>
                            <button class="btn btn-outline-secondary" type="button" disabled>
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> Los comentarios estarán disponibles después de guardar
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo URL_BASE; ?>assets/js/documentos.js"></script>
    
    <script>
    // Configurar rutas para el JavaScript
    window.DOCUMENTOS_CONFIG = {
        urlBase: '<?php echo URL_BASE; ?>',
        urlGuardar: '<?php echo URL_BASE; ?>documentos/guardar.php',
        urlEnviar: '<?php echo URL_BASE; ?>documentos/enviar_apartado1.php',
        apartado: 1
    };
    </script>
</body>
</html>