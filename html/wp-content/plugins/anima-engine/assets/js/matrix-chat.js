jQuery(document).ready(function ($) {
    const chatCore = $('#matrix-chat-core');
    const messagesContainer = $('#matrix-messages');
    const input = $('#matrix-input');
    const sendBtn = $('#matrix-send');

    let lastMsgId = null;
    let isCollapsed = localStorage.getItem('anima_chat_collapsed') === 'true';

    // Init State
    if (isCollapsed) {
        chatCore.addClass('matrix-chat-collapsed');
    } else {
        chatCore.removeClass('matrix-chat-collapsed');
    }

    // Toggle UI
    window.toggleMatrixChat = function () {
        chatCore.toggleClass('matrix-chat-collapsed');
        const collapsed = chatCore.hasClass('matrix-chat-collapsed');
        localStorage.setItem('anima_chat_collapsed', collapsed);

        if (!collapsed) {
            scrollToBottom();
            pollMessages(); // Trigger immediate poll on open
        }
    };

    // Send Message
    window.sendMatrixMessage = function () {
        const msg = input.val().trim();
        if (!msg) return;

        input.val(''); // Clear immediately

        $.post(anima_chat_vars.ajax_url, {
            action: 'anima_chat_send',
            nonce: anima_chat_vars.nonce,
            message: msg
        }, function (res) {
            if (res.success) {
                renderMessages(res.data);
            }
        });
    };

    // Handle Enter Key
    input.on('keypress', function (e) {
        if (e.which === 13) sendMatrixMessage();
    });

    // Poll Messages
    function pollMessages() {
        if (chatCore.hasClass('matrix-chat-collapsed')) return; // Don't poll if closed to save resources? Or poll slower?

        $.post(anima_chat_vars.ajax_url, {
            action: 'anima_chat_poll'
        }, function (res) {
            if (res.success) {
                renderMessages(res.data);
            }
        });
    }

    function renderMessages(data) {
        if (!data || data.length === 0) return;

        // Check if new messages arrived
        const latest = data[data.length - 1];
        if (lastMsgId === latest.id) return; // No changes

        lastMsgId = latest.id;
        messagesContainer.empty();

        data.forEach(msg => {
            const isSelf = msg.user === anima_chat_vars.current_user;
            const time = new Date(msg.time * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            const html = `
                <div class="chat-msg ${isSelf ? 'self' : ''}">
                    <span class="timestamp">[${time}]</span>
                    <span class="user">${msg.user}:</span>
                    <span class="text">${escapeHtml(msg.msg)}</span>
                </div>
            `;
            messagesContainer.append(html);
        });

        scrollToBottom();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Start Polling (every 4 seconds)
    setInterval(pollMessages, 4000);
    pollMessages(); // Initial load
});
