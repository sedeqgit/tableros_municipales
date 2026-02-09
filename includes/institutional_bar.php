<?php
/**
 * =============================================================================
 * BARRA INSTITUCIONAL SUPERIOR - COMPONENTE COMPARTIDO
 * =============================================================================
 * 
 * Este archivo contiene la barra institucional superior con enlaces y redes
 * sociales del Gobierno de Querétaro. Es compartido por todas las páginas
 * del sistema SEDEQ.
 * 
 * @version 1.0
 * @since 2026
 */
require_once __DIR__ . '/../headers/headers.php';
?>
<!-- ============================================================================ -->
<!-- BARRA SUPERIOR INSTITUCIONAL                                                -->
<!-- ============================================================================ -->
<div class="top-institutional-bar">
    <div class="institutional-bar-content">
        <!-- Enlaces institucionales importantes -->
        <div class="institutional-links">
            <a href="https://www.queretaro.gob.mx/transparencia" class="institutional-link">Portal Transparencia</a>
            <a href="https://portal.queretaro.gob.mx/prensa/" class="institutional-link">Portal Prensa</a>
            <a href="https://www.queretaro.gob.mx/covid19" class="institutional-link">COVID19</a>
        </div>

        <!-- Redes sociales y contacto -->
        <div class="social-links">
            <a href="https://wa.me/+524421443740" class="social-link social-link-chatbot" title="Chat">
                <img class="icon-sidebar" src="https://queretaro.gob.mx/o/queretaro-theme/images/chatboxLines.png">
                <span class="social-name">Chatbot</span>
            </a>
            <a href="https://www.facebook.com/educacionqro" target="_blank" class="social-link" title="Facebook">
                <i class="fab fa-facebook-f"></i>
                <span class="social-name">Facebook</span>
            </a>
            <a href="https://x.com/educacionqro" target="_blank" class="social-link" title="Twitter">
                <i class="fab fa-twitter"></i>
                <span class="social-name">Twitter</span>
            </a>
            <a href="https://www.instagram.com/educacionqueretaro?fbclid=IwZXh0bgNhZW0CMTAAYnJpZBExR09OOWJid2NZT2ZTbUJvRHNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR4yi6bwE_6iEuyyUdbWYkjRLv9zjFFWyxwABVKdZSunmMWOwOsHAv_dcFFBOw_aem_t72qtgoL72OI4Pzyj-oILw"
                target="_blank" class="social-link" title="Instagram">
                <i class="fab fa-instagram"></i>
                <span class="social-name">Instagram</span>
            </a>
            <a href="https://www.youtube.com/@SecretariadeEducacionGEQ" target="_blank" class="social-link"
                title="YouTube">
                <i class="fab fa-youtube"></i>
                <span class="social-name">YouTube</span>
            </a>
            <a href="tel:4422385000" class="social-link" title="Teléfono">
                <i class="fas fa-phone"></i>
                <span class="social-name">442 238 5000</span>
            </a>
        </div>
    </div>
</div>