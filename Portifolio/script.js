// Configuração do IntersectionObserver para Scroll Reveal
document.addEventListener("DOMContentLoaded", () => {
    // Seleciona todos os elementos com a classe .reveal
    const reveals = document.querySelectorAll(".reveal");

    // Opções do Observer: ativa quando 10% do elemento estiver visível na tela
    const observerOptions = {
        root: null,
        rootMargin: "0px",
        threshold: 0.1
    };

    // Callback executado quando os elementos entram na view
    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Adiciona a classe active para disparar a animação do CSS
                entry.target.classList.add("active");
                // Depois de revelado, para de observar o elemento
                observer.unobserve(entry.target);
            }
        
    // Fechar menu mobile ao clicar em um link
    const menuLinks = document.querySelectorAll('.menu a');
    const menuToggle = document.getElementById('menu-toggle');
    
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (menuToggle.checked) {
                menuToggle.checked = false;
            }
        });
    });
});

    };

    // Inicializa o Observer
    const revealObserver = new IntersectionObserver(revealCallback, observerOptions);

    // Aplica o observer em todos os elementos .reveal
    reveals.forEach(reveal => {
        revealObserver.observe(reveal);
    
    // Fechar menu mobile ao clicar em um link
    const menuLinks = document.querySelectorAll('.menu a');
    const menuToggle = document.getElementById('menu-toggle');
    
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (menuToggle.checked) {
                menuToggle.checked = false;
            }
        });
    });
});


    // Fechar menu mobile ao clicar em um link
    const menuLinks = document.querySelectorAll('.menu a');
    const menuToggle = document.getElementById('menu-toggle');
    
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (menuToggle.checked) {
                menuToggle.checked = false;
            }
        });
    });
});

