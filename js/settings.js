/**
 * Script específico para la página de configuración
 * Sistema de Estadística Educativa - SEDEQ
 * 
 * NOTA: Este script complementa la funcionalidad base de home.js
 * No reemplaza el comportamiento de home.js, sino que añade funcionalidades específicas
 * para la página de configuración
 */
document.addEventListener('DOMContentLoaded', function() {
    // Manejo de la navegación por pestañas de configuración
    const settingsNavLinks = document.querySelectorAll('.settings-nav-link');
    
    if (settingsNavLinks.length > 0) {
        settingsNavLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Eliminar clase activa de todos los enlaces
                settingsNavLinks.forEach(l => l.classList.remove('active'));
                
                // Añadir clase activa al enlace clickeado
                this.classList.add('active');
                
                // Aquí se podría implementar la lógica para mostrar la sección correspondiente
                // Por ejemplo, cargar el contenido de forma dinámica o mostrar/ocultar secciones
                // En esta versión de demostración, solo activamos visualmente el enlace
            });
        });
    }
    
    // Validación del formulario de cambio de contraseña
    const passwordForm = document.querySelector('.settings-section:nth-child(2)');
    const saveButton = document.querySelector('.save-button');
    
    if (saveButton && passwordForm) {
        saveButton.addEventListener('click', function() {
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            let isValid = true;
            
            // Limpiar errores anteriores
            const errorElements = document.querySelectorAll('.password-error');
            errorElements.forEach(el => el.remove());
            
            // Validar que la contraseña actual no esté vacía
            if (!currentPassword.value.trim()) {
                displayError(currentPassword, 'La contraseña actual es requerida');
                isValid = false;
            }
            
            // Validar que la nueva contraseña no esté vacía
            if (!newPassword.value.trim()) {
                displayError(newPassword, 'La nueva contraseña es requerida');
                isValid = false;
            }
            
            // Validar que la confirmación no esté vacía y coincida con la nueva contraseña
            if (!confirmPassword.value.trim()) {
                displayError(confirmPassword, 'La confirmación de contraseña es requerida');
                isValid = false;
            } else if (newPassword.value !== confirmPassword.value) {
                displayError(confirmPassword, 'Las contraseñas no coinciden');
                isValid = false;
            }
            
            // Si todo es válido, mostrar mensaje de éxito (en una aplicación real, se enviaría el formulario)
            if (isValid) {
                alert('Los cambios han sido guardados correctamente');
                // Limpiar campos de contraseña
                currentPassword.value = '';
                newPassword.value = '';
                confirmPassword.value = '';
            }
        });
    }
    
    // Zona de peligro - Desactivar cuenta
    const dangerButton = document.querySelector('.danger-button');
    if (dangerButton) {
        dangerButton.addEventListener('click', function() {
            const confirmation = confirm('¿Está seguro que desea desactivar su cuenta? Esta acción no se puede deshacer.');
            if (confirmation) {
                alert('Esta es una versión de demostración. En un sistema real, su cuenta sería desactivada.');
            }
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
