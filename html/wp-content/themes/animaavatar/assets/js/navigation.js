(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        console.log("Anima JS: Iniciado"); // CHIVATO 1

        /* =========================================
           MENÚ MÓVIL & TOGGLE (VERSIÓN ROBUSTA)
           ========================================= */

        // Usamos querySelectorAll por si hay más de un botón (uno oculto y otro visible)
        var menuToggles = document.querySelectorAll('.menu-toggle');
        var mainNavigation = document.querySelector('.main-navigation');
        var body = document.body;

        // Verificación de existencia para evitar errores
        if (menuToggles.length > 0 && mainNavigation) {
            console.log("Anima JS: Botones encontrados: " + menuToggles.length); // CHIVATO 2

            // Añadimos el evento a TODOS los botones .menu-toggle que existan
            menuToggles.forEach(function (toggle) {
                toggle.addEventListener('click', function (e) {
                    e.preventDefault(); // Evita saltos raros
                    console.log("Anima JS: Click detectado"); // CHIVATO 3

                    var isExpanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !isExpanded);

                    // Añadir clase al menú y al body
                    mainNavigation.classList.toggle('toggled');
                    body.classList.toggle('no-scroll');
                });
            });
        } else {
            console.error("Anima JS Error: No se encuentra .menu-toggle o .main-navigation");
        }

        /* =========================================
           SUBMENÚS MÓVIL (ACORDEÓN)
           ========================================= */
        var parentItems = document.querySelectorAll('.menu-item-has-children > a');

        parentItems.forEach(function (link) {
            link.addEventListener('click', function (e) {
                // Solo ejecutar en móvil
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    var parent = this.parentElement;

                    // Toggle clase para flechita
                    parent.classList.toggle('toggled-submenu-active');

                    // Toggle altura del submenú
                    var subMenu = parent.querySelector('.sub-menu');
                    if (subMenu) {
                        if (subMenu.style.maxHeight) {
                            subMenu.style.maxHeight = null; // Cerrar
                        } else {
                            subMenu.style.maxHeight = subMenu.scrollHeight + "px"; // Abrir
                        }
                    }
                }
            });
        });

        /* =========================================
           CERRAR AL HACER CLICK FUERA
           ========================================= */
        document.addEventListener('click', function (event) {
            // Si el menú está abierto
            if (mainNavigation && menuToggles.length > 0 && mainNavigation.classList.contains('toggled')) {
                var clickedOnToggle = false;
                menuToggles.forEach(function (t) {
                    if (t.contains(event.target)) clickedOnToggle = true;
                });

                if (!mainNavigation.contains(event.target) && !clickedOnToggle) {
                    mainNavigation.classList.remove('toggled');
                    body.classList.remove('no-scroll');
                    menuToggles.forEach(function (t) {
                        t.setAttribute('aria-expanded', 'false');
                    });
                }
            }
        });

        /* =========================================
           RESETEAR AL CAMBIAR TAMAÑO (RESIZE)
           ========================================= */
        window.addEventListener('resize', function () {
            if (window.innerWidth > 992) {
                if (mainNavigation && mainNavigation.classList.contains('toggled')) {
                    mainNavigation.classList.remove('toggled');
                    body.classList.remove('no-scroll');
                    menuToggles.forEach(function (t) {
                        t.setAttribute('aria-expanded', 'false');
                    });
                }
                // Limpiar estilos inline de submenús
                document.querySelectorAll('.sub-menu').forEach(function (sm) {
                    sm.style.maxHeight = '';
                });
            }
        });

        /* =========================================
           ACORDEÓN CURSO (TEMARIO) - AÑADIDO
           ========================================= */
        const moduleTitles = document.querySelectorAll('.module-title');

        moduleTitles.forEach(title => {
            // Navegación segura hacia los elementos
            const parent = title.closest('.module-item');
            const lessons = parent ? parent.querySelector('.module-lessons') : null;

            if (!parent || !lessons) return;

            // Set initial state (CSS debe tener transition y overflow: hidden)
            lessons.style.maxHeight = '0';
            lessons.style.opacity = 0;

            title.addEventListener('click', function () {

                const isActive = parent.classList.contains('active');

                // 1. Cierra todos los demás para un mejor UX
                document.querySelectorAll('.module-item.active').forEach(item => {
                    if (item !== parent) {
                        item.classList.remove('active');
                        item.querySelector('.module-lessons').style.maxHeight = '0';
                        item.querySelector('.module-lessons').style.opacity = 0;
                    }
                });

                // 2. Aplica el estado al módulo clickeado
                if (isActive) {
                    parent.classList.remove('active');
                    lessons.style.maxHeight = '0';
                    lessons.style.opacity = 0;
                } else {
                    parent.classList.add('active');
                    // Abrir calculando la altura del contenido
                    lessons.style.maxHeight = lessons.scrollHeight + "px";
                    lessons.style.opacity = 1;
                }
            });
        });


        /* =========================================
           MODAL VISOR 3D (Tu código original intacto)
           ========================================= */
        const glbModal = document.getElementById('anima-glb-modal');
        const modelViewer = document.getElementById('anima-model-viewer');
        const modalTitle = document.getElementById('anima-glb-modal-title');

        if (glbModal && modelViewer) {
            const closeModal = () => {
                glbModal.classList.remove('is-open');
                body.style.overflow = '';
                setTimeout(() => {
                    modelViewer.src = '';
                    if (modalTitle) modalTitle.textContent = '';
                }, 300);
            };

            document.querySelectorAll('.js-open-glb-viewer').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const glbUrl = this.dataset.glbUrl;
                    const assetTitle = this.dataset.title;

                    if (glbUrl) {
                        modelViewer.src = glbUrl;
                        if (modalTitle) modalTitle.textContent = assetTitle;
                        glbModal.classList.add('is-open');
                        body.style.overflow = 'hidden';
                    }
                });
            });

            document.querySelectorAll('.js-close-modal').forEach(btn => btn.addEventListener('click', closeModal));

            glbModal.addEventListener('click', function (e) {
                if (e.target === glbModal) closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && glbModal.classList.contains('is-open')) closeModal();
            });
        }

    });
})();