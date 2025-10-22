<?php
/**
 * Interfaz de comentarios para documentos colaborativos
 * Variables disponibles: $comentarios, $permisos, $documento
 */
?>

<div class="row">
    <!-- Panel de comentarios existentes -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-chat-dots"></i>
                    Comentarios (<?= count($comentarios) ?>)
                </h6>
            </div>
            <div class="card-body comentarios-panel">
                <?php if (empty($comentarios)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-square-text text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No hay comentarios todavía</p>
                        <?php if ($permisos['puede_comentar']): ?>
                            <p class="text-muted small">Sé el primero en comentar</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <?php
                        $tipo_class = 'tipo-' . $comentario['tipo_mensaje'];
                        $tipo_icon = [
                            'normal' => 'chat-left-text',
                            'aclaracion' => 'question-circle',
                            'correccion' => 'pencil-square',
                            'solicitud' => 'clipboard-check'
                        ];
                        $icono = $tipo_icon[$comentario['tipo_mensaje']] ?? 'chat-left-text';
                        
                        $tipo_badge = [
                            'normal' => 'secondary',
                            'aclaracion' => 'info',
                            'correccion' => 'warning',
                            'solicitud' => 'primary'
                        ];
                        $badge_color = $tipo_badge[$comentario['tipo_mensaje']] ?? 'secondary';
                        
                        $puede_eliminar = ($comentario['usuario_autor_id'] == $usuario_id);
                        ?>
                        
                        <div class="comentario-item <?= $tipo_class ?>" data-comentario-id="<?= $comentario['id'] ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong class="text-primary">
                                        <i class="bi bi-person-circle"></i>
                                        <?= htmlspecialchars($comentario['usuario_autor_nombre']) ?>
                                    </strong>
                                    <span class="badge bg-light text-dark ms-2">
                                        <?= htmlspecialchars($comentario['departamento_autor']) ?>
                                    </span>
                                    <span class="badge bg-<?= $badge_color ?> ms-1">
                                        <i class="bi bi-<?= $icono ?>"></i>
                                        <?= ucfirst($comentario['tipo_mensaje']) ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i>
                                        <?= date('d/m/Y H:i', strtotime($comentario['fecha_hora_publicacion'])) ?>
                                    </small>
                                    <?php if ($puede_eliminar && $documento['estado'] != 'completado'): ?>
                                    <button class="btn btn-sm btn-outline-danger ms-2 btn-eliminar-comentario" 
                                            data-comentario-id="<?= $comentario['id'] ?>"
                                            title="Eliminar comentario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="mb-0 text-dark">
                                <?= nl2br(htmlspecialchars($comentario['texto_comentario'])) ?>
                            </p>
                            
                            <?php if ($comentario['editado']): ?>
                            <small class="text-muted fst-italic">
                                <i class="bi bi-pencil"></i>
                                Editado el <?= date('d/m/Y H:i', strtotime($comentario['fecha_edicion'])) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Panel para agregar comentario -->
    <div class="col-lg-4">
        <?php if ($permisos['puede_comentar']): ?>
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="bi bi-plus-circle"></i>
                    Agregar Comentario
                </h6>
            </div>
            <div class="card-body">
                <form id="formNuevoComentario">
                    <input type="hidden" name="documento_id" value="<?= $documento['id'] ?>">
                    <input type="hidden" name="folio" value="<?= htmlspecialchars($documento['folio']) ?>">
                    
                    <!-- Tipo de mensaje -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de mensaje</label>
                        <select class="form-select" name="tipo_mensaje" required>
                            <option value="normal" selected>💬 Comentario normal</option>
                            <option value="aclaracion">❓ Solicitud de aclaración</option>
                            <option value="correccion">✏️ Corrección</option>
                            <option value="solicitud">📋 Solicitud específica</option>
                        </select>
                    </div>
                    
                    <!-- Texto del comentario -->
                    <div class="mb-3">
                        <label class="form-label">Comentario</label>
                        <textarea class="form-control" 
                                  name="texto_comentario" 
                                  rows="5" 
                                  placeholder="Escribe tu comentario aquí..."
                                  required></textarea>
                        <div class="form-text">
                            Máximo 1000 caracteres
                        </div>
                    </div>
                    
                    <!-- Botón enviar -->
                    <button type="submit" class="btn btn-success w-100" id="btnEnviarComentario">
                        <i class="bi bi-send"></i> Publicar Comentario
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Información sobre comentarios -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-info-circle text-primary"></i>
                    Guía de Comentarios
                </h6>
                <small class="text-muted">
                    <ul class="mb-0 ps-3">
                        <li><strong>Normal:</strong> Comentarios generales</li>
                        <li><strong>Aclaración:</strong> Para resolver dudas</li>
                        <li><strong>Corrección:</strong> Señalar errores o ajustes</li>
                        <li><strong>Solicitud:</strong> Pedir información adicional</li>
                    </ul>
                </small>
            </div>
        </div>
        
        <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Comentarios bloqueados</strong>
                    <p class="mb-0 mt-2 small">
                        <?php if ($documento['estado'] == 'completado'): ?>
                            No se pueden agregar comentarios a documentos completados.
                        <?php else: ?>
                            No tienes permiso para comentar en este documento.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Estadísticas de comentarios -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-graph-up text-primary"></i>
                    Estadísticas
                </h6>
                
                <?php
                $stats_tipos = [];
                $usuarios_comentaron = [];
                
                foreach ($comentarios as $com) {
                    $tipo = $com['tipo_mensaje'];
                    $stats_tipos[$tipo] = ($stats_tipos[$tipo] ?? 0) + 1;
                    $usuarios_comentaron[$com['usuario_autor_id']] = true;
                }
                ?>
                
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-chat-dots text-secondary"></i>
                        Total: <strong><?= count($comentarios) ?></strong>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-people text-info"></i>
                        Participantes: <strong><?= count($usuarios_comentaron) ?></strong>
                    </li>
                    <?php if (!empty($comentarios)): ?>
                    <li class="mb-2">
                        <i class="bi bi-clock text-success"></i>
                        Último: <strong><?= date('d/m/Y H:i', strtotime($comentarios[count($comentarios)-1]['fecha_hora_publicacion'])) ?></strong>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <?php if (!empty($stats_tipos)): ?>
                <hr>
                <small class="text-muted">Por tipo:</small>
                <ul class="list-unstyled small mb-0 mt-2">
                    <?php foreach ($stats_tipos as $tipo => $cantidad): ?>
                    <li>
                        <?php
                        $tipo_nombres = [
                            'normal' => '💬 Normal',
                            'aclaracion' => '❓ Aclaración',
                            'correccion' => '✏️ Corrección',
                            'solicitud' => '📋 Solicitud'
                        ];
                        ?>
                        <?= $tipo_nombres[$tipo] ?? $tipo ?>: <strong><?= $cantidad ?></strong>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Script para manejar comentarios
document.addEventListener('DOMContentLoaded', function() {
    // Formulario nuevo comentario
    const formComentario = document.getElementById('formNuevoComentario');
    if (formComentario) {
        formComentario.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnEnviar = document.getElementById('btnEnviarComentario');
            const textoOriginal = btnEnviar.innerHTML;
            
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
            
            const formData = new FormData(this);
            
            fetch('/Pagina_Solicitudes3/documentos/procesar_comentario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('success', 'Éxito', data.message);
                    
                    // Recargar página después de 1 segundo
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    mostrarAlerta('danger', 'Error', data.message);
                    btnEnviar.disabled = false;
                    btnEnviar.innerHTML = textoOriginal;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('danger', 'Error', 'Error al enviar el comentario');
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = textoOriginal;
            });
        });
    }
    
    // Botones eliminar comentario
    const botonesEliminar = document.querySelectorAll('.btn-eliminar-comentario');
    botonesEliminar.forEach(btn => {
        btn.addEventListener('click', function() {
            const comentarioId = this.getAttribute('data-comentario-id');
            
            if (confirm('¿Estás seguro de eliminar este comentario? Esta acción no se puede deshacer.')) {
                eliminarComentario(comentarioId);
            }
        });
    });
});

