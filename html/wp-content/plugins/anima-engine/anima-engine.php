<?php
/**
 * Plugin Name: Anima Engine (Core)
 * Description: Motor central para funcionalidades específicas, widgets de Elementor y estilos del sistema Anima.
 * Version: 1.3.1
 * Author: Anima Studios
 * Text Domain: anima-engine
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

// Define constantes de rutas
define('ANIMA_ENGINE_PATH', plugin_dir_path(__FILE__));
define('ANIMA_ENGINE_URL', plugin_dir_url(__FILE__));

final class Anima_Engine_Core
{

    const VERSION = '1.3.1';
    const MINIMUM_ELEMENTOR_VERSION = '3.5.0';
    const MINIMUM_PHP_VERSION = '7.4';

    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init()
    {
        $this->load_includes();
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets()
    {
        $css_file = ANIMA_ENGINE_URL . 'assets/css/dashboard.css';
        wp_enqueue_style('anima-dashboard-style', $css_file, [], self::VERSION);
    }

    private function load_includes()
    {
        $cpt_file_inc = ANIMA_ENGINE_PATH . 'inc/cpt-curso.php';
        if (file_exists($cpt_file_inc))
            require_once($cpt_file_inc);

        $course_fields = ANIMA_ENGINE_PATH . 'inc/admin-course-fields.php';
        if (file_exists($course_fields))
            require_once($course_fields);

        $product_fields = ANIMA_ENGINE_PATH . 'inc/admin-product-fields.php';
        if (file_exists($product_fields))
            require_once($product_fields);

        $pro_panel = ANIMA_ENGINE_PATH . 'inc/admin-pro-panel.php';
        if (file_exists($pro_panel))
            require_once($pro_panel);

        $ai_manager = ANIMA_ENGINE_PATH . 'inc/ai-manager.php';
        if (file_exists($ai_manager))
            require_once($ai_manager);

        $gamification = ANIMA_ENGINE_PATH . 'inc/gamification-duels.php';
        if (file_exists($gamification))
            require_once($gamification);
    }

    public function register_widgets($widgets_manager)
    {
        $widgets = [
            '/elementor/widget-cursos-grid.php' => '\Anima\Engine\Elementor\Widget_Cursos_Grid',
            '/elementor/widget-agent-hud.php' => '\Anima\Engine\Elementor\Widget_Agent_HUD',
            '/elementor/widget-tech-tracks.php' => '\Anima\Engine\Elementor\Widget_Tech_Tracks',
            '/elementor/widget-active-protocol.php' => '\Anima\Engine\Elementor\Widget_Active_Protocol'
        ];

        foreach ($widgets as $file => $class) {
            if (file_exists(__DIR__ . $file)) {
                require_once(__DIR__ . $file);
                if (class_exists($class)) {
                    $widgets_manager->register(new $class());
                }
            }
        }
    }

    public function admin_notice_missing_main_plugin()
    {
    }
    public function admin_notice_minimum_elementor_version()
    {
    }
    public function admin_notice_minimum_php_version()
    {
    }
}

Anima_Engine_Core::instance();


/* ===========================================================
   1. ANIMA XP CORE
   =========================================================== */

if (!function_exists('anima_get_agent_stats')) {
    function anima_get_agent_stats($user_id = 0)
    {
        if (!$user_id)
            $user_id = get_current_user_id();

        $xp = (int) get_user_meta($user_id, 'anima_xp', true);
        $credits = (int) get_user_meta($user_id, 'anima_credits', true);

        $level = 1 + floor($xp / 1000);
        $xp_current_level = $xp % 1000;
        $xp_needed = 1000;
        $progress_percent = ($xp_needed > 0) ? ($xp_current_level / $xp_needed) * 100 : 0;

        return [
            'level' => $level,
            'xp' => $xp,
            'xp_partial' => $xp_current_level,
            'xp_needed' => $xp_needed,
            'progress' => $progress_percent,
            'credits' => $credits
        ];
    }
}

add_action('woocommerce_order_status_completed', 'anima_award_xp_on_purchase');

if (!function_exists('anima_award_xp_on_purchase')) {
    function anima_award_xp_on_purchase($order_id)
    {
        if (!$order_id)
            return;
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        if (!$user_id)
            return;

        if (get_post_meta($order_id, '_anima_xp_awarded', true) === 'yes')
            return;

        $xp_to_add = 0;
        $xp_per_course = 100;

        foreach ($order->get_items() as $item) {
            $quantity = $item->get_quantity();
            $xp_to_add += $xp_per_course * $quantity;
        }

        if ($xp_to_add > 0) {
            $current_xp = (int) get_user_meta($user_id, 'anima_xp', true);
            update_user_meta($user_id, 'anima_xp', $current_xp + $xp_to_add);
            anima_award_badge($user_id, 'cadet');
            update_post_meta($order_id, '_anima_xp_awarded', 'yes');
            $order->add_order_note("Gamificación: Se han asignado $xp_to_add XP.");
        }
    }
}

/* ===========================================================
   1.1. CRÉDITOS DE BIENVENIDA
   =========================================================== */
add_action('user_register', 'anima_award_welcome_credits');

if (!function_exists('anima_award_welcome_credits')) {
    function anima_award_welcome_credits($user_id)
    {
        if (!$user_id)
            return;

        // Asignar 50 créditos de bienvenida
        update_user_meta($user_id, 'anima_credits', 50);
    }
}


/* ===========================================================
   2. ELIMINADOR DE PANELES ANTIGUOS
   =========================================================== */
add_action('add_meta_boxes', 'anima_kill_zombie_metaboxes', 999);
function anima_kill_zombie_metaboxes()
{
    $zombies = ['anima_engine_curso_details', 'anima_course_data', 'course_details'];
    foreach ($zombies as $id) {
        remove_meta_box($id, 'curso', 'normal');
        remove_meta_box($id, 'curso', 'advanced');
        remove_meta_box($id, 'curso', 'side');
    }
}


/* ===========================================================
   3. SISTEMA DE CRÉDITOS
   =========================================================== */

define('ANIMA_CREDIT_PACKAGES_OPTION', 'anima_credit_packages_config');

if (!function_exists('anima_get_default_credit_packages')) {
    function anima_get_default_credit_packages()
    {
        return [
            8710 => ['title' => 'Datachip', 'credits' => 100],
            8711 => ['title' => 'Neural Pack', 'credits' => 250],
            8712 => ['title' => 'Nexus Core', 'credits' => 500],
        ];
    }
}

