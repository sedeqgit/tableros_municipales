/**
 * Script para la página de inicio de sesión
 */
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');
    const loginError = document.getElementById('loginError');
    const loginButton = document.querySelector('.login-button');
    const togglePassword = document.getElementById('togglePassword');
    
    // Toggle para mostrar/ocultar contraseña
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir comportamiento por defecto
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar el ícono
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Función para mostrar mensajes de error
    function showError(inputElement, errorElement, message) {
        if (inputElement) {
            inputElement.classList.add('form-error');
            inputElement.classList.add('shake-animation');
            setTimeout(() => {
                inputElement.classList.remove('shake-animation');
            }, 500);
        }
        
        if (errorElement) {
            errorElement.classList.add('show');
            const textNode = errorElement.querySelector('i').nextSibling;
            if (textNode) {
                textNode.textContent = ' ' + message;
            }
        }
    }
    
    // Función para ocultar mensajes de error
    function hideError(inputElement, errorElement) {
        if (inputElement) {
            inputElement.classList.remove('form-error');
        }
        
        if (errorElement) {
            errorElement.classList.remove('show');
        }
    }
    
    // Validación en tiempo real del usuario
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            hideError(usernameInput, usernameError);
            hideError(null, loginError); // Ocultar error general
        });
    }
    
    // Validación en tiempo real de la contraseña
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            hideError(passwordInput, passwordError);
            hideError(null, loginError); // Ocultar error general
        });
    }
    
    // Manejo del envío del formulario
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = usernameInput ? usernameInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value.trim() : '';
            let isValid = true;
            
            // Ocultar todos los errores antes de validar
            hideError(usernameInput, usernameError);
            hideError(passwordInput, passwordError);
            hideError(null, loginError);
            
            // Validar usuario
            if (!username) {
                showError(usernameInput, usernameError, 'Por favor ingrese su nombre de usuario');
                isValid = false;
            }
            
            // Validar contraseña
            if (!password) {
                showError(passwordInput, passwordError, 'Por favor ingrese su contraseña');
                isValid = false;
            }
            
            if (isValid) {
                // Verificar credenciales temporales
                if (username === 'practicas25.dppee@gmail.com' && password === 'Balluff254') {
                    // Simulación de carga
                    loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
                    loginButton.disabled = true;
                    loginButton.classList.add('btn-disabled');
                    
                    // Delay para dar realismo al proceso de autenticación
                    setTimeout(() => {
                        // Crear la sesión en el servidor para que funcione en todo el sistema
                        const formData = new FormData(loginForm);
                        
                        fetch('process_login.php', {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Sesión creada exitosamente, redirigir
                                window.location.href = 'home.php';
                            } else {
                                // Si por alguna razón falla, redirigir de todos modos
                                window.location.href = 'home.php';
                            }
                        })
                        .catch(error => {
                            // Si hay error en la petición, redirigir de todos modos
                            console.log('Error creando sesión, pero redirigiendo:', error);
                            window.location.href = 'home.php';
                        });
                    }, 1500); // Delay de 1.5 segundos para dar realismo
                } else {
                    // Mostrar error si las credenciales no son válidas
                    loginError.classList.add('show');
                    loginForm.classList.add('shake-animation');
                    
                    setTimeout(() => {
                        loginForm.classList.remove('shake-animation');
                    }, 500);
                }
            }
        });
    }
    
    // Inicialización: Establecer valores predeterminados
    if (usernameInput) {
        usernameInput.focus(); // Poner el foco en el campo de usuario al cargar
    }

    // Asegúrate de que tu login.js incluya la verificación del CAPTCHA
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Limpiar mensajes de error previos
    document.getElementById('usernameError').style.display = 'none';
    document.getElementById('passwordError').style.display = 'none';
    document.getElementById('loginError').style.display = 'none';
    document.getElementById('captchaError').style.display = 'none';
    
    const formData = new FormData(this);
    
    fetch('process_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            if (data.type === 'captcha') {
                document.getElementById('captchaError').style.display = 'block';
                document.getElementById('captchaError').innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
            } else {
                document.getElementById('loginError').style.display = 'block';
                document.getElementById('loginError').innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
            }
            // Resetear el reCAPTCHA
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loginError').style.display = 'block';
    });
});
});
