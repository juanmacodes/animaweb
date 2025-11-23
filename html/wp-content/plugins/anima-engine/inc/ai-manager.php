<?php
if (!defined('ABSPATH'))
    exit;

class Anima_AI_Manager
{

    public function __construct()
    {
        add_shortcode('anima_ai', [$this, 'render_shortcode']);
        add_action('wp_ajax_anima_ai_chat', [$this, 'handle_chat']);
        add_action('wp_ajax_nopriv_anima_ai_chat', [$this, 'handle_chat']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts()
    {
        wp_register_script('anima-ai-chat', ANIMA_ENGINE_URL . 'assets/js/ai-chat.js', ['jquery'], '1.0.0', true);
        wp_localize_script('anima-ai-chat', 'anima_ai_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('anima_ai_chat_nonce')
        ]);
    }

    public function render_shortcode($atts)
    {
        $atts = shortcode_atts(['id' => ''], $atts);
        $slug = $atts['id'];

        if (empty($slug))
            return '<p>Error: AI Assistant ID not specified.</p>';

        $assistants = get_option('anima_ai_assistants', []);
        if (!isset($assistants[$slug]))
            return '<p>Error: AI Assistant not found.</p>';

        $assistant = $assistants[$slug];
        wp_enqueue_script('anima-ai-chat');
        wp_enqueue_style('anima-ai-chat-css', ANIMA_ENGINE_URL . 'assets/css/ai-chat.css', [], '1.0.0');

        ob_start();
        ?>
        <div class="anima-ai-chat-container" data-assistant="<?php echo esc_attr($slug); ?>">
            <div class="anima-ai-header">
                <?php if (!empty($assistant['avatar'])): ?>
                    <img src="<?php echo esc_url($assistant['avatar']); ?>" alt="Avatar" class="anima-ai-avatar">
                <?php endif; ?>
                <div class="anima-ai-info">
                    <h4><?php echo esc_html($assistant['name']); ?></h4>
                    <span class="status-dot"></span> Online
                </div>
            </div>
            <div class="anima-ai-messages" id="chat-messages-<?php echo esc_attr($slug); ?>">
                <div class="message ai-message">
                    <p>Hola, soy <?php echo esc_html($assistant['name']); ?>. ¿En qué puedo ayudarte hoy?</p>
                </div>
            </div>
            <div class="anima-ai-input-area">
                <input type="text" class="anima-ai-input" placeholder="Escribe tu mensaje..."
                    data-target="<?php echo esc_attr($slug); ?>">
                <button class="anima-ai-send" data-target="<?php echo esc_attr($slug); ?>">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_chat()
    {
        check_ajax_referer('anima_ai_chat_nonce', 'nonce');

        $slug = sanitize_text_field($_POST['assistant_id']);
        $message = sanitize_text_field($_POST['message']);

        $assistants = get_option('anima_ai_assistants', []);
        if (!isset($assistants[$slug]))
            wp_send_json_error('Assistant not found');

        $assistant = $assistants[$slug];
        $system_prompt = $assistant['prompt'];

        // Call OpenAI
        $api_key = defined('ANIMA_OPENAI_KEY') ? ANIMA_OPENAI_KEY : '';
        if (empty($api_key))
            wp_send_json_error('API Key missing');

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ],
            'body' => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $system_prompt],
                    ['role' => 'user', 'content' => $message]
                ],
                'temperature' => 0.7
            ]),
            'timeout' => 30
        ]);

        if (is_wp_error($response))
            wp_send_json_error($response->get_error_message());

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $reply = $body['choices'][0]['message']['content'] ?? 'Error processing request.';

        wp_send_json_success(['reply' => $reply]);
    }
}

new Anima_AI_Manager();
