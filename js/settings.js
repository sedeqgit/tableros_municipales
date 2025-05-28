/**
 * Script específico para la página de configuración
 * Sistema de Estadística Educativa - SEDEQ
 * 
 * NOTA: Este script complementa la funcionalidad base de home.js
 * No reemplaza el comportamiento de home.js, sino que añade funcionalidades específicas
 * para la página de configuración con animaciones suaves
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar animaciones para la página de configuración
    initSettingsAnimations();
    
    // Manejo de la navegación por pestañas de configuración con animaciones
    const settingsNavLinks = document.querySelectorAll('.settings-nav-link');
    
    if (settingsNavLinks.length > 0) {
        settingsNavLinks.forEach((link, index) => {
            // Añadir clases de animación inicial
            link.classList.add('animate-fade');
            link.style.animationDelay = `${index * 100}ms`;
            
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Eliminar clase activa de todos los enlaces con animación
                settingsNavLinks.forEach(l => {
                    l.classList.remove('active');
                    l.style.transform = 'translateX(0)';
                });
                
                // Añadir clase activa al enlace clickeado con animación
                this.classList.add('active');
                this.style.transform = 'translateX(5px)';
                this.style.transition = 'all 0.3s ease';
                
                // Animación de contenido si hay secciones específicas
                animateContentSection(this.getAttribute('data-section'));
            });
        });
    }
      // Validación del formulario de cambio de contraseña con animaciones
    const passwordForm = document.querySelector('.settings-section:nth-child(2)');
    const saveButton = document.querySelector('.save-button');
    
    if (saveButton && passwordForm) {
        // Añadir animación al botón de guardar
        saveButton.classList.add('animate-hover');
        
        saveButton.addEventListener('click', function() {
            // Animación de carga en el botón
            this.style.transform = 'scale(0.95)';
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            let isValid = true;
            
            // Limpiar errores anteriores con animación de salida
            const errorElements = document.querySelectorAll('.password-error');
            errorElements.forEach(el => {
                el.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => el.remove(), 300);
            });
            
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
              // Si todo es válido, mostrar mensaje de éxito con animación
            if (isValid) {
                setTimeout(() => {
                    // Restaurar el botón
                    this.style.transform = 'scale(1)';
                    this.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                    
                    // Mostrar notificación de éxito animada
                    showSuccessAlert('Los cambios han sido guardados correctamente');
                    
                    // Limpiar campos de contraseña con animación
                    [currentPassword, newPassword, confirmPassword].forEach(field => {
                        field.style.transition = 'all 0.3s ease';
                        field.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            field.value = '';
                            field.style.transform = 'scale(1)';
                        }, 150);
                    });
                }, 1000);
            } else {
                // Restaurar botón si hay errores
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                    this.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                }, 500);
            }
        });
    }
      // Zona de peligro - Desactivar cuenta con animaciones
    const dangerButton = document.querySelector('.danger-button');
    if (dangerButton) {
        // Añadir animación de pulso al botón de peligro
        dangerButton.classList.add('animate-pulse');
        
        dangerButton.addEventListener('click', function() {
            // Animación de advertencia
            this.style.animation = 'shake 0.5s ease';
            
            const confirmation = confirm('¿Está seguro que desea desactivar su cuenta? Esta acción no se puede deshacer.');
            if (confirmation) {
                showWarningAlert('Esta es una versión de demostración. En un sistema real, su cuenta sería desactivada.');
            }
            
            // Remover animación después de un tiempo
            setTimeout(() => {
                this.style.animation = '';
            }, 500);
        });
    }
    
    // Botón cancelar con animaciones
    const cancelButton = document.querySelector('.cancel-button');
    if (cancelButton) {
        cancelButton.classList.add('animate-hover');
        
        cancelButton.addEventListener('click', function() {
            // Animación del botón
            this.style.transform = 'scale(0.95)';
            
            // Resetear valores de los campos a los valores originales con animación
            const fullnameField = document.getElementById('fullname');
            const emailField = document.getElementById('email');
            
            const fieldsToReset = [
                { field: fullnameField, value: 'Emiliano Ledesma' },
                { field: emailField, value: 'practicas25.dppee@gmail.com' }
            ];
            
            fieldsToReset.forEach(({ field, value }, index) => {
                if (field) {
                    setTimeout(() => {
                        field.style.transition = 'all 0.3s ease';
                        field.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            field.value = value;
                            field.style.transform = 'scale(1)';
                        }, 150);
                    }, index * 100);
                }
            });
            
            // Limpiar campos de contraseña con animación
            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach((field, index) => {
                setTimeout(() => {
                    field.style.transition = 'all 0.3s ease';
                    field.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        field.value = '';
                        field.style.transform = 'scale(1)';
                    }, 150);
                }, index * 50);
            });
            
            setTimeout(() => {
                this.style.transform = 'scale(1)';
                showInfoAlert('Se han descartado los cambios');
            }, 800);
        });
    }
});

/**
 * Muestra un mensaje de error debajo de un campo de formulario con animación
 * @param {HTMLElement} element - El elemento de entrada
 * @param {string} message - El mensaje de error a mostrar
 */
