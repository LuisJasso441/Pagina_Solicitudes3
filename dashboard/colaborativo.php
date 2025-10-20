<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sesion.php';

if (!es_usuario_colaborativo()) {
    establecer_alerta('error', 'No tiene acceso a este panel');
    redirigir(URL_BASE . 'dashboard/departamento.php');
}

$nombre_usuario = $_SESSION['nombre_completo'];
$departamento = $_SESSION['departamento_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Colaborativo - <?php echo htmlspecialchars($departamento); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>assets/css/formularios.css">
    
    <!-- Sistema de notificaciones -->
    <script src="<?php echo URL_BASE; ?>assets/js/notificaciones.js" defer></script>
</head>
<body>
    
    <div class="dashboard-container">
        
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="bi bi-people-fill text-white fs-1 mb-2"></i>
                <h4><?php echo htmlspecialchars($departamento); ?></h4>
                <small class="text-white-50"><?php echo htmlspecialchars($nombre_usuario); ?></small>
                <span class="badge bg-info mt-2">Colaborativo</span>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo URL_BASE; ?>dashboard/colaborativo.php">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    
                    <hr class="text-white-50 my-2">
                    <small class="text-white-50 px-3 fw-bold">MIS SOLICITUDES</small>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
                            <i class="bi bi-plus-circle"></i> Nueva Solicitud
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_BASE; ?>solicitudes/crear_mantenimiento.php">
                            <i class="bi bi-tools"></i> Solicitar Mantenimiento
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_BASE; ?>solicitudes/listar.php">
                            <i class="bi bi-list-ul"></i> Mis Solicitudes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_BASE; ?>solicitudes/listar_mantenimientos.php">
                            <i class="bi bi-wrench"></i> Mis Mantenimientos
                        </a>
                    </li>
                    
                    <hr class="text-white-50 my-2">
                    <small class="text-white-50 px-3 fw-bold">ÁREA COLABORATIVA</small>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_BASE; ?>documentos/crear.php">
                            <i class="bi bi-file-earmark-plus"></i> Nuevo Documento
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_BASE; ?>colaborativo/documentos.php">
                            <i class="bi bi-folder-symlink"></i> Documentos Compartidos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_BASE; ?>colaborativo/base_datos.php">
                            <i class="bi bi-database"></i> Base de Datos
                        </a>
                    </li>
                    
                    <hr class="text-white-50 my-3">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold" href="<?php echo URL_BASE; ?>auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            <div class="content-wrapper">
                
                <div class="top-navbar d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="welcome-text">¡Bienvenido, <?php echo htmlspecialchars(explode(' ', $nombre_usuario)[0]); ?>!</h2>
                        <p class="text-muted mb-0">
                            <i class="bi bi-calendar3"></i> 
                            <?php echo obtener_fecha_actual_espanol(); ?>
                        </p>
                    </div>
                    <div class="user-info">
                        <!-- Notificaciones - DESHABILITADO (manteniendo SSE activo) -->
                        <?php // include __DIR__ . '/../includes/notificaciones_ui.php'; ?>
                        
                        <span class="user-badge">
                            <i class="bi bi-people-fill"></i>
                            <?php echo htmlspecialchars($departamento); ?>
                        </span>
                    </div>
                </div>

                <?php echo mostrar_alerta(); ?>

                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Área Colaborativa</strong><br>
                        Como parte de <?php echo htmlspecialchars($departamento); ?>, tienes acceso a documentos compartidos y creación de documentos colaborativos.
                    </div>
                </div>

                <!-- ACCIONES RÁPIDAS -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header">
                                <i class="bi bi-lightning-charge"></i> Acciones Rápidas
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-3">
                                    <a href="#" class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
                                        <i class="bi bi-plus-circle"></i> Nueva Solicitud
                                    </a>
                                    <a href="<?php echo URL_BASE; ?>documentos/crear.php" class="btn btn-success">
                                        <i class="bi bi-file-earmark-plus"></i> Crear Documento Colaborativo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

    </div>

    <!-- Botón flotante de cambio de tema -->
    <button class="theme-toggle-float" id="themeToggle" aria-label="Cambiar tema">
        <span class="icon-sun"><i class="bi bi-sun-fill"></i></span>
        <span class="icon-moon"><i class="bi bi-moon-fill"></i></span>
    </button>

    <!-- Modal de Nueva Solicitud -->
    <?php include __DIR__ . '/../solicitudes/modal_crear.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const bodyElement = document.body;
        const currentTheme = localStorage.getItem('theme') || 'light';
        bodyElement.setAttribute('data-theme', currentTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = bodyElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            themeToggle.classList.add('rotating');
            setTimeout(() => {
                themeToggle.classList.remove('rotating');
            }, 500);
            bodyElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>

</body>
</html>