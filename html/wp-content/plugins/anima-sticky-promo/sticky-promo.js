jQuery(document).ready(function ($) {
    var promoElement = $('#anima-promo-overlay');
    var closeButton = promoElement.find('.promo-close-btn');
    var storageKey = 'anima_metahuman_promo_closed';

    // 1. Verificar el estado de persistencia
    if (localStorage.getItem(storageKey) === 'true') {
        promoElement.hide();
        return; // Detener la ejecución si ya fue cerrado
    } else {
        promoElement.show();
    }

    // 2. Lógica de Cierre
    closeButton.on('click', function (e) {
        e.preventDefault();

        // Esconder el overlay
        promoElement.fadeOut(300, function () {
            // Guardar el estado en el almacenamiento local para que no vuelva a aparecer
            localStorage.setItem(storageKey, 'true');
        });
    });

    // OPCIONAL: Lógica para mostrar la promoción de nuevo al pasar un tiempo (ej: 7 días)
    // Para simplificar, lo dejamos cerrado permanentemente a menos que se borre el localStorage.
});