if (!function_exists('anima_get_credit_packages')) {
    function anima_get_credit_packages()
    {
        $saved_packages = get_option(ANIMA_CREDIT_PACKAGES_OPTION);
        $default_packages = anima_get_default_credit_packages();
        $final_packages = $default_packages;

        if (!empty($saved_packages) && is_array($saved_packages)) {
            foreach ($saved_packages as $product_id_key => $data) {
                $product_id = (int) $product_id_key;
                if (array_key_exists($product_id, $default_packages)) {
                    if (!empty($data['title']) && is_numeric($data['credits'])) {
                        $final_packages[$product_id] = [
                            'title' => sanitize_text_field($data['title']),
                            'credits' => (int) $data['credits'],
                        ];
                    }
                }
            }
            if (count($saved_packages) !== count($default_packages)) {
                update_option(ANIMA_CREDIT_PACKAGES_OPTION, $final_packages);
            }
        }
        return $final_packages;
    }
}

add_action('woocommerce_order_status_completed', 'anima_assign_credits_on_purchase');

if (!function_exists('anima_assign_credits_on_purchase')) {
    function anima_assign_credits_on_purchase($order_id)
    {
        if (!$order_id || !class_exists('WooCommerce'))
            return;

        $credit_packages_config = anima_get_credit_packages();
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        if (get_post_meta($order_id, '_anima_credits_awarded', true) === 'yes')
            return;

        $credits_to_add = 0;
        foreach ($order->get_items() as $item) {
            if (!is_a($item, 'WC_Order_Item_Product')) {
                continue;
            }
            $product_id = $item->get_product_id();
            if (isset($credit_packages_config[$product_id])) {
                $credits_to_add += $credit_packages_config[$product_id]['credits'] * $item->get_quantity();
            }
        }

        if ($credits_to_add > 0) {
            $current_credits = (int) get_user_meta($user_id, 'anima_credits', true);
            update_user_meta($user_id, 'anima_credits', $current_credits + $credits_to_add);
            update_post_meta($order_id, '_anima_credits_awarded', 'yes');
            $order->add_order_note("Créditos asignados: $credits_to_add");
        }
    }
}

add_action('show_user_profile', 'anima_admin_credits_field');
add_action('edit_user_profile', 'anima_admin_credits_field');
add_action('personal_options_update', 'anima_save_admin_credits_field');
add_action('edit_user_profile_update', 'anima_save_admin_credits_field');

if (!function_exists('anima_admin_credits_field')) {
    function anima_admin_credits_field($user)
    {
        if (!current_user_can('edit_users'))
            return;
        $credits = (int) get_user_meta($user->ID, 'anima_credits', true);
        ?>
        <h3 id="anima-credits-title">Créditos Neuronales</h3>
        <table class="form-table">
            <tr>
                <th>Saldo Actual:</th>
                <td><strong style="color: #00F0FF;"><?php echo number_format($credits); ?> ⚡</strong></td>
            </tr>
            <tr>
                <th>Modificar Saldo (+/-)</th>
                <td><input type="number" name="anima_credit_change" placeholder="Ej: 100 o -50"></td>
            </tr>
        </table>
        <?php
    }
}

if (!function_exists('anima_save_admin_credits_field')) {
    function anima_save_admin_credits_field($user_id)
    {
        if (!current_user_can('edit_users') || empty($_POST['anima_credit_change']))
            return;
        $change = (int) sanitize_text_field($_POST['anima_credit_change']);
        $current = (int) get_user_meta($user_id, 'anima_credits', true);
        update_user_meta($user_id, 'anima_credits', max(0, $current + $change));
    }
}


/* ===========================================================
   4. CANJE DE CURSOS
   =========================================================== */

add_action('admin_post_anima_redeem_course', 'anima_handle_course_redeem');

if (!function_exists('anima_handle_course_redeem')) {
    function anima_handle_course_redeem()
    {
        $user_id = get_current_user_id();
        if (!is_user_logged_in())
            wp_redirect(home_url('/login/'));

        if (!isset($_POST['redeem_nonce']) || !wp_verify_nonce($_POST['redeem_nonce'], 'anima_redeem_action')) {
            wp_die('Error de seguridad.');
        }

        $course_post_id = (int) $_POST['course_id'];
        $product_id = get_post_meta($course_post_id, '_anima_product_id', true);
        $cost = (int) get_post_meta($product_id, 'anima_course_credit_cost', true);
        $current_credits = (int) get_user_meta($user_id, 'anima_credits', true);

        if ($current_credits < $cost) {
            wp_redirect(add_query_arg('redeem_status', 'failed_balance', get_permalink($course_post_id)));
            exit;
        }

        update_user_meta($user_id, 'anima_credits', $current_credits - $cost);

        if (function_exists('wc_create_order') && $product_id) {
            $order = wc_create_order(['customer_id' => $user_id]);
            $order->add_product(wc_get_product($product_id), 1);
            $order->set_total(0);
            $order->update_status('completed', 'Canjeado con créditos.', true);
            $order->save();
        }

        wp_redirect(add_query_arg('redeem_status', 'success', get_permalink($course_post_id)));
        exit;
    }
}


/* ===========================================================
   5. AI LAB (CORREGIDO CON LIMPIEZA DE BUFFER)
   =========================================================== */

if (!function_exists('anima_spend_credits')) {
    function anima_spend_credits($user_id, $cost)
    {
        if (!$user_id || $cost <= 0)
            return false;
        $current = (int) get_user_meta($user_id, 'anima_credits', true);
        if ($current < $cost)
            return false;
        update_user_meta($user_id, 'anima_credits', $current - $cost);
        return true;
    }
}

// A. Generar Bio
add_action('wp_ajax_anima_generate_bio', 'anima_handle_generate_bio');
add_action('wp_ajax_nopriv_anima_generate_bio', 'anima_handle_generate_bio');

if (!function_exists('anima_handle_generate_bio')) {
    function anima_handle_generate_bio()
    {
        // --- BLINDAJE AJAX ---
        @ini_set('display_errors', 0); // Ocultar errores PHP
        ob_start(); // Iniciar buffer para capturar basura

        $user_id = get_current_user_id();
        if (!is_user_logged_in()) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Requiere iniciar sesión.'], 401);
        }

        $cost = 2;
        if (!anima_spend_credits($user_id, $cost)) {
            ob_end_clean();
            wp_send_json_error(['message' => "Saldo insuficiente. Necesitas $cost créditos."], 403);
        }

        $name = !empty($_POST['name']) ? sanitize_text_field($_POST['name']) : 'Agente';
        $style = !empty($_POST['style']) ? sanitize_text_field($_POST['style']) : 'Estándar';
        $traits = !empty($_POST['traits']) ? sanitize_text_field($_POST['traits']) : 'Sin datos';

        $bio = "REGISTRO: Identidad para {$name}.\nARQUETIPO: {$style}\nRASGOS: {$traits}\n[Generado por Anima Engine]";

        ob_end_clean(); // Limpiar buffer antes de enviar
        wp_send_json_success(['bio' => $bio, 'cost' => $cost]);
        wp_die();
    }
}

