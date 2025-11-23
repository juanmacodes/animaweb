jQuery(document).ready(function ($) {
    $('.anima-ai-send').on('click', function () {
        var slug = $(this).data('target');
        var input = $('.anima-ai-input[data-target="' + slug + '"]');
        var message = input.val().trim();
        var chatContainer = $('#chat-messages-' + slug);

        if (message === '') return;

        // Append User Message
        chatContainer.append('<div class="message user-message"><p>' + message + '</p></div>');
        input.val('');
        chatContainer.scrollTop(chatContainer[0].scrollHeight);

        // Show Loading
        var loadingId = 'loading-' + Date.now();
        chatContainer.append('<div class="message ai-message loading" id="' + loadingId + '"><p>...</p></div>');
        chatContainer.scrollTop(chatContainer[0].scrollHeight);

        $.ajax({
            url: anima_ai_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'anima_ai_chat',
                nonce: anima_ai_vars.nonce,
                assistant_id: slug,
                message: message
            },
            success: function (response) {
                $('#' + loadingId).remove();
                if (response.success) {
                    chatContainer.append('<div class="message ai-message"><p>' + response.data.reply + '</p></div>');
                } else {
                    chatContainer.append('<div class="message ai-message error"><p>Error: ' + response.data + '</p></div>');
                }
                chatContainer.scrollTop(chatContainer[0].scrollHeight);
            },
            error: function () {
                $('#' + loadingId).remove();
                chatContainer.append('<div class="message ai-message error"><p>Error de conexión.</p></div>');
            }
        });
    });

    // Enter key support
    $('.anima-ai-input').on('keypress', function (e) {
        if (e.which === 13) {
            var slug = $(this).data('target');
            $('.anima-ai-send[data-target="' + slug + '"]').click();
        }
    });
});
