<?php
/**
 * Template del formulario - Apartado 2
 * Detalles del servicio y resolución (completado por Usuario 2)
 */
?>

<!-- Información de quien recibe -->
<div class="row mb-4">
    <div class="col-md-6">
        <label for="nombre_recibe" class="form-label fw-bold">
            Nombre de quien recibe solicitud: <span class="text-danger">*</span>
        </label>
        <input 
            type="text" 
            class="form-control" 
            id="nombre_recibe" 
            name="nombre_recibe" 
            placeholder="Nombre completo"
            required
        >
    </div>
    <div class="col-md-6">
        <label for="fecha_recibido" class="form-label fw-bold">
            Fecha y hora de recibido: <span class="text-danger">*</span>
        </label>
        <input 
            type="datetime-local" 
            class="form-control" 
            id="fecha_recibido" 
            name="fecha_recibido"
            required
        >
    </div>
</div>

<hr class="my-4">

<!-- DETALLES DEL SERVICIO SOLICITADO -->
<div class="detalles-servicio-header mb-3">
    <h5 class="mb-0 text-center">DETALLES DEL SERVICIO SOLICITADO</h5>
</div>

<div class="mb-4">
    <label class="form-label fw-bold">
        Servicio solicitado: <span class="text-danger">*</span>
    </label>
    <div class="row">
        <div class="col-md-3">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="servicio_tratamiento_agua" 
                    id="servicio_tratamiento_agua"
                    value="1"
                >
                <label class="form-check-label" for="servicio_tratamiento_agua">
                    Tratamiento de agua
                </label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="servicio_evaluacion_quimicos" 
                    id="servicio_evaluacion_quimicos"
                    value="1"
                >
                <label class="form-check-label" for="servicio_evaluacion_quimicos">
                    Evaluación de productos químicos
                </label>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="servicio_calibracion" 
                    id="servicio_calibracion"
                    value="1"
                >
                <label class="form-check-label" for="servicio_calibracion">
                    Calibración y/o verificación de equipos
                </label>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="servicio_otro" 
                    id="servicio_otro"
                    value="1"
                >
                <label class="form-check-label" for="servicio_otro">
                    Otro. Especifique:
                </label>
            </div>
            <input 
                type="text" 
                class="form-control mt-2" 
                id="servicio_otro_especificar" 
                name="servicio_otro_especificar" 
                placeholder="Especifique el servicio"
                disabled
            >
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-bold">
        Prioridad: <span class="text-danger">*</span>
    </label>
    <div class="row">
        <div class="col-md-4">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="radio" 
                    name="prioridad" 
                    id="prioridad_baja" 
                    value="Baja"
                    required
                >
                <label class="form-check-label" for="prioridad_baja">
                    Baja
                </label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="radio" 
                    name="prioridad" 
                    id="prioridad_media" 
                    value="Media"
                >
                <label class="form-check-label" for="prioridad_media">
                    Media
                </label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="radio" 
                    name="prioridad" 
                    id="prioridad_alta" 
                    value="Alta"
                >
                <label class="form-check-label" for="prioridad_alta">
                    Alta
                </label>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <label for="descripcion_servicio" class="form-label fw-bold">
        Descripción del servicio: <span class="text-danger">*</span>
    </label>
    <textarea 
        class="form-control" 
        id="descripcion_servicio" 
        name="descripcion_servicio" 
        rows="5" 
        placeholder="Describa detalladamente el servicio solicitado..."
        required
    ></textarea>
</div>

<div class="mb-4">
    <label for="resumen_resultados" class="form-label fw-bold">
        Resumen de resultados: <span class="text-danger">*</span>
    </label>
    <textarea 
        class="form-control" 
        id="resumen_resultados" 
        name="resumen_resultados" 
        rows="5" 
        placeholder="Resumen de los resultados obtenidos..."
        required
    ></textarea>
</div>

<hr class="my-4">

<!-- Fecha y hora de entrega -->
<div class="alert alert-warning mb-3">
    <i class="bi bi-clock-history"></i>
    <strong>Fecha y hora de entrega</strong>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <label for="fecha_entrega" class="form-label fw-bold">
            Fecha de entrega: <span class="text-danger">*</span>
        </label>
        <input 
            type="date" 
            class="form-control" 
            id="fecha_entrega" 
            name="fecha_entrega"
            required
        >
    </div>
    <div class="col-md-6">
        <label for="hora_entrega" class="form-label fw-bold">
            Hora de entrega: <span class="text-danger">*</span>
        </label>
        <input 
            type="time" 
            class="form-control" 
            id="hora_entrega" 
            name="hora_entrega"
            required
        >
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Nota:</strong> Al enviar este apartado, se generará el documento final completo.
</div>

<script>
// Habilitar campo de "Otro" cuando se marca el checkbox
document.getElementById('servicio_otro').addEventListener('change', function() {
    document.getElementById('servicio_otro_especificar').disabled = !this.checked;
    if (!this.checked) {
        document.getElementById('servicio_otro_especificar').value = '';
    }
});
</script>