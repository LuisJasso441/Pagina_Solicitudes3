/**
 * JavaScript para el sistema de documentos colaborativos
 * Maneja la lógica del formulario, validación y envío
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de documentos cargado');
    
    const form = document.getElementById('formDocumento');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnEnviar = document.getElementById('btnEnviar');
    
    // Validar que al menos un servicio esté seleccionado
    function validarServicios() {
        const servicios = [
            document.getElementById('servicio_tratamiento_agua'),
            document.getElementById('servicio_evaluacion_quimicos'),
            document.getElementById('servicio_calibracion'),
            document.getElementById('servicio_otro')
        ];
        
        const algunoMarcado = servicios.some(checkbox => checkbox.checked);
        
        if (!algunoMarcado) {
            alert('Debe seleccionar al menos un servicio');
            return false;
        }
        
        // Si seleccionó "Otro", validar que especifique
        const otroCheckbox = document.getElementById('servicio_otro');
        const otroInput = document.getElementById('servicio_otro_especificar');
        
        if (otroCheckbox.checked && !otroInput.value.trim()) {
            alert('Debe especificar el tipo de servicio en "Otro"');
            otroInput.focus();
            return false;
        }
        
        return true;
    }
    
    // Validar formulario completo
    function validarFormulario() {
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return false;
        }
        
        if (!validarServicios()) {
            return false;
        }
        
        return true;
    }
    
    // Botón Guardar (sin validación estricta)
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Confirmar
            if (!confirm('¿Desea guardar el documento como borrador?\n\nPodrá continuar editándolo después.')) {
                return;
            }
            
            // Agregar campo de acción
            const inputAccion = document.createElement('input');
            inputAccion.type = 'hidden';
            inputAccion.name = 'accion';
            inputAccion.value = 'guardar';
            form.appendChild(inputAccion);
            
            // Mostrar loading
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            // Enviar formulario
            form.submit();
        });
    }
    
    // Botón Enviar (con validación completa)
    if (btnEnviar) {
        btnEnviar.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validar
            if (!validarFormulario()) {
                alert('Por favor, complete todos los campos obligatorios antes de enviar.');
                return;
            }
            
            // Confirmar
            if (!confirm('¿Está seguro de enviar el Apartado 1?\n\n⚠️ Una vez enviado, NO podrá modificar esta información.\n\nSe notificará a otro usuario de su departamento para completar el Apartado 2.')) {
                return;
            }
            
            // Agregar campo de acción
            const inputAccion = document.createElement('input');
            inputAccion.type = 'hidden';
            inputAccion.name = 'accion';
            inputAccion.value = 'enviar';
            form.appendChild(inputAccion);
            
            // Mostrar loading
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
            btnGuardar.disabled = true;
            
            // Enviar formulario
            form.submit();
        });
    }
    
    // Habilitar/deshabilitar campo "Otro"
    const servicioOtro = document.getElementById('servicio_otro');
    const servicioOtroInput = document.getElementById('servicio_otro_especificar');
    
    if (servicioOtro && servicioOtroInput) {
        servicioOtro.addEventListener('change', function() {
            servicioOtroInput.disabled = !this.checked;
            if (!this.checked) {
                servicioOtroInput.value = '';
            } else {
                servicioOtroInput.focus();
            }
        });
    }
    
    // Autoguardado cada 2 minutos (opcional)
    let autoguardadoInterval;
    
    function iniciarAutoguardado() {
        // Desactivado por ahora, se implementará en Fase 2
        // autoguardadoInterval = setInterval(autoguardar, 120000); // 2 minutos
    }
    
    function autoguardar() {
        console.log('Autoguardado ejecutado');
        // Implementar en Fase 2
    }
    
    // Prevenir salida accidental
    let formularioModificado = false;
    
    form.addEventListener('input', function() {
        formularioModificado = true;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formularioModificado) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de salir? Los cambios no guardados se perderán.';
            return e.returnValue;
        }
    });
    
    // Limpiar flag cuando se envía el formulario
    form.addEventListener('submit', function() {
        formularioModificado = false;
    });
    
    // Validación en tiempo real
    const camposRequeridos = form.querySelectorAll('[required]');
    
    camposRequeridos.forEach(campo => {
        campo.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
        
        campo.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    console.log('Listeners de formulario configurados correctamente');
});