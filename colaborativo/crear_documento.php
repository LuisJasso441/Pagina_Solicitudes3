<?php
/**
 * Subir documento colaborativo
 * Solo accesible para departamentos colaborativos
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/verificar_sesion.php';
require_once __DIR__ . '/../config/database.php';

// Verificar que sea usuario colaborativo
if (!es_usuario_colaborativo()) {
    establecer_alerta('error', 'No tiene acceso a esta sección');
    redirigir(URL_BASE . 'dashboard/departamento.php');
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_completo'];
$departamento = $_SESSION['departamento_nombre'];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $errores = [];
        
        // Validar campos
        if (empty($_POST['titulo'])) $errores[] = "El título es obligatorio";
        if (empty($_FILES['archivo']['name'])) $errores[] = "Debe seleccionar un archivo";
        
        if (empty($errores)) {
            // Validar archivo
            $validacion = validar_archivo($_FILES['archivo']);
            
            if (!$validacion['valido']) {
                $errores[] = $validacion['error'];
            } else {
                // Subir archivo
                $nombre_archivo = subir_archivo($_FILES['archivo'], 'colaborativo');
                
                if ($nombre_archivo) {
                    // Preparar datos
                    $titulo = limpiar_dato($_POST['titulo']);
                    $descripcion = limpiar_dato($_POST['descripcion']);
                    $categoria = limpiar_dato($_POST['categoria']);
                    
                    // Insertar en BD
                    $pdo = conectarDB();
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO documentos_colaborativos 
                        (titulo, descripcion, categoria, nombre_archivo, subido_por, fecha_subida)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $stmt->execute([
                        $titulo,
                        $descripcion,
                        $categoria,
                        $nombre_archivo,
                        $usuario_id
                    ]);
                    
                    establecer_alerta('success', '¡Documento subido exitosamente!');
                    redirigir(URL_BASE . 'colaborativo/documentos.php');
                    
                } else {
                    $errores[] = "Error al subir el archivo";
                }
            }
        }
        
        if (!empty($errores)) {
            establecer_alerta('error', implode('<br>', $errores));
        }
        
    } catch (Exception $e) {
        establecer_alerta('error', 'Error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Documento - <?php echo htmlspecialchars($departamento); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>assets/css/formularios.css">
</head>
<body>
    
    <div class="dashboard-container">
        
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../includes/sidebar_colaborativo.php'; ?>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            <div class="content-wrapper">
                
                <!-- Header -->
                <div class="top-navbar d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="welcome-text">
                            <i class="bi bi-cloud-upload"></i> Subir Documento
                        </h2>
                        <p class="text-muted mb-0">
                            Compartir archivos con el área colaborativa
                        </p>
                    </div>
                    <div>
                        <a href="<?php echo URL_BASE; ?>colaborativo/documentos.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <!-- Alertas -->
                <?php echo mostrar_alerta(); ?>

                <!-- Formulario -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card card-custom">
                            <div class="card-header">
                                <i class="bi bi-file-earmark-arrow-up"></i> Información del Documento
                            </div>
                            <div class="card-body">
                                
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Importante:</strong> Este documento será visible para todos los usuarios de 
                                    Normatividad, Ventas y Laboratorio.
                                </div>

                                <form method="POST" enctype="multipart/form-data">
                                    
                                    <!-- Título -->
                                    <div class="mb-3">
                                        <label for="titulo" class="form-label required">Título del documento</label>
                                        <input type="text" class="form-control" id="titulo" name="titulo" 
                                               placeholder="Ej: Manual de procedimientos 2025"
                                               maxlength="200" required>
                                    </div>

                                    <!-- Descripción -->
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                                  rows="3" placeholder="Breve descripción del contenido (opcional)"></textarea>
                                    </div>

                                    <!-- Categoría -->
                                    <div class="mb-3">
                                        <label for="categoria" class="form-label">Categoría</label>
                                        <select class="form-select" id="categoria" name="categoria">
                                            <option value="">Sin categoría</option>
                                            <option value="Manuales">Manuales</option>
                                            <option value="Procedimientos">Procedimientos</option>
                                            <option value="Políticas">Políticas</option>
                                            <option value="Formatos">Formatos</option>
                                            <option value="Reportes">Reportes</option>
                                            <option value="Capacitación">Capacitación</option>
                                            <option value="Normatividad">Normatividad</option>
                                            <option value="Otros">Otros</option>
                                        </select>
                                    </div>

                                    <!-- Archivo -->
                                    <div class="mb-4">
                                        <label for="archivo" class="form-label required">Archivo</label>
                                        <input type="file" class="form-control" id="archivo" name="archivo" required>
                                        <small class="text-muted">
                                            Tipos permitidos: PDF, Word, Excel, PowerPoint, Imágenes<br>
                                            Tamaño máximo: <?php echo (MAX_FILE_SIZE / (1024 * 1024)); ?>MB
                                        </small>
                                    </div>

                                    <!-- Info del usuario -->
                                    <div class="alert alert-light">
                                        <strong>Subido por:</strong> <?php echo htmlspecialchars($nombre_usuario); ?><br>
                                        <strong>Departamento:</strong> <?php echo htmlspecialchars($departamento); ?>
                                    </div>

                                    <!-- Botones -->
                                    <div class="d-flex justify-content-between">
                                        <a href="<?php echo URL_BASE; ?>colaborativo/documentos.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-gradient">
                                            <i class="bi bi-cloud-upload"></i> Subir Documento
                                        </button>
                                    </div>

                                </form>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Modo oscuro
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

        // Preview del archivo seleccionado
        document.getElementById('archivo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                console.log(`Archivo seleccionado: ${file.name} (${sizeMB}MB)`);
            }
        });
    </script>

</body>
</html>