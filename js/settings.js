/**
 * Script específico para la página de configuración
 * Sistema de Estadística Educativa - SEDEQ
 * 
 * NOTA: Este script complementa la funcionalidad base de home.js
 * No reemplaza el comportamiento de home.js, sino que añade funcionalidades específicas
 * para la página de configuración
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Sistema de Notificaciones
    class NotificationSystem {
        constructor() {
            this.container = document.getElementById('notification-container');
            this.notifications = [];
        }

        createNotification(type, title, message, actions = []) {
            const notificationId = 'notification-' + Date.now();
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.id = notificationId;

            let actionsHTML = '';
            if (actions.length > 0) {
                const actionButtons = actions.map(action => 
                    `<button class="notification-btn notification-btn-${action.type}" data-action="${action.action}">${action.text}</button>`
                ).join('');
                actionsHTML = `<div class="notification-actions">${actionButtons}</div>`;
            }

            notification.innerHTML = `
                <div class="notification-header">
                    <h4 class="notification-title">
                        <i class="fas ${this.getIcon(type)}"></i>
                        ${title}
                    </h4>
                    <button class="notification-close" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="notification-body">${message}</div>
                ${actionsHTML}
            `;

            // Agregar eventos
            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => this.hideNotification(notificationId));

            // Agregar eventos a botones de acción
            const actionButtons = notification.querySelectorAll('[data-action]');
            actionButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const action = e.target.getAttribute('data-action');
                    this.handleAction(action, notificationId);
                });
            });

            this.container.appendChild(notification);
            this.notifications.push(notificationId);

            // Mostrar la notificación
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);

            // Auto-ocultar después de 5 segundos (excepto confirmaciones)
            if (actions.length === 0) {
                setTimeout(() => {
                    this.hideNotification(notificationId);
                }, 5000);
            }

            return notificationId;
        }

        getIcon(type) {
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-triangle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            return icons[type] || 'fa-info-circle';
        }

        hideNotification(notificationId) {
            const notification = document.getElementById(notificationId);
            if (notification) {
                notification.classList.remove('show');
                notification.classList.add('hide');
                
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                    this.notifications = this.notifications.filter(id => id !== notificationId);
                }, 300);
            }
        }

        handleAction(action, notificationId) {
            switch (action) {
                case 'confirm-deactivate':
                    this.hideNotification(notificationId);
                    this.showSuccess('Cuenta Desactivada', 'Su cuenta ha sido desactivada correctamente.');
                    break;
                case 'cancel':
                    this.hideNotification(notificationId);
                    break;
                default:
                    this.hideNotification(notificationId);
            }
        }

        showSuccess(title, message) {
            return this.createNotification('success', title, message);
        }

        showError(title, message) {
            return this.createNotification('error', title, message);
        }

        showWarning(title, message) {
            return this.createNotification('warning', title, message);
        }

        showInfo(title, message) {
            return this.createNotification('info', title, message);
        }

        showConfirmation(title, message, confirmText = 'Confirmar', cancelText = 'Cancelar', confirmAction = 'confirm') {
            const actions = [
                { type: 'secondary', text: cancelText, action: 'cancel' },
                { type: 'danger', text: confirmText, action: confirmAction }
            ];
            return this.createNotification('warning', title, message, actions);
        }
    }

    // Inicializar el sistema de notificaciones
    const notifications = new NotificationSystem();

    // Función auxiliar para limpiar errores previos
    function clearErrors() {
        const existingErrors = document.querySelectorAll('.password-error');
        existingErrors.forEach(error => error.remove());
        
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.style.borderColor = '';
        });
    }

    // Función para mostrar error en un campo específico
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'password-error';
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.style.marginTop = '5px';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            field.parentNode.appendChild(errorDiv);
            field.style.borderColor = '#dc3545';
            field.focus();
        }
    }

    // Manejo del botón Guardar Cambios
    const saveButton = document.querySelector('.save-button');
    if (saveButton) {
        saveButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            clearErrors();
            
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            let isValid = true;
            
            // Validar contraseñas si se han introducido
            if (newPassword && confirmPassword && (newPassword.value || confirmPassword.value)) {
                if (!currentPassword.value) {
                    showFieldError('current_password', 'Debe introducir su contraseña actual');
                    isValid = false;
                } else if (newPassword.value !== confirmPassword.value) {
                    showFieldError('confirm_password', 'Las contraseñas no coinciden');
                    notifications.showError('Error de Validación', 'Las contraseñas introducidas no coinciden. Por favor, verifique e intente nuevamente.');
                    isValid = false;
                } else if (newPassword.value.length < 8) {
                    showFieldError('new_password', 'La contraseña debe tener al menos 8 caracteres');
                    isValid = false;
                }
            }
            
            if (isValid) {
                // Limpiar campos de contraseña
                if (currentPassword) currentPassword.value = '';
                if (newPassword) newPassword.value = '';
                if (confirmPassword) confirmPassword.value = '';
                
                notifications.showSuccess('Cambios Guardados', 'Los cambios en su configuración han sido guardados correctamente.');
            }
        });
    }

    // Manejo del botón Cancelar
    const cancelButton = document.querySelector('.cancel-button');
    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            clearErrors();
            
            // Resetear campos a valores originales
            const fullnameField = document.getElementById('fullname');
            const emailField = document.getElementById('email');
            
            if (fullnameField) fullnameField.value = fullnameField.getAttribute('value') || '';
            if (emailField) emailField.value = emailField.getAttribute('value') || '';
            
            // Limpiar campos de contraseña
            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => field.value = '');
            
            notifications.showInfo('Cambios Cancelados', 'Se han descartado todos los cambios realizados.');
        });
    }

    // Manejo del botón de Desactivar Cuenta
    const dangerButton = document.querySelector('.danger-button');
    if (dangerButton) {
        dangerButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            notifications.showConfirmation(
                'Confirmar Desactivación',
                '¿Está seguro que desea desactivar su cuenta? Esta acción no se puede deshacer y perderá acceso a todas las funcionalidades del sistema.',
                'Desactivar Cuenta',
                'Cancelar',
                'confirm-deactivate'
            );
        });
    }
});
