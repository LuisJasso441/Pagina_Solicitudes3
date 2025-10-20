<?php
/**
 * Documentos colaborativos compartidos
 * Solo accesible para Normatividad, Ventas y Laboratorio
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

$nombre_usuario = $_SESSION['nombre_completo'];
$departamento = $_SESSION['departamento_nombre'];

// Parámetros de búsqueda
$busqueda = isset($_GET['buscar']) ? limpiar_dato($_GET['buscar']) : '';
$categoria = isset($_GET['categoria']) ? limpiar_dato($_GET['categoria']) : '';

// Obtener documentos
try {
    $pdo = conectarDB();
    
    $sql = "SELECT d.*, u.nombre_completo as subido_por_nombre, u.departamento as subido_por_depto
            FROM documentos_colaborativos d
            INNER JOIN usuarios u ON d.subido_por = u.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($busqueda)) {
        $sql .= " AND (d.titulo LIKE ? OR d.descripcion LIKE ?)";
        $params[] = "%$busqueda%";
        $params[] = "%$busqueda%";
    }
    
    if (!empty($categoria)) {
        $sql .= " AND d.categoria = ?";
        $params[] = $categoria;
    }
    
    $sql .= " ORDER BY d.fecha_subida DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documentos = $stmt->fetchAll();
    
    // Obtener categorías disponibles
    $stmt = $pdo->query("SELECT DISTINCT categoria FROM documentos_colaborativos ORDER BY categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    establecer_alerta('error', 'Error al cargar documentos: ' . $e->getMessage());
    $documentos = [];
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos Compartidos - <?php echo htmlspecialchars($departamento); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>assets/css/dashboard.css">
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
                            <i class="bi bi-folder-symlink"></i> Documentos Compartidos
                        </h2>
                        <p class="text-muted mb-0">
                            Área colaborativa entre Normatividad, Ventas y Laboratorio
                        </p>
                    </div>
                    <div>
                        <a href="<?php echo URL_BASE; ?>colaborativo/crear_documento.php" class="btn btn-gradient">
                            <i class="bi bi-cloud-upload"></i> Subir Documento
                        </a>
                    </div>
                </div>

                <!-- Alertas -->
                <?php echo mostrar_alerta(); ?>

                <!-- Filtros de Búsqueda -->
                <div class="card card-custom mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Buscar documento</label>
                                <input type="text" name="buscar" class="form-control" 
                                       placeholder="Título o descripción..."
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Categoría</label>
                                <select name="categoria" class="form-select">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo $categoria == $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-gradient w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Documentos -->
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-files"></i> Documentos Disponibles</span>
                        <span class="badge bg-primary"><?php echo count($documentos); ?> documento(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($documentos)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-folder2-open fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-0">No hay documentos disponibles</p>
                            <a href="<?php echo URL_BASE; ?>colaborativo/crear_documento.php" class="btn btn-gradient mt-3">
                                <i class="bi bi-cloud-upload"></i> Subir primer documento
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($documentos as $doc): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="<?php echo obtener_icono_archivo($doc['nombre_archivo']); ?> fs-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h5 class="mb-1">
                                                    <a href="<?php echo URL_BASE; ?>colaborativo/ver_documento.php?id=<?php echo $doc['id']; ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($doc['titulo']); ?>
                                                    </a>
                                                </h5>
                                                <?php if (!empty($doc['categoria'])): ?>
                                                <span class="badge bg-info text-dark">
                                                    <?php echo htmlspecialchars($doc['categoria']); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="<?php echo URL_BASE; ?>colaborativo/descargar.php?id=<?php echo $doc['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                        <?php if (!empty($doc['descripcion'])): ?>
                                        <p class="mb-2 text-muted">
                                            <?php echo htmlspecialchars($doc['descripcion']); ?>
                                        </p>
                                        <?php endif; ?>
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="bi bi-person me-1"></i>
                                            <span class="me-3"><?php echo htmlspecialchars($doc['subido_por_nombre']); ?></span>
                                            <i class="bi bi-building me-1"></i>
                                            <span class="me-3"><?php echo htmlspecialchars($doc['subido_por_depto']); ?></span>
                                            <i class="bi bi-clock me-1"></i>
                                            <span><?php echo formatear_fecha($doc['fecha_subida'], true); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
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
    </script>

</body>
</html>