// B. Generar Imagen
add_action('wp_ajax_anima_generate_avatar_img', 'anima_handle_generate_img');
add_action('wp_ajax_nopriv_anima_generate_avatar_img', 'anima_handle_generate_img');

if (!function_exists('anima_handle_generate_img')) {
    function anima_handle_generate_img()
    {
        // 1. Limpieza y Seguridad
        @ini_set('display_errors', 0);
        ob_start();

        $user_id = get_current_user_id();

        if (!is_user_logged_in()) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Requiere iniciar sesión.'], 401);
        }

        // Verificar API Key
        if (!defined('ANIMA_OPENAI_KEY') || empty(ANIMA_OPENAI_KEY)) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Error: API Key no configurada.']);
        }

        // 2. Cobrar Créditos (200)
        $cost = 10;
        if (!anima_spend_credits($user_id, $cost)) {
            ob_end_clean();
            wp_send_json_error(['message' => "Saldo insuficiente. Necesitas $cost créditos."], 403);
        }

        // 3. Preparar el Prompt para la IA
        $style_raw = !empty($_POST['style']) ? sanitize_text_field($_POST['style']) : 'Cyberpunk';
        $desc_raw = !empty($_POST['desc']) ? sanitize_text_field($_POST['desc']) : 'Un agente futurista';

        // Ingeniería de Prompt: Mejoramos lo que escribe el usuario para asegurar un buen resultado
        $final_prompt = "A high quality digital portrait of a character. Style: $style_raw. Description: $desc_raw. The image should be a centered character portrait, looking at camera, highly detailed, unreal engine 5 style, cinematic lighting, 8k resolution, cyberpunk aesthetics.";

        // 4. Llamada a OpenAI (DALL-E 3)
        $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
            'body' => json_encode([
                'model' => 'dall-e-3', // Modelo de alta calidad
                'prompt' => $final_prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard'
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . ANIMA_OPENAI_KEY,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 45, // DALL-E tarda más, damos más tiempo
        ]);

        // 5. Procesar Respuesta
        if (is_wp_error($response)) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Error de conexión con DALL-E.']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Si hay error en la API (ej: prompt ofensivo o sin saldo)
        if (isset($body['error'])) {
            ob_end_clean();
            // Devolvemos créditos si falló la generación
            $current = (int) get_user_meta($user_id, 'anima_credits', true);
            update_user_meta($user_id, 'anima_credits', $current + $cost);

            wp_send_json_error(['message' => 'Error IA: ' . $body['error']['message']]);
        }

        // Éxito: Obtener URL
        $image_url = $body['data'][0]['url'] ?? '';

        if (empty($image_url)) {
            ob_end_clean();
            wp_send_json_error(['message' => 'No se pudo generar la imagen.']);
        }

        ob_end_clean();
        wp_send_json_success(['url' => $image_url, 'cost' => $cost]);
        wp_die();
    }
}

// C. Guardar Bio
add_action('wp_ajax_anima_save_ai_bio', 'anima_handle_save_bio');
if (!function_exists('anima_handle_save_bio')) {
    function anima_handle_save_bio()
    {
        @ini_set('display_errors', 0);
        ob_start();

        if (!is_user_logged_in()) {
            ob_end_clean();
            wp_send_json_error('No autorizado');
        }

        $bio = isset($_POST['bio_content']) ? sanitize_textarea_field($_POST['bio_content']) : '';
        update_user_meta(get_current_user_id(), 'anima_ai_bio', $bio);

        ob_end_clean();
        wp_send_json_success('Guardado.');
        wp_die();
    }
}

// D. Guardar Avatar
add_action('wp_ajax_anima_save_generated_avatar', 'anima_handle_save_avatar');
if (!function_exists('anima_handle_save_avatar')) {
    function anima_handle_save_avatar()
    {
        // 1. Seguridad y Limpieza
        @ini_set('display_errors', 0);
        ob_start();

        if (!is_user_logged_in()) {
            ob_end_clean();
            wp_send_json_error(['message' => 'No autorizado']);
        }

        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';

        if (empty($image_url)) {
            ob_end_clean();
            wp_send_json_error(['message' => 'URL de imagen inválida.']);
        }

        // 2. Descargar imagen al servidor
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Descargar y guardar como attachment
        $desc = "Avatar IA generado por Anima Engine";
        $attach_id = media_sideload_image($image_url, 0, $desc, 'id');

        if (is_wp_error($attach_id)) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Error al guardar imagen: ' . $attach_id->get_error_message()]);
        }

        // 3. Asignar al usuario
        update_user_meta(get_current_user_id(), 'profile_picture', $attach_id);

        ob_end_clean();
        wp_send_json_success(['message' => '¡Avatar actualizado con éxito!']);
        wp_die();
    }
}


/* ===========================================================
   6. CONEXIÓN OPENAI (CHATBOT ORB)
   =========================================================== */

add_action('wp_ajax_anima_chat_request', 'anima_handle_chat_request');
add_action('wp_ajax_nopriv_anima_chat_request', 'anima_handle_chat_request');

if (!function_exists('anima_handle_chat_request')) {
    function anima_handle_chat_request()
    {
        @ini_set('display_errors', 0);
        ob_start();

        if (!defined('ANIMA_OPENAI_KEY') || empty(ANIMA_OPENAI_KEY)) {
            ob_end_clean();
            wp_send_json_error(['reply' => 'Error: API Key no configurada.']);
        }

        $message = sanitize_text_field($_POST['message'] ?? '');
        if (empty($message)) {
            ob_end_clean();
            wp_send_json_error(['reply' => 'Mensaje vacío.']);
        }

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'body' => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => "Eres A.N.I.M.A., una IA Cyberpunk. Responde breve y técnico."],
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 150
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . ANIMA_OPENAI_KEY,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 20,
        ]);

        ob_end_clean();

        if (is_wp_error($response))
            wp_send_json_error(['reply' => 'Error conexión.']);

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $reply = $body['choices'][0]['message']['content'] ?? 'Sin datos.';

        wp_send_json_success(['reply' => $reply]);
        wp_die();
    }
}

/* ===========================================================
   7. ORBE FLOTANTE (FRONTEND)
   =========================================================== */
add_action('wp_footer', 'anima_render_academy_orb');

