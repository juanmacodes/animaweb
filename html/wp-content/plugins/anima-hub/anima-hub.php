<?php
/**
 * Plugin Name: Anima Hub
 * Description: Core del sistema: Badges, Cursos, Comunidad, Streaming e IA.
 * Version: 0.8.0 (Full AI + Nexus)
 * Author: Anima Avatar Agency
 */

if ( ! defined('ABSPATH') ) exit;

// --- CONFIGURACIÓN GLOBAL ---
// PEGA TU CLAVE AQUÍ (sk-...):
define('ANIMA_OPENAI_KEY', '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'); 


/* ============================================================
 * 1. GENERADOR IA (TEXTO + IMAGEN)
 * ============================================================ */

// Generar BIO (Texto)
add_action('wp_ajax_anima_generate_bio', 'anima_ajax_generate_bio');
function anima_ajax_generate_bio() {
    check_ajax_referer('anima_ai_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error('Debes iniciar sesión.');

    $name   = sanitize_text_field($_POST['name']);
    $style  = sanitize_text_field($_POST['style']);
    $traits = sanitize_text_field($_POST['traits']);

    $prompt = "Escribe una biografía breve y épica (máx 50 palabras) para un avatar llamado '$name'. Estilo: $style. Rasgos: $traits. Tono: Cyberpunk/Sci-Fi. En español.";

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => ['Content-Type'=>'application/json', 'Authorization'=>'Bearer '.ANIMA_OPENAI_KEY],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.7,
            'max_tokens' => 150
        ]),
        'timeout' => 20
    ]);

    if (is_wp_error($response)) wp_send_json_error('Error de conexión IA.');
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if(isset($body['error'])) wp_send_json_error($body['error']['message']);

    $content = $body['choices'][0]['message']['content'] ?? 'Error generando texto.';
    wp_send_json_success(['bio' => trim($content)]);
}

// Guardar BIO
add_action('wp_ajax_anima_save_ai_bio', 'anima_ajax_save_ai_bio');
function anima_ajax_save_ai_bio() {
    check_ajax_referer('anima_ai_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();
    update_user_meta(get_current_user_id(), 'anima_ai_bio', sanitize_textarea_field($_POST['bio_content']));
    wp_send_json_success();
}

// Generar IMAGEN (DALL-E)
add_action('wp_ajax_anima_generate_avatar_img', 'anima_ajax_generate_avatar_img');
function anima_ajax_generate_avatar_img() {
    check_ajax_referer('anima_ai_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error('Sesión expirada.');

    $desc = sanitize_text_field($_POST['desc']);
    $style = sanitize_text_field($_POST['style']); 
    
    $full_prompt = "A high quality, close-up portrait of a $style character. $desc. Cyberpunk aesthetic, unreal engine 5 render, cinematic lighting, centered composition.";

    $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
        'headers' => ['Content-Type'=>'application/json', 'Authorization'=>'Bearer '.ANIMA_OPENAI_KEY],
        'body' => json_encode([
            'model' => 'dall-e-3',
            'prompt' => $full_prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'standard'
        ]),
        'timeout' => 60
    ]);

    if (is_wp_error($response)) wp_send_json_error('Error de conexión con DALL-E.');

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if(isset($body['error'])) wp_send_json_error($body['error']['message']);

    $image_url = $body['data'][0]['url'] ?? '';
    if(!$image_url) wp_send_json_error('No se generó imagen.');

    wp_send_json_success(['url' => $image_url]);
}

