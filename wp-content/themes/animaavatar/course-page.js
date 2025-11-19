// course-page.js
document.addEventListener('DOMContentLoaded', function () {
    const accordionHeaders = document.querySelectorAll('.course-accordion-item .accordion-header');

    accordionHeaders.forEach(header => {
        // Solo añadir el evento de clic si no está bloqueado
        if (!header.classList.contains('locked-header')) {
            header.addEventListener('click', function () {
                const item = this.closest('.course-accordion-item');
                const content = item.querySelector('.accordion-content');
                const toggleIcon = this.querySelector('.toggle-icon');

                item.classList.toggle('active');
                if (item.classList.contains('active')) {
                    content.style.maxHeight = content.scrollHeight + 'px';
                    if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)';
                } else {
                    content.style.maxHeight = '0';
                    if (toggleIcon) toggleIcon.style.transform = 'rotate(0deg)';
                }
            });
        }
    });

    // Asegurarse de que el video se cargue y reproduzca correctamente después de expandirse
    document.querySelectorAll('.anima-lesson-video').forEach(video => {
        video.addEventListener('loadedmetadata', function () {
            // No hacer nada especial, solo asegurar que cargue
        });
    });
});