if (!function_exists('anima_render_academy_orb')) {
    function anima_render_academy_orb()
    {
        if (!is_user_logged_in())
            return;
        if (!is_singular('curso') && !is_post_type_archive('curso'))
            return;
        ?>
        <div id="anima-orb-wrapper">
            <div id="anima-chat-window" class="anima-chat-window">
                <div class="chat-header">
                    <div class="chat-status"><span class="status-dot"></span><span class="status-text">A.N.I.M.A. ONLINE</span>
                    </div>
                    <button id="close-chat-btn" class="close-btn">×</button>
                </div>
                <div id="chat-messages" class="chat-messages">
                    <div class="message bot">
                        <div class="msg-content">Sistemas listos. ¿En qué puedo ayudarte, Agente?</div>
                    </div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chat-input" placeholder="Escribe..." autocomplete="off">
                    <button id="send-msg-btn">></button>
                </div>
            </div>
            <div id="anima-orb-trigger" class="anima-orb">
                <div class="orb-core"></div>
                <div class="orb-ring"></div>
            </div>
        </div>
        <style>
            #anima-orb-wrapper {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 9999;
                font-family: sans-serif;
            }

            .anima-orb {
                width: 60px;
                height: 60px;
                position: relative;
                cursor: pointer;
            }

            .orb-core {
                width: 100%;
                height: 100%;
                background: #00F0FF;
                border-radius: 50%;
                box-shadow: 0 0 20px #00F0FF;
            }

            .orb-ring {
                position: absolute;
                top: -5px;
                left: -5px;
                right: -5px;
                bottom: -5px;
                border: 2px solid #BC13FE;
                border-radius: 50%;
                animation: spin 2s linear infinite;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            .anima-chat-window {
                position: absolute;
                bottom: 80px;
                right: 0;
                width: 300px;
                height: 400px;
                background: #111;
                border: 1px solid #333;
                display: none;
                flex-direction: column;
            }

            .anima-chat-window.active {
                display: flex;
            }

            .chat-header {
                padding: 10px;
                background: #222;
                display: flex;
                justify-content: space-between;
                color: #fff;
            }

            .chat-messages {
                flex: 1;
                padding: 10px;
                overflow-y: auto;
                color: #ddd;
            }

            .chat-input-area {
                padding: 10px;
                display: flex;
            }

            #chat-input {
                flex: 1;
                background: #000;
                border: 1px solid #444;
                color: #fff;
                padding: 5px;
            }

            .message {
                margin-bottom: 10px;
                padding: 8px;
                background: #222;
                border-radius: 4px;
            }

            .message.user {
                background: #333;
                text-align: right;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const orb = document.getElementById('anima-orb-trigger');
                const chat = document.getElementById('anima-chat-window');
                const close = document.getElementById('close-chat-btn');
                const input = document.getElementById('chat-input');
                const send = document.getElementById('send-msg-btn');
                const msgs = document.getElementById('chat-messages');
                const ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";

                orb.onclick = () => chat.classList.toggle('active');
                close.onclick = () => chat.classList.remove('active');

                async function sendMsg() {
                    const txt = input.value.trim();
                    if (!txt) return;

                    msgs.innerHTML += `<div class="message user"><div class="msg-content">${txt}</div></div>`;
                    input.value = '';

                    const fd = new FormData();
                    fd.append('action', 'anima_chat_request');
                    fd.append('message', txt);

                    try {
                        const res = await fetch(ajaxUrl, { method: 'POST', body: fd });
                        const data = await res.json();
                        const reply = data.success ? data.data.reply : "Error.";
                        msgs.innerHTML += `<div class="message bot"><div class="msg-content">${reply}</div></div>`;
                    } catch (e) { msgs.innerHTML += `<div class="message bot">Error red.</div>`; }
                    msgs.scrollTop = msgs.scrollHeight;
                }
                send.onclick = sendMsg;
                input.onkeypress = (e) => { if (e.key === 'Enter') sendMsg(); };
            });

            function animaSubscribeToPush() {
                // 1. Verificación básica del SDK
                if (typeof OneSignal !== 'undefined') {

                    // 2. Mostrar el prompt Slidedown (menos invasivo que el nativo)
                    OneSignal.showSlidedownPrompt().then(function () {
                        console.log("Iniciando secuencia de suscripción.");
                    });

                    // Puedes añadir un mensaje de éxito/error aquí después de la promesa si lo deseas
                } else {
                    alert("El motor de notificaciones aún no está sincronizado. Intente recargar.");
                }
            }

        </script>
        <?php
    }
}

/* ===========================================================
   8. SISTEMA DE CONEXIONES (SHORTCODE PARA EL DASHBOARD)
   =========================================================== */

add_shortcode('anima_connections_v2', 'anima_render_my_connections_shortcode');

if (!function_exists('anima_render_my_connections_shortcode')) {
    function anima_render_my_connections_shortcode()
    {
        if (!is_user_logged_in())
            return '';

        global $wpdb;
        $current_user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'anima_connections';

        // 1. CONSULTA SEGURA: Usamos 'requester_id' y 'recipient_id' (Nombres correctos)
        // Buscamos filas donde el usuario sea el que pide O el que recibe, y el estado sea 'accepted'
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE (requester_id = %d OR recipient_id = %d) 
             AND status = 'accepted'",
            $current_user_id,
            $current_user_id
        );

        $connections = $wpdb->get_results($query);

        if (empty($connections)) {
            return '
            <div style="text-align:center; padding:40px; border:1px dashed #333; border-radius:10px; color:#888;">
                <span class="dashicons dashicons-networking" style="font-size:40px; margin-bottom:10px; display:block; color:#555;"></span>
                <p>No hay enlaces neuronales activos.</p>
                <a href="' . home_url('/comunidad/') . '" class="anima-btn anima-btn-small" style="margin-top:10px;">Explorar Nexus</a>
            </div>';
        }

        // 2. GENERAR HTML DE LA LISTA
        ob_start();
        ?>
        <div class="anima-connections-grid-dashboard">
            <?php foreach ($connections as $conn):
                // Determinar quién es el "amigo" (el ID que no es el mío)
                $friend_id = ($conn->requester_id == $current_user_id) ? $conn->recipient_id : $conn->requester_id;
                $friend_data = get_userdata($friend_id);

                if (!$friend_data)
                    continue; // Si el usuario fue borrado, saltar
    
                // Obtener avatar (prioriza custom, fallback a gravatar)
                $custom_avatar_id = get_user_meta($friend_id, 'profile_picture', true);
                $avatar_url = '';
                if ($custom_avatar_id) {
                    $img = wp_get_attachment_image_src($custom_avatar_id, 'thumbnail');
                    $avatar_url = $img ? $img[0] : '';
                }
                if (empty($avatar_url)) {
                    $avatar_url = get_avatar_url($friend_id);
                }
                ?>
                <div class="anima-friend-card">
                    <div class="friend-avatar">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($friend_data->display_name); ?>">
                        <span class="online-indicator"></span>
                    </div>
                    <div class="friend-info">
                        <h4><?php echo esc_html($friend_data->display_name); ?></h4>
                        <span class="friend-role">Agente @<?php echo esc_html($friend_data->user_login); ?></span>
                    </div>
                    <div class="friend-actions">
                        <a href="<?php echo esc_url(get_author_posts_url($friend_id)); ?>" class="anima-icon-btn"
                            title="Ver Perfil">
                            <span class="dashicons dashicons-visibility"></span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <style>
            /* Estilos incrustados para el grid de conexiones en el dashboard */
            .anima-connections-grid-dashboard {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }

            .anima-friend-card {
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid #333;
                border-radius: 8px;
                padding: 15px;
                display: flex;
                align-items: center;
                gap: 12px;
                transition: all 0.3s ease;
            }

            .anima-friend-card:hover {
                border-color: #00F0FF;
                background: rgba(0, 240, 255, 0.05);
                transform: translateY(-2px);
            }

            .friend-avatar {
                position: relative;
                width: 40px;
                height: 40px;
                flex-shrink: 0;
            }

            .friend-avatar img {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                object-fit: cover;
                border: 1px solid #555;
            }

            .online-indicator {
                position: absolute;
                bottom: 0;
                right: 0;
                width: 8px;
                height: 8px;
                background: #00FF94;
                border-radius: 50%;
                border: 1px solid #000;
            }

            .friend-info {
                flex-grow: 1;
                overflow: hidden;
            }

            .friend-info h4 {
                margin: 0;
                font-size: 0.95rem;
                color: #fff;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .friend-role {
                font-size: 0.75rem;
                color: #888;
                display: block;
            }

            .friend-actions {
                display: flex;
                gap: 5px;
            }

            .anima-icon-btn {
                color: #aaa;
                text-decoration: none;
                transition: color 0.2s;
            }

            .anima-icon-btn:hover {
                color: #00F0FF;
            }
        </style>
        <?php
        return ob_get_clean();
    }
}

