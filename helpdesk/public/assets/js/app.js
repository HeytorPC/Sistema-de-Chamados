/**
 * app.js - Comportamentos globais da interface (ES6, sem dependências).
 */
document.addEventListener('DOMContentLoaded', () => {
    // Fecha o menu lateral (mobile) ao clicar fora dele
    document.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.menu-toggle');
        if (!sidebar || !sidebar.classList.contains('open')) return;
        if (!sidebar.contains(e.target) && e.target !== toggle) {
            sidebar.classList.remove('open');
        }
    });

    // Auto-oculta alertas de sucesso/erro após alguns segundos
    document.querySelectorAll('.alert').forEach((alerta) => {
        setTimeout(() => {
            alerta.style.transition = 'opacity .5s ease';
            alerta.style.opacity = '0';
            setTimeout(() => alerta.remove(), 500);
        }, 5000);
    });
});
