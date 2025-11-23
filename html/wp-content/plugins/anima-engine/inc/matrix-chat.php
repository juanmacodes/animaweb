<?php
if (!defined('ABSPATH'))
    exit;

class Anima_Matrix_Chat
{
    const OPTION_KEY = 'anima_matrix_chat_history';
    const MAX_MESSAGES = 50;

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_chat_ui']);

        // AJAX Handlers
        add_action('wp_ajax_anima_chat_send', [$this, 'handle_send']);
        add_action('wp_ajax_anima_chat_poll', [$this, 'handle_poll']);
        // Allow reading (but not sending) for non-logged in users if desired? 
        // For now, let's keep it logged-in only for interaction, maybe read-only for guests.
        add_action('wp_ajax_nopriv_anima_chat_poll', [$this, 'handle_poll']);
    }

    public function enqueue_assets()
    {
        if (!is_user_logged_in())
            return; // Only for logged in users for now

        wp_enqueue_style('anima-matrix-chat', ANIMA_ENGINE_URL . 'assets/css/matrix-chat.css', [], '1.0.0');
        wp_enqueue_script('anima-matrix-chat', ANIMA_ENGINE_URL . 'assets/js/matrix-chat.js', ['jquery'], '1.0.0', true);

        wp_localize_script('anima-matrix-chat', 'anima_chat_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('anima_chat_nonce'),
            'current_user' => wp_get_current_user()->display_name
        ]);
    }

    public function render_chat_ui()
    {
        if (!is_user_logged_in())
            return;
        ?>
        <div id="matrix-chat-core" class="matrix-chat-collapsed">
            <div class="matrix-chat-header" onclick="toggleMatrixChat()">
                <span class="status-led"></span>
                <span class="header-title">GLOBAL_NET // v1.0</span>
                <span class="toggle-icon">_</span>
            </div>
            <div class="matrix-chat-body">
                <div id="matrix-messages" class="matrix-messages">
                    <!-- Messages injected via JS -->
                    <div class="system-msg">> CONNECTING TO NODE...</div>
                </div>
                <div class="matrix-input-area">
                    <input type="text" id="matrix-input" placeholder="Transmit data..." maxlength="140">
                    <button id="matrix-send" onclick="sendMatrixMessage()">SEND</button>
                </div>
            </div>
        </div>
        <?php
    }

    public function handle_send()
    {
        check_ajax_referer('anima_chat_nonce', 'nonce');
        if (!is_user_logged_in())
            wp_send_json_error('Unauthorized');

        $msg_content = sanitize_text_field($_POST['message']);
        if (empty($msg_content))
            wp_send_json_error('Empty message');

        $user = wp_get_current_user();

        $new_message = [
            'id' => uniqid(),
            'user' => $user->display_name,
            'user_id' => $user->ID,
            'msg' => $msg_content,
            'time' => current_time('timestamp')
        ];

        // Get existing messages
        $history = get_option(self::OPTION_KEY, []);
        if (!is_array($history))
            $history = [];

        // Add new
        $history[] = $new_message;

        // Prune
        if (count($history) > self::MAX_MESSAGES) {
            $history = array_slice($history, -self::MAX_MESSAGES);
        }

        update_option(self::OPTION_KEY, $history);

        wp_send_json_success($history);
    }

    public function handle_poll()
    {
        // Public read access allowed
        $history = get_option(self::OPTION_KEY, []);
        if (!is_array($history))
            $history = [];

        wp_send_json_success($history);
    }
}

new Anima_Matrix_Chat();
