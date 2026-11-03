// Cyberpunk Dark UI Interactive Scripts — Ademar Teisen Portfolio
document.addEventListener("DOMContentLoaded", () => {
    // 1. Mobile Menu Toggle & Auto Close
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

    // 2. IntersectionObserver para Revelação Suave de Elementos
    const animatedElements = document.querySelectorAll('.cyber-card, .spine-node-marker, .company-tile, .tech-icon-pill, .hero-chip, .cta-banner-box');
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
    });

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -40px 0px'
    });

    animatedElements.forEach(el => revealObserver.observe(el));

    // 3. Navbar Blur & Scrolled State
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 40) {
            navbar.style.background = 'rgba(5, 5, 12, 0.92)';
            navbar.style.borderBottomColor = 'rgba(139, 92, 246, 0.2)';
            navbar.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.5)';
        } else {
            navbar.style.background = 'rgba(5, 5, 12, 0.75)';
            navbar.style.borderBottomColor = 'rgba(255, 255, 255, 0.08)';
            navbar.style.boxShadow = 'none';
        }
    }, { passive: true });
});