function displayError(element, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'password-error alert-error';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '5px';
    errorDiv.style.opacity = '0';
    errorDiv.style.transform = 'translateY(-10px)';
    errorDiv.style.transition = 'all 0.3s ease';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    element.parentNode.appendChild(errorDiv);
    element.style.borderColor = '#dc3545';
    element.style.transition = 'border-color 0.3s ease';
    
    // Animar entrada del error
    setTimeout(() => {
        errorDiv.style.opacity = '1';
        errorDiv.style.transform = 'translateY(0)';
    }, 50);
}

/**
 * Inicializa las animaciones específicas para la página de configuración
 */
function initSettingsAnimations() {
    // Animar elementos principales con retrasos escalonados
    const settingsContent = document.querySelector('.settings-content');
    const settingsNav = document.querySelector('.settings-nav');
    const settingsSections = document.querySelectorAll('.settings-section');
    
    if (settingsNav) {
        settingsNav.classList.add('animate-left');
        settingsNav.style.animationDelay = '100ms';
    }
    
    if (settingsContent) {
        settingsContent.classList.add('animate-right');
        settingsContent.style.animationDelay = '200ms';
    }
    
    // Animar secciones de configuración
    settingsSections.forEach((section, index) => {
        section.classList.add('animate-fade');
        section.style.animationDelay = `${300 + (index * 100)}ms`;
    });
    
    // Animar campos de formulario
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach((control, index) => {
        control.classList.add('animate-hover');
        control.addEventListener('focus', function() {
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 0 0 3px rgba(0, 73, 144, 0.1)';
        });
        control.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = '';
        });
    });
    
    // Animar botones
    const buttons = document.querySelectorAll('.save-button, .cancel-button, .danger-button');
    buttons.forEach(button => {
        button.classList.add('animate-hover');
    });
}

/**
 * Anima el contenido de una sección específica
 * @param {string} sectionName - Nombre de la sección a animar
 */
function animateContentSection(sectionName) {
    const sections = document.querySelectorAll('.settings-section');
    sections.forEach(section => {
        section.style.animation = 'fadeIn 0.4s ease';
    });
}

/**
 * Muestra una alerta de éxito animada
 * @param {string} message - Mensaje a mostrar
 */
function showSuccessAlert(message) {
    const alert = createAlert(message, 'success');
    document.body.appendChild(alert);
    animateAlert(alert);
}

/**
 * Muestra una alerta de advertencia animada
 * @param {string} message - Mensaje a mostrar
 */
function showWarningAlert(message) {
    const alert = createAlert(message, 'warning');
    document.body.appendChild(alert);
    animateAlert(alert);
}

/**
 * Muestra una alerta de información animada
 * @param {string} message - Mensaje a mostrar
 */
function showInfoAlert(message) {
    const alert = createAlert(message, 'info');
    document.body.appendChild(alert);
    animateAlert(alert);
}

/**
 * Crea un elemento de alerta
 * @param {string} message - Mensaje de la alerta
 * @param {string} type - Tipo de alerta (success, warning, info, error)
 * @returns {HTMLElement} - Elemento de alerta creado
 */
function createAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `settings-alert alert-${type}`;
    
    const icons = {
        success: 'fas fa-check-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle',
        error: 'fas fa-times-circle'
    };
    
    const colors = {
        success: '#d4edda',
        warning: '#fff3cd',
        info: '#cce7ff',
        error: '#f8d7da'
    };
    
    const borderColors = {
        success: '#c3e6cb',
        warning: '#ffeaa7',
        info: '#b3d9ff',
        error: '#f5c6cb'
    };
    
    const textColors = {
        success: '#155724',
        warning: '#856404',
        info: '#0c5460',
        error: '#721c24'
    };
    
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, ${colors[type]} 0%, ${borderColors[type]} 100%);
        color: ${textColors[type]};
        border: 1px solid ${borderColors[type]};
        border-radius: 8px;
        padding: 15px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        z-index: 9999;
        max-width: 400px;
        opacity: 0;
        transform: translateX(100%);
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    
    alert.innerHTML = `
        <i class="${icons[type]}"></i>
        <span>${message}</span>
        <button onclick="removeAlert(this.parentElement)" style="
            background: none;
            border: none;
            color: ${textColors[type]};
            font-size: 18px;
            cursor: pointer;
            margin-left: auto;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    return alert;
}

/**
 * Anima la entrada y salida de una alerta
 * @param {HTMLElement} alert - Elemento de alerta
 */
function animateAlert(alert) {
    // Animar entrada
    setTimeout(() => {
        alert.style.opacity = '1';
        alert.style.transform = 'translateX(0)';
    }, 50);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        removeAlert(alert);
    }, 5000);
}

/**
 * Remueve una alerta con animación
 * @param {HTMLElement} alert - Elemento de alerta a remover
 */
function removeAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateX(100%)';
    setTimeout(() => {
        if (alert.parentElement) {
            alert.parentElement.removeChild(alert);
        }
    }, 300);
}
