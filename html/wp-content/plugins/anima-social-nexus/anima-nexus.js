jQuery(document).ready(function ($) {
    /**
     * =================================================================
     * ANIMA SOCIAL NEXUS: INTERFAZ NEURONAL (Frontend JS)
     * =================================================================
     */

    // --- A) MANEJAR EL CLIC EN "ESTABLECER ENLACE" (Enviar solicitud) ---

    // Detectamos clic en cualquier botón con la clase '.anima-nexus-connect-btn'
    // Usamos 'on' en 'body' para que funcione incluso con contenido cargado dinámicamente (Ajax)
    $('body').on('click', '.anima-nexus-connect-btn', function (e) {
        e.preventDefault();
        var $button = $(this);
        var recipientId = $button.data('recipient-id'); // Obtenemos el ID del usuario destino del atributo data-

        // Evitar doble clic si ya se está procesando
        if ($button.hasClass('processing') || $button.hasClass('disabled')) return;

        // Efecto visual inmediato: estado de carga
        $button.addClass('processing').prop('disabled', true);
        var originalText = $button.html();
        $button.html('<span class="nexus-loading-glitch">Transmitiendo...</span>');

        // Enviar petición AJAX
        $.ajax({
            url: anima_nexus_vars.ajax_url, // URL de admin-ajax.php (pasada desde PHP)
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'anima_nexus_send_request', // El nombre del manejador PHP (wp_ajax_...)
                recipient_id: recipientId,
                nonce: anima_nexus_vars.nonce     // Token de seguridad
            },
            success: function (response) {
                $button.removeClass('processing');
                if (response.success) {
                    // Éxito: Cambiar el botón a estado "Pendiente"
                    $button.removeClass('anima-nexus-connect-btn').addClass('anima-nexus-pending-btn disabled');
                    $button.html('<span class="dashicons dashicons-hourglass"></span> Señal en Espera');
                    // Opcional: Mostrar un mensaje toast/notificación rápida
                    // showNexusNotification(response.data.message, 'success');
                } else {
                    // Error lógico (ej. ya existe): Restaurar botón y mostrar error
                    $button.html(originalText).prop('disabled', false);
                    alert('Error del Nexus: ' + response.data.message);
                }
            },
            error: function () {
                // Error de servidor/red
                $button.removeClass('processing').html(originalText).prop('disabled', false);
                alert('Fallo crítico en la red. Inténtalo de nuevo.');
            }
        });
    });


    // --- B) MANEJAR CLICS EN "ACEPTAR" / "BLOQUEAR" (Responder solicitud) ---

    // Estos botones estarán en la lista de notificaciones.
    // Clases: .nexus-accept-btn y .nexus-block-btn
    $('body').on('click', '.nexus-accept-btn, .nexus-block-btn', function (e) {
        e.preventDefault();
        var $button = $(this);
        var $card = $button.closest('.nexus-request-card'); // La tarjeta contenedora
        var requesterId = $card.data('requester-id');
        // Determinamos la acción basada en la clase del botón pulsado
        var action = $button.hasClass('nexus-accept-btn') ? 'accept' : 'block';

        if ($card.hasClass('processing')) return;
        $card.addClass('processing');

        $.ajax({
            url: anima_nexus_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'anima_nexus_respond_request',
                requester_id: requesterId,
                nexus_action: action, // 'accept' o 'block'
                nonce: anima_nexus_vars.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Éxito: Animación de salida de la tarjeta
                    if (action === 'accept') {
                        $card.addClass('nexus-accepted-anim').fadeOut(600, function () { $(this).remove(); });
                        // showNexusNotification('Enlace neuronal establecido.', 'success');
                    } else {
                        $card.addClass('nexus-blocked-anim').fadeOut(600, function () { $(this).remove(); });
                        // showNexusNotification('Señal bloqueada.', 'warning');
                    }
                    // Actualizar contador de notificaciones si existe
                    updateNotificationCounter(-1);
                } else {
                    $card.removeClass('processing');
                    alert('Error: ' + response.data.message);
                }
            },
            error: function () {
                $card.removeClass('processing');
                alert('Fallo de comunicación.');
            }
        });
    });

    // Función auxiliar para el contador de notificaciones (simple implementación)
    function updateNotificationCounter(change) {
        var $counter = $('.nexus-notification-badge');
        if ($counter.length) {
            var currentCount = parseInt($counter.text()) || 0;
            var newCount = Math.max(0, currentCount + change);
            if (newCount > 0) {
                $counter.text(newCount).show();
            } else {
                $counter.hide();
            }
        }
    }
});