function eliminarComentario(comentarioId) {
    const formData = new FormData();
    formData.append('comentario_id', comentarioId);
    formData.append('accion', 'eliminar');
    
    fetch('/Pagina_Solicitudes3/documentos/procesar_comentario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('success', 'Éxito', data.message);
            
            // Eliminar el elemento del DOM con animación
            const comentarioElement = document.querySelector(`[data-comentario-id="${comentarioId}"]`);
            if (comentarioElement) {
                comentarioElement.style.transition = 'opacity 0.3s ease';
                comentarioElement.style.opacity = '0';
                
                setTimeout(() => {
                    comentarioElement.remove();
                    
                    // Si no quedan comentarios, recargar para mostrar mensaje vacío
                    const comentariosRestantes = document.querySelectorAll('.comentario-item');
                    if (comentariosRestantes.length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } else {
            mostrarAlerta('danger', 'Error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('danger', 'Error', 'Error al eliminar el comentario');
    });
}

function mostrarAlerta(tipo, titulo, mensaje) {
    const alertaHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
             style="z-index: 9999; min-width: 300px;" role="alert">
            <strong>${titulo}:</strong> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertaHTML);
    
    setTimeout(() => {
        const alerta = document.querySelector('.alert');
        if (alerta) {
            alerta.remove();
        }
    }, 5000);
}
</script>