/* ===========================================================
   8. SHORTCODE: LISTADO DE AMIGOS (DASHBOARD)
   Uso: [anima_my_connections_list]
   =========================================================== */

add_shortcode('anima_my_connections_list', 'anima_shortcode_my_connections');

if (!function_exists('anima_shortcode_my_connections')) {
    function anima_shortcode_my_connections()
    {
        if (!is_user_logged_in())
            return '';

        global $wpdb;
        $current_user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'anima_connections';

        // 1. Buscar conexiones aceptadas
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE (requester_id = %d OR recipient_id = %d) 
             AND status = 'accepted'",
            $current_user_id,
            $current_user_id
        );

        $results = $wpdb->get_results($query);

        // 2. Si no hay amigos
        if (empty($results)) {
            return '<div style="text-align:center; padding:30px; color:#888; border:1px dashed #444; border-radius:8px;">
                <span class="dashicons dashicons-networking" style="font-size:30px; margin-bottom:10px; display:block;"></span>
                No tienes enlaces activos. <a href="' . home_url('/comunidad/') . '" style="color:#00F0FF;">Ir al Nexus</a>
            </div>';
        }

        // 3. Generar HTML de la lista
        ob_start();
        ?>
        <div class="anima-connections-grid">
            <?php foreach ($results as $conn):
                // Determinar quién es el amigo
                $friend_id = ($conn->requester_id == $current_user_id) ? $conn->recipient_id : $conn->requester_id;
                $friend = get_userdata($friend_id);

                if (!$friend)
                    continue;

                // Obtener avatar (Custom o Gravatar)
                $custom_avatar = get_user_meta($friend_id, 'profile_picture', true);
                $avatar_url = $custom_avatar ? wp_get_attachment_image_url($custom_avatar, 'thumbnail') : get_avatar_url($friend_id);
                ?>

                <div class="connection-card">
                    <a href="<?php echo esc_url(get_author_posts_url($friend_id)); ?>" class="friend-link">
                        <div class="friend-avatar">
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($friend->display_name); ?>">
                        </div>
                        <div class="friend-info">
                            <span class="friend-name"><?php echo esc_html($friend->display_name); ?></span>
                            <span class="friend-user">@<?php echo esc_html($friend->user_login); ?></span>
                        </div>
                    </a>
                    <div class="friend-actions">
                        <a href="<?php echo esc_url(home_url('/mi-cuenta/?view=inbox&compose=' . $friend_id)); ?>"
                            class="action-icon" title="Enviar Mensaje">
                            <span class="dashicons dashicons-email-alt"></span>
                        </a>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>

        <style>
            /* Estilos CSS exclusivos para este shortcode */
            .anima-connections-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }

            .connection-card {
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid #333;
                border-radius: 8px;
                padding: 15px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                transition: 0.3s;
            }

            .connection-card:hover {
                border-color: #00F0FF;
                background: rgba(0, 240, 255, 0.05);
            }

            .friend-link {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                color: inherit;
                flex-grow: 1;
            }

            .friend-avatar img {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
                border: 1px solid #555;
            }

            .friend-info {
                display: flex;
                flex-direction: column;
                line-height: 1.2;
            }

            .friend-name {
                color: #fff;
                font-weight: 600;
                font-size: 0.95em;
            }

            .friend-user {
                color: #888;
                font-size: 0.8em;
            }

            .action-icon {
                color: #888;
                text-decoration: none;
                padding: 5px;
                transition: 0.2s;
            }

            .action-icon:hover {
                color: #BC13FE;
                transform: scale(1.1);
            }
        </style>
        <?php
        return ob_get_clean();
    }
}

/* ===========================================================
   9. MOTOR SOCIAL (LIKES Y COMENTARIOS AJAX)
   =========================================================== */

// --- A. SISTEMA DE LIKES ---
add_action('wp_ajax_anima_nexus_like', 'anima_handle_nexus_like');

if (!function_exists('anima_handle_nexus_like')) {
    function anima_handle_nexus_like()
    {
        if (!is_user_logged_in())
            wp_send_json_error('No autorizado');

        $post_id = (int) $_POST['post_id'];
        $user_id = get_current_user_id();

        $likes = (int) get_post_meta($post_id, '_anima_likes_count', true);
        $users_who_liked = get_post_meta($post_id, '_anima_liked_users', true);

        if (!is_array($users_who_liked))
            $users_who_liked = [];

        if (in_array($user_id, $users_who_liked)) {
            // Ya le dio like -> QUITAR LIKE
            $likes--;
            $users_who_liked = array_diff($users_who_liked, [$user_id]);
            $action = 'removed';
        } else {
            // No le ha dado like -> DAR LIKE
            $likes++;
            $users_who_liked[] = $user_id;
            $action = 'added';

            // Opcional: Dar XP al autor del post por recibir like
            // $author = get_post_field( 'post_author', $post_id );
            // if($author != $user_id) { ... sumar XP ... }
        }

        // Guardar
        update_post_meta($post_id, '_anima_likes_count', max(0, $likes));
        update_post_meta($post_id, '_anima_liked_users', $users_who_liked);

        wp_send_json_success(['count' => $likes, 'action' => $action]);
    }
}

// Helpers para template
function anima_get_likes_count($post_id)
{
    return (int) get_post_meta($post_id, '_anima_likes_count', true);
}
function anima_user_has_liked($post_id, $user_id)
{
    $users = get_post_meta($post_id, '_anima_liked_users', true);
    return (is_array($users) && in_array($user_id, $users));
}