// Guardar IMAGEN
add_action('wp_ajax_anima_save_generated_avatar', 'anima_ajax_save_generated_avatar');
function anima_ajax_save_generated_avatar() {
    check_ajax_referer('anima_ai_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error('Sesión expirada.');

    $image_url = esc_url_raw($_POST['image_url']);
    if(!$image_url) wp_send_json_error('URL inválida.');

    // Descargar y guardar en WP
    $tmp = download_url($image_url);
    if(is_wp_error($tmp)) wp_send_json_error('Error descargando imagen.');

    $file_array = [
        'name' => 'avatar-ai-' . time() . '.png',
        'tmp_name' => $tmp
    ];
    
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');

    $id = media_handle_sideload($file_array, 0);
    
    if(is_wp_error($id)) {
        @unlink($tmp);
        wp_send_json_error('Error guardando imagen.');
    }

    update_user_meta(get_current_user_id(), 'anima_custom_avatar', $id);
    wp_send_json_success();
}


/* ============================================================
 * 2. BADGES Y NIVEL
 * ============================================================ */
add_action('init', function(){
  register_post_type('anima_badge', [
    'label' => 'Badges',
    'labels' => ['name'=>'Badges','singular_name'=>'Badge'],
    'public' => false,
    'show_ui' => true,
    'menu_icon' => 'dashicons-awards',
    'supports' => ['title','thumbnail'],
    'show_in_rest' => true
  ]);
});

add_action('add_meta_boxes', function(){
  add_meta_box('anima_badge_opts','Configuración del Badge','anima_badge_opts_cb','anima_badge','side','default');
});

function anima_badge_opts_cb($post){
  $color  = get_post_meta($post->ID,'anima_badge_color',true) ?: '#6f65ff';
  $points = (int)get_post_meta($post->ID,'anima_badge_points',true);
  echo '<p><label>Color del borde</label><br><input type="color" name="anima_badge_color" value="'.esc_attr($color).'"></p>';
  echo '<p><label>Puntos (XP)</label><br><input type="number" name="anima_badge_points" value="'.$points.'" style="width:100%"></p>';
}

add_action('save_post_anima_badge', function($post_id){
  if (isset($_POST['anima_badge_color'])) update_post_meta($post_id,'anima_badge_color',sanitize_hex_color($_POST['anima_badge_color']));
  if (isset($_POST['anima_badge_points'])) update_post_meta($post_id,'anima_badge_points',intval($_POST['anima_badge_points']));
});

function anima_get_user_level_info($user_id){
    $badges = (array) get_user_meta($user_id, 'anima_user_badges', true);
    $total_points = 0;
    foreach($badges as $bid){
        $total_points += (int) get_post_meta($bid, 'anima_badge_points', true);
    }
    $level = 1 + floor($total_points / 100);
    return ['level' => $level, 'xp' => $total_points, 'badges_count' => count($badges)];
}



/* ============================================================
 * 4. LÓGICA DE COMPRA
 * ============================================================ */
add_action('woocommerce_order_status_completed', 'anima_process_course_purchase');
function anima_process_course_purchase($order_id){
    $order = wc_get_order($order_id);
    if (!$order) return;
    $user_id = $order->get_user_id();
    if (!$user_id) return;

    foreach($order->get_items() as $item){
        $pid = $item->get_product_id();
        $courses = get_posts([
            'post_type'=>'curso',
            'meta_key'=>'_anima_product_id',
            'meta_value'=>$pid,
            'numberposts'=>1
        ]);

        if ($courses) {
            $course = $courses[0];
            $course_id = $course->ID;

            $enrolled = (array) get_user_meta($user_id,'anima_courses',true);
            if (!in_array($course_id, $enrolled)){
                $enrolled[] = $course_id;
                update_user_meta($user_id,'anima_courses',$enrolled);
            }

            $badge_id = get_post_meta($course_id, '_anima_reward_badge', true);
            if ($badge_id){
                $user_badges = (array) get_user_meta($user_id,'anima_user_badges',true);
                if (!in_array($badge_id, $user_badges)){
                    $user_badges[] = $badge_id;
                    update_user_meta($user_id,'anima_user_badges',$user_badges);
                }
            }
        }
    }
}

/* ============================================================
 * 5. NEXUS (LIKES)
 * ============================================================ */
add_action('wp_ajax_anima_nexus_like', 'anima_handle_nexus_like');
if ( ! function_exists('anima_handle_nexus_like') ) {
    function anima_handle_nexus_like() {
        if (!is_user_logged_in()) { wp_send_json_error('Debes iniciar sesión.'); return; }
        if (!isset($_POST['post_id'])) { wp_send_json_error('Falta ID.'); return; }

        $post_id = (int) $_POST['post_id'];
        $user_id = get_current_user_id();
        $likes = (array) get_post_meta($post_id, '_anima_likes', true);
        
        if (in_array($user_id, $likes)) {
            $likes = array_diff($likes, [$user_id]);
            $action = 'removed';
        } else {
            $likes[] = $user_id;
            $action = 'added';
        }
        
        update_post_meta($post_id, '_anima_likes', array_values($likes));
        wp_send_json_success(['action' => $action, 'count' => count($likes)]);
    }
}

if ( ! function_exists('anima_get_likes_count') ) {
    function anima_get_likes_count($post_id) {
        return count((array) get_post_meta($post_id, '_anima_likes', true));
    }
}

if ( ! function_exists('anima_user_has_liked') ) {
    function anima_user_has_liked($post_id, $user_id) {
        return in_array($user_id, (array) get_post_meta($post_id, '_anima_likes', true));
    }
}