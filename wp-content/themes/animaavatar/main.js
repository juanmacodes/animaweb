(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* =========================================
           MENÚ MÓVIL & MEGA MENÚ
           ========================================= */
        var menuToggle = document.querySelector('.menu-toggle');
        var mainNavigation = document.querySelector('.main-navigation');
        var siteHeader = document.querySelector('.site-header');
        var body = document.body;

        // Toggle del Menú Móvil
        if (menuToggle && mainNavigation) {
            menuToggle.addEventListener('click', function () {
                var isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                mainNavigation.classList.toggle('toggled');
                body.classList.toggle('no-scroll'); // Bloquear scroll del fondo
            });

            // Cerrar menú al hacer clic fuera (en el fondo oscurecido si lo hubiera)
            document.addEventListener('click', function (event) {
                if (mainNavigation.classList.contains('toggled') &&
                    !mainNavigation.contains(event.target) &&
                    !menuToggle.contains(event.target)) {

                    mainNavigation.classList.remove('toggled');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    body.classList.remove('no-scroll');
                }
            });
        }

        // Submenús en Móvil (Acordeón)
        var parentItems = document.querySelectorAll('.menu-item-has-children > a');
        parentItems.forEach(function (link) {
            link.addEventListener('click', function (e) {
                // Solo actuar como toggle si estamos en vista móvil (menos de 992px)
                if (window.innerWidth <= 992) {
                    e.preventDefault(); // Evitar navegar al link padre
                    var parent = this.parentElement;
                    var subMenu = parent.querySelector('.sub-menu');

                    // Toggle clase para girar flechitas si las hubiera
                    parent.classList.toggle('toggled');

                    // Toggle visibilidad del submenú
                    if (subMenu) {
                        if (subMenu.style.display === 'block') {
                            subMenu.style.display = 'none';
                        } else {
                            subMenu.style.display = 'block';
                        }
                    }
                }
            });
        });

        // Resetear estilos al cambiar tamaño de ventana a escritorio
        window.addEventListener('resize', function () {
            if (window.innerWidth > 992) {
                if (mainNavigation && mainNavigation.classList.contains('toggled')) {
                    mainNavigation.classList.remove('toggled');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    body.classList.remove('no-scroll');
                }
                // Restaurar submenús
                document.querySelectorAll('.sub-menu').forEach(function (sm) {
                    sm.style.display = ''; // Quitar estilos inline
                });
                document.querySelectorAll('.menu-item-has-children').forEach(function (li) {
                    li.classList.remove('toggled');
                });
            }
        });


        /* =========================================
           HEADER SCROLL (Efecto pegajoso)
           ========================================= */
        if (siteHeader) {
            var lastScroll = 0;
            var ticking = false;

            var handleHeaderScroll = function () {
                var current = window.pageYOffset || document.documentElement.scrollTop;

                // Añadir sombra/fondo al hacer scroll
                if (current > 10) {
                    siteHeader.classList.add('site-header--scrolled');
                } else {
                    siteHeader.classList.remove('site-header--scrolled');
                }

                // Opcional: Ocultar header al bajar y mostrar al subir (efecto inteligente)
                /*
                if (current > lastScroll && current > siteHeader.offsetHeight + 40) {
                    siteHeader.classList.add('header--hidden');
                } else if (current < lastScroll - 5) {
                    siteHeader.classList.remove('header--hidden');
                }
                */

                lastScroll = current <= 0 ? 0 : current;
                ticking = false;
            };

            window.addEventListener('scroll', function () {
                if (!ticking) {
                    window.requestAnimationFrame(handleHeaderScroll);
                    ticking = true;
                }
            });
        }


        /* =========================================
           ANIMACIONES AL HACER SCROLL
           ========================================= */
        var animatedItems = document.querySelectorAll('.animate-on-scroll');
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            animatedItems.forEach(function (item) {
                observer.observe(item);
            });
        } else {
            // Fallback para navegadores antiguos
            animatedItems.forEach(function (item) {
                item.classList.add('is-visible');
            });
        }


        /* =========================================
           SWIPER SLIDER (Si existe)
           ========================================= */
        if (typeof Swiper !== 'undefined' && document.querySelector('.swiper')) {
            new Swiper('.swiper', {
                loop: true,
                slidesPerView: 1,
                spaceBetween: 24,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false
                },
                breakpoints: {
                    768: { slidesPerView: 2 },
                    1024: { slidesPerView: 3 }
                }
            });
        }


        /* =========================================
           MODAL VISOR 3D (Para page-assets.php)
           ========================================= */
        // Este código maneja el modal que añadimos en la galería de assets
        const glbModal = document.getElementById('anima-glb-modal');
        const modelViewer = document.getElementById('anima-model-viewer');
        const modalTitle = document.getElementById('anima-glb-modal-title');

        if (glbModal && modelViewer) {
            // Abrir modal
            document.querySelectorAll('.js-open-glb-viewer').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const glbUrl = this.dataset.glbUrl;
                    const assetTitle = this.dataset.title;

                    if (glbUrl) {
                        modelViewer.src = glbUrl;
                        if (modalTitle) modalTitle.textContent = assetTitle;
                        glbModal.classList.add('is-open');
                        body.style.overflow = 'hidden'; // Bloquear scroll
                    }
                });
            });

            // Cerrar modal (botón X o clic fuera)
            const closeModal = () => {
                glbModal.classList.remove('is-open');
                body.style.overflow = ''; // Restaurar scroll
                // Pequeño delay para limpiar el src y no ver el parpadeo
                setTimeout(() => {
                    modelViewer.src = '';
                    if (modalTitle) modalTitle.textContent = '';
                }, 300);
            };

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