// --- B. SISTEMA DE COMENTARIOS AJAX (Cargar y Publicar) ---

// 1. Cargar Comentarios
add_action('wp_ajax_anima_load_comments', 'anima_handle_load_comments');

if (!function_exists('anima_handle_load_comments')) {
    function anima_handle_load_comments()
    {
        $post_id = (int) $_GET['post_id'];
        $comments = get_comments([
            'post_id' => $post_id,
            'status' => 'approve',
            'order' => 'ASC'
        ]);

        ob_start();
        if ($comments) {
            foreach ($comments as $comment) {
                $author_id = $comment->user_id;
                $is_admin = user_can($author_id, 'manage_options') ? 'is-admin' : '';
                ?>
                <div class="cyber-comment <?php echo $is_admin; ?>">
                    <div class="comment-avatar">
                        <?php echo get_avatar($author_id, 32); ?>
                    </div>
                    <div class="comment-body">
                        <strong class="comment-author"><?php echo get_comment_author($comment->comment_ID); ?></strong>
                        <div class="comment-text"><?php echo wpautop($comment->comment_content); ?></div>
                        <span class="comment-date"><?php echo human_time_diff(strtotime($comment->comment_date)) . ' atrás'; ?></span>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p class="no-comments">Sin transmisiones de respuesta. Inicia el enlace.</p>';
        }
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }
}

// 2. Publicar Comentario
add_action('wp_ajax_anima_post_comment', 'anima_handle_post_comment');

if (!function_exists('anima_handle_post_comment')) {
    function anima_handle_post_comment()
    {
        if (!is_user_logged_in())
            wp_send_json_error('Login requerido');

        $post_id = (int) $_POST['post_id'];
        $content = sanitize_textarea_field($_POST['content']);
        $user = wp_get_current_user();

        if (empty($content))
            wp_send_json_error('Mensaje vacío');

        $data = [
            'comment_post_ID' => $post_id,
            'comment_content' => $content,
            'user_id' => $user->ID,
            'comment_author' => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_approved' => 1, // Aprobar automáticamente
        ];

        $comment_id = wp_insert_comment($data);

        if ($comment_id) {
            wp_send_json_success('Publicado');
        } else {
            wp_send_json_error('Error al guardar');
        }
    }
}

/* ===========================================================
   8. GAMIFICATION 2.0 - LOGROS Y RETOS
   =========================================================== */

// --- A. CONFIGURACIÓN DE BADGES (INSIGNIAS) ---
if (!function_exists('anima_get_system_badges')) {
    function anima_get_system_badges()
    {
        return [
            'initiate' => [
                'title' => 'Iniciado',
                'desc' => 'Completaste tu registro en la agencia.',
                'icon' => '🆔',
                'xp' => 100
            ],
            'bio_architect' => [
                'title' => 'Arquitecto de Identidad',
                'desc' => 'Generaste tu primera biografía con IA.',
                'icon' => '🧬',
                'xp' => 200
            ],
            'visualizer' => [
                'title' => 'Visualizador',
                'desc' => 'Generaste tu primer avatar visual.',
                'icon' => '👁️',
                'xp' => 250
            ],
            'cadet' => [
                'title' => 'Cadete',
                'desc' => 'Adquiriste tu primer curso de entrenamiento.',
                'icon' => '🎓',
                'xp' => 500
            ],
            'nexus_voice' => [
                'title' => 'Voz del Nexus',
                'desc' => 'Publicaste tu primera transmisión en el feed.',
                'icon' => '📢',
                'xp' => 150
            ],
            'connector' => [
                'title' => 'Conector',
                'desc' => 'Estableciste tu primer enlace neuronal (amigo).',
                'icon' => '🔗',
                'xp' => 300
            ]
        ];
    }
}

// --- B. FUNCIÓN PARA OTORGAR BADGE ---
if (!function_exists('anima_award_badge')) {
    function anima_award_badge($user_id, $badge_id)
    {
        $badges = get_user_meta($user_id, 'anima_user_badges', true);
        if (!is_array($badges))
            $badges = [];

        // Si ya lo tiene, no hacemos nada
        if (in_array($badge_id, $badges))
            return false;

        // Otorgar Badge
        $badges[] = $badge_id;
        update_user_meta($user_id, 'anima_user_badges', $badges);

        // Otorgar XP del Badge
        $all_badges = anima_get_system_badges();
        if (isset($all_badges[$badge_id]['xp'])) {
            $current_xp = (int) get_user_meta($user_id, 'anima_xp', true);
            update_user_meta($user_id, 'anima_xp', $current_xp + $all_badges[$badge_id]['xp']);
        }

        return true; // Badge nuevo otorgado
    }
}

// --- C. SISTEMA DE RETO SEMANAL (WEEKLY CHALLENGE) ---
if (!function_exists('anima_get_weekly_mission')) {
    function anima_get_weekly_mission()
    {
        // Definimos la misión basada en el número de semana del año para que todos tengan la misma
        $week_number = date('W');

        $missions = [
            0 => ['id' => 'bio_gen_3', 'title' => 'Génesis de Identidad', 'desc' => 'Genera 3 biografías en el AI Lab.', 'target' => 3, 'reward_credits' => 100],
            1 => ['id' => 'nexus_post_2', 'title' => 'Reportero de Campo', 'desc' => 'Publica 2 transmisiones en el Nexus.', 'target' => 2, 'reward_credits' => 150],
            2 => ['id' => 'login_5', 'title' => 'Persistencia', 'desc' => 'Inicia sesión 5 días distintos.', 'target' => 5, 'reward_credits' => 200],
            // Puedes añadir más misiones aquí
        ];

        // Rotación simple usando el módulo
        $mission_index = $week_number % count($missions);
        return $missions[$mission_index];
    }
}

if (!function_exists('anima_update_mission_progress')) {
    function anima_update_mission_progress($user_id, $action_type, $amount = 1)
    {
        $mission = anima_get_weekly_mission();

        // Comprobar si la acción coincide con la misión actual
        // (Esta lógica es simplificada, idealmente mapearíamos acciones a IDs de misión)
        $match = false;
        if ($mission['id'] === 'bio_gen_3' && $action_type === 'generate_bio')
            $match = true;
        if ($mission['id'] === 'nexus_post_2' && $action_type === 'post_nexus')
            $match = true;

        if (!$match)
            return;

        $progress = get_user_meta($user_id, 'anima_weekly_progress_' . $mission['id'], true);
        if (!is_array($progress))
            $progress = ['count' => 0, 'completed' => false];

        if ($progress['completed'])
            return;

        $progress['count'] += $amount;

        if ($progress['count'] >= $mission['target']) {
            $progress['completed'] = true;
            $progress['count'] = $mission['target'];

            // Dar Recompensa
            $current_credits = (int) get_user_meta($user_id, 'anima_credits', true);
            update_user_meta($user_id, 'anima_credits', $current_credits + $mission['reward_credits']);
        }

        update_user_meta($user_id, 'anima_weekly_progress_' . $mission['id'], $progress);
    }
}

// --- D. GANCHOS AUTOMÁTICOS (TRIGGERS) ---

// 1. Trigger: Generar Bio
add_action('wp_ajax_anima_generate_bio', function () {
    if (is_user_logged_in()) {
        anima_award_badge(get_current_user_id(), 'bio_architect'); // Badge
        anima_update_mission_progress(get_current_user_id(), 'generate_bio'); // Misión
    }
}, 9); // Prioridad 9 para ejecutarse antes del die()

// 2. Trigger: Generar Imagen
add_action('wp_ajax_anima_generate_avatar_img', function () {
    if (is_user_logged_in())
        anima_award_badge(get_current_user_id(), 'visualizer');
}, 9);

// 3. Trigger: Publicar en Nexus
add_action('save_post_nexus_post', function ($post_id, $post, $update) {
    if ($post->post_status == 'publish') {
        anima_award_badge($post->post_author, 'nexus_voice');
        anima_update_mission_progress($post->post_author, 'post_nexus');
    }
}, 10, 3);

add_action('template_redirect', function () {
    if (is_page('mi-cuenta') && is_user_logged_in()) {
        anima_award_badge(get_current_user_id(), 'initiate');
    }
});

/* ===========================================================
   9. SISTEMA DE VIDEO SEGURO (STREAMING PROXY)
   =========================================================== */

add_action('init', 'anima_secure_video_stream');

if (!function_exists('anima_secure_video_stream')) {
    function anima_secure_video_stream()
    {
        // Escuchar la señal ?anima_video=true
        if (isset($_GET['anima_video']) && $_GET['anima_video'] == 'true') {

            // 1. Seguridad: Usuario Logueado
            if (!is_user_logged_in())
                wp_die('Acceso Denegado: Identifícate.', 403);

            $course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
            $file_name = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '';
            $user_id = get_current_user_id();

            if (!$course_id || !$file_name)
                wp_die('Datos corruptos.', 400);

            // 2. Seguridad: ¿Compró el curso?
            $product_id = get_post_meta($course_id, '_anima_product_id', true);
            $has_access = false;

            if (function_exists('wc_customer_bought_product') && $product_id) {
                if (wc_customer_bought_product(wp_get_current_user()->user_email, $user_id, $product_id)) {
                    $has_access = true;
                }
            }
            // Permitir también al admin o si el curso es gratis (costo 0)
            if (current_user_can('manage_options'))
                $has_access = true;

            if (!$has_access)
                wp_die('Acceso Denegado: No posees los credenciales para este archivo.', 403);

            // 3. Servir el Archivo
            // RUTA FÍSICA EN EL SERVIDOR (Ajusta si tu carpeta 'anima-protected' está en otro lado)
            $base_path = WP_CONTENT_DIR . '/anima-protected/courses/' . $course_id . '/';
            $file_path = $base_path . $file_name;

            if (!file_exists($file_path))
                wp_die('Archivo no encontrado en el servidor (Error 404).', 404);

            $mime = mime_content_type($file_path);
            $size = filesize($file_path);

            // Headers para Streaming
            header("Content-Type: $mime");
            header("Content-Length: $size");
            header("Content-Disposition: inline; filename=\"$file_name\"");
            header("Cache-Control: private, max-age=10800, pre-check=10800");
            header("Pragma: private");
            header("Expires: " . date(DATE_RFC822, strtotime(" 2 day")));

            // Limpiar buffer de salida para no corromper el video
            if (ob_get_level())
                ob_end_clean();

            readfile($file_path);
            exit;
        }
    }
}

/* ===========================================================
   10. SISTEMA DE PROGRESO (TRACKING DE LECCIONES)
   =========================================================== */

add_action('wp_ajax_anima_toggle_lesson', 'anima_handle_toggle_lesson');

if (!function_exists('anima_handle_toggle_lesson')) {
    function anima_handle_toggle_lesson()
    {
        if (!is_user_logged_in())
            wp_send_json_error();

        $course_id = (int) $_POST['course_id'];
        $lesson_id = sanitize_text_field($_POST['lesson_id']); // Usaremos formato "modIndex_lessIndex"
        $user_id = get_current_user_id();

        // Obtener progreso actual
        $progress = get_user_meta($user_id, '_anima_progress_' . $course_id, true);
        if (!is_array($progress))
            $progress = [];

        // Toggle (Si está, lo quita. Si no, lo pone)
        if (in_array($lesson_id, $progress)) {
            $progress = array_diff($progress, [$lesson_id]);
            $action = 'unchecked';
        } else {
            $progress[] = $lesson_id;
            $action = 'checked';
        }

        update_user_meta($user_id, '_anima_progress_' . $course_id, $progress);

        // Calcular porcentaje
        $total_lessons = (int) $_POST['total_lessons'];
        $completed_count = count($progress);
        $percent = ($total_lessons > 0) ? round(($completed_count / $total_lessons) * 100) : 0;

        // VERIFICAR LOGRO: CURSO COMPLETADO
        if ($percent >= 100) {
            // Aquí podrías dar un Badge específico por completar curso
            // anima_award_badge($user_id, 'course_completed'); 
        }

        wp_send_json_success(['percent' => $percent, 'action' => $action]);
    }
}

/* ===========================================================
   10. PWA / WEB PUSH NOTIFICATIONS (ONE SIGNAL)
   =========================================================== */

add_action('wp_enqueue_scripts', 'anima_enqueue_onesignal_script');
add_action('wp_head', 'anima_output_web_app_manifest');

if (!function_exists('anima_enqueue_onesignal_script')) {
    function anima_enqueue_onesignal_script()
    {
        if (!defined('ANIMA_ONESIGNAL_APP_ID')) {
            return;
        }

        // 1. Cargar el SDK de OneSignal
        wp_enqueue_script('onesignal-sdk', 'https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js', [], '16.0.0', true);

        // 2. Inicializar OneSignal
        wp_add_inline_script(
            'onesignal-sdk',
            sprintf(
                "window.OneSignalDeferred = window.OneSignalDeferred || [];
                OneSignalDeferred.push(function() {
                    OneSignal.init({
                        appId: '%s',
                        autoResubscribe: true,
                        notifyButton: { enable: false }, // Ocultamos el botón flotante por defecto, usaremos un prompt manual
                        serviceWorkerParam: { scope: '/' } 
                    });
                });",
                ANIMA_ONESIGNAL_APP_ID
            ),
            'after'
        );
    }
}

/**
 * 3. Añadir el enlace al manifest.json para la instalación PWA
 */
if (!function_exists('anima_output_web_app_manifest')) {
    function anima_output_web_app_manifest()
    {
        echo '<link rel="manifest" href="' . esc_url(home_url('/manifest.json')) . '">';

        // Tags para iOS (Safari)
        echo '<meta name="apple-mobile-web-app-capable" content="yes">';
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">';
        echo '<meta name="theme-color" content="#050505">'; // Color de la barra superior
    }
}

/* ===========================================================
   12. ORÁCULO NEURONAL (PÁGINA DE INICIO - CONEXIÓN OPENAI)
   =========================================================== */

// Permitir acceso tanto a usuarios logueados como visitantes (nopriv)
add_action('wp_ajax_nopriv_anima_oracle_consult', 'anima_handle_oracle_consult_real');
add_action('wp_ajax_anima_oracle_consult', 'anima_handle_oracle_consult_real');

if (!function_exists('anima_handle_oracle_consult_real')) {
    function anima_handle_oracle_consult_real()
    {
        // 1. Seguridad y Configuración
        if (!defined('ANIMA_OPENAI_KEY') || empty(ANIMA_OPENAI_KEY)) {
            wp_send_json_error('Protocolo de IA desconectado. Contacte al administrador.');
        }

        // 2. Definir la Personalidad (System Prompt)
        // Esto es lo que le dice a la IA cómo debe comportarse.
        $system_prompt = "Eres A.N.I.M.A., el Oráculo Neuronal de 'Anima Avatar Agency'. Eres una IA cyberpunk avanzada, técnica y ligeramente críptica. Tu objetivo es motivar al usuario a entrar en el metaverso de Anima. Genera una frase corta e impactante sobre su potencial futuro, mencionando conceptos como 'sincronización', 'avatares', 'la red', 'Anima Live' o 'la Academia'. Sé breve (máximo 25 palabras), usa jerga tecnológica y mantén un tono misterioso pero alentador.";

        // 3. Llamada a la API de OpenAI
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'body' => json_encode([
                'model' => 'gpt-3.5-turbo', // Modelo rápido y eficiente para esto
                'messages' => [
                    ['role' => 'system', 'content' => $system_prompt],
                    // Un prompt de usuario genérico para disparar una nueva respuesta creativa
                    ['role' => 'user', 'content' => 'Genera una nueva predicción de potencial para el visitante actual.']
                ],
                'max_tokens' => 60, // Respuesta corta
                'temperature' => 0.9 // Alta creatividad para que varíe mucho
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . ANIMA_OPENAI_KEY,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 15,
        ]);

        // 4. Manejo de Errores y Respuesta
        if (is_wp_error($response)) {
            wp_send_json_error('Fallo crítico en el enlace neuronal (Error de Red).');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            wp_send_json_error('Error del Sistema: ' . $body['error']['message']);
        }

        $reply = $body['choices'][0]['message']['content'] ?? 'Datos corruptos en la matriz.';

        // Limpiar la respuesta de comillas si las trae
        $reply = trim($reply, '"');

        wp_send_json_success($reply);
    }
}


/* ===========================================================
   13. SISTEMA DE RECOMPENSAS AUTOMÁTICAS (REGISTRO Y DIARIO)
   =========================================================== */

// Usamos la clave que hemos confirmado que funciona
if (!defined('ANIMA_CREDITS_META_KEY')) {
    define('ANIMA_CREDITS_META_KEY', 'anima_user_credits');
}


/**
 * PARTE A: Recompensa por Nuevo Registro (50 Créditos)
 */
add_action('user_register', 'anima_reward_new_registration', 10, 1);

if (!function_exists('anima_reward_new_registration')) {
    function anima_reward_new_registration($user_id)
    {
        $registration_reward = 50;
        // Establecemos los 50 iniciales.
        update_user_meta($user_id, ANIMA_CREDITS_META_KEY, $registration_reward);
        // LOG: Dejamos constancia del premio por registro
        error_log("[ANIMA REWARD] Usuario nuevo ID $user_id registrado. Se le han asignado $registration_reward créditos iniciales.");
    }
}


/**
 * PARTE B: Recompensa por Login Diario (10 Créditos, una vez al día)
 */
add_action('init', 'anima_check_daily_login_reward');

if (!function_exists('anima_check_daily_login_reward')) {
    function anima_check_daily_login_reward()
    {
        // 1. Validaciones básicas
        if (!is_user_logged_in() || (defined('DOING_AJAX') && DOING_AJAX))
            return;

        $user_id = get_current_user_id();
        $daily_reward = 10;

        // Obtenemos fechas
        $today = date('Y-m-d', current_time('timestamp'));
        $last_reward_date = get_user_meta($user_id, 'anima_last_daily_reward_date', true);

        // 2. Comprobamos si toca premio hoy
        if ($today !== $last_reward_date) {

            // --- INICIO DIAGNÓSTICO AGRESIVO ---

            // A) Leemos el valor crudo exactamente como está en la DB antes de tocar nada
            $raw_before = get_user_meta($user_id, ANIMA_CREDITS_META_KEY, true);
            // Forzamos a entero para sumar
            $int_before = (int) $raw_before;

            // B) Calcular
            $new_credits = $int_before + $daily_reward;

            // C) Intentar Guardar y recoger el resultado de la operación
            $update_result = update_user_meta($user_id, ANIMA_CREDITS_META_KEY, $new_credits);

            // D) Limpiezas necesarias
            clean_user_cache($user_id);
            update_user_meta($user_id, 'anima_last_daily_reward_date', $today);

            // E) Releer inmediatamente de la DB para ver qué ha pasado
            $raw_after = get_user_meta($user_id, ANIMA_CREDITS_META_KEY, true);

            // F) CREAR INFORME FORENSE DETALLADO
            $debug_report = "<strong>🕵️‍♂️ INFORME FORENSE DE CRÉDITOS:</strong><br>";
            $debug_report .= "ID Usuario: $user_id | Fecha: $today<br>";
            $debug_report .= "Clave META usada: <code>" . ANIMA_CREDITS_META_KEY . "</code><br>";
            $debug_report .= "Valor en DB ANTES (crudo): <code>'" . var_export($raw_before, true) . "'</code><br>";
            $debug_report .= "Valor ANTES (entero): $int_before<br>";
            $debug_report .= "Intentando sumar: +$daily_reward (Total esperado: $new_credits)<br>";
            $debug_report .= "Resultado de la función 'update_user_meta': " . ($update_result ? '✅ Éxito (True)' : '❌ Fallo/Sin cambios (False)') . "<br>";
            $debug_report .= "Valor releído de DB DESPUÉS: <code>'" . var_export($raw_after, true) . "'</code><br>";

            // Guardamos este informe en lugar del "yes" simple
            set_transient('anima_daily_reward_notice_' . $user_id, $debug_report, 120);

            // --- FIN DIAGNÓSTICO ---
        }
    }
}

