// Interações Leves & Acessibilidade — Ademar Teisen Portfólio
document.addEventListener("DOMContentLoaded", () => {
    // 1. Controle do Menu Mobile
    const menuLinks = document.querySelectorAll('.menu a');
    const menuToggle = document.getElementById('menu-toggle');

    if (menuToggle && menuLinks.length) {
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (menuToggle.checked) {
                    menuToggle.checked = false;
                }
            });
        });
    }

    // 2. Navbar State ao Rolar a Página
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const handleScroll = () => {
            if (window.scrollY > 30) {
                navbar.style.background = 'rgba(10, 14, 23, 0.96)';
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.35)';
            } else {
                navbar.style.background = 'rgba(10, 14, 23, 0.88)';
                navbar.style.boxShadow = 'none';
            }
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }
});
