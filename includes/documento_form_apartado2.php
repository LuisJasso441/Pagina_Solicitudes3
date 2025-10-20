<?php
/**
 * Template del formulario - Apartado 2
 * Detalles y resolución (completado por Usuario 2)
 * Este apartado se mostrará cuando el Usuario 2 reciba el documento
 */
?>

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