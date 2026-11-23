// Lógica para el botón de Modo Oscuro / Claro
document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    
    if (themeToggle) {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        actualizarIcono(currentTheme);

        themeToggle.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-bs-theme');
            theme = theme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            actualizarIcono(theme);
        });

        function actualizarIcono(theme) {
            themeToggle.innerHTML = theme === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
        }
    }
});