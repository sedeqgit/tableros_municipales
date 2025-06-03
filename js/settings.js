/**
 * Script específico para la página de configuración
 * Sistema de Estadística Educativa - SEDEQ
 * 
 * NOTA: Este script complementa la funcionalidad base de home.js
 * No reemplaza el comportamiento de home.js, sino que añade funcionalidades específicas
 * para la página de configuración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Funciones para manejar los modales
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
        }
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
        }
    }

    // Configurar los manejadores de eventos para cerrar los modales
    const modalCloseButtons = document.querySelectorAll('.modal-close, .modal-btn');
    if (modalCloseButtons.length > 0) {
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.classList.remove('show');
                }
            });
        });
    }
      // Manejo del formulario de configuración
    const saveButton = document.querySelector('.save-button');
    
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            // Simulamos una validación simple
            let isValid = true;
            
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Simulamos la validación solo si hay campos de contraseña con valor
            if (currentPassword && newPassword && confirmPassword) {
                if (newPassword.value && confirmPassword.value && newPassword.value !== confirmPassword.value) {
                    alert('Las contraseñas no coinciden');
                    isValid = false;
                }
            }
            
            // Si todo es válido, mostrar el modal de éxito
            if (isValid) {
                // Limpiamos campos de contraseña si existen
                if (currentPassword) currentPassword.value = '';
                if (newPassword) newPassword.value = '';
                if (confirmPassword) confirmPassword.value = '';
                
                // Mostramos el modal de éxito
                showModal('successModal');
            }
        });
    }
    
    // Función para mostrar errores (utilizada en la validación)
    function displayError(inputElement, errorMessage) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'password-error text-danger';
        errorDiv.textContent = errorMessage;
        errorDiv.style.color = 'red';
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '5px';
        
        inputElement.parentNode.appendChild(errorDiv);
        inputElement.style.borderColor = 'red';
    }
      // Zona de peligro - Desactivar cuenta
    const dangerButton = document.querySelector('.danger-button');
    if (dangerButton) {
        dangerButton.addEventListener('click', function() {
            // Mostrar el modal de confirmación
            showModal('confirmModal');
        });
    }
    
    // Manejar la confirmación de desactivación
    const modalConfirmBtn = document.getElementById('modalConfirmBtn');
    if (modalConfirmBtn) {
        modalConfirmBtn.addEventListener('click', function() {
            // Ocultar el modal de confirmación
            hideModal('confirmModal');
            
            // Mostrar mensaje de éxito y cambiar el texto
            document.getElementById('modalMessage').textContent = 'Su cuenta ha sido desactivada correctamente.';
            showModal('successModal');
        });
    }
    
    // Botón cancelar
    const cancelButton = document.querySelector('.cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            // Resetear valores de los campos a los valores originales
            const fullnameField = document.getElementById('fullname');
            const emailField = document.getElementById('email');
            
            if (fullnameField) fullnameField.value = 'Emiliano Ledesma';
            if (emailField) emailField.value = 'practicas25.dppee@gmail.com';
            
            // Limpiar campos de contraseña
            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => field.value = '');
            
            alert('Se han descartado los cambios');
        });
    }
});

/**
 * Muestra un mensaje de error debajo de un campo de formulario
 * @param {HTMLElement} element - El elemento de entrada
 * @param {string} message - El mensaje de error a mostrar
 */
function displayError(element, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'password-error';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '5px';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    element.parentNode.appendChild(errorDiv);
    element.style.borderColor = '#dc3545';
}
