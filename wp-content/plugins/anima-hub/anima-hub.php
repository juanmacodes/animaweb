<?php
/**
 * Plugin Name: Anima Hub
 * Description: Core del sistema: Badges, Cursos, Comunidad y Streaming seguro.
 * Version: 0.4.0
 * Author: Anima Avatar Agency
 */

if ( ! defined('ABSPATH') ) exit;

/* ============================================================
 * 1. BADGES Y NIVEL
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

// Obtener nivel del usuario basado en puntos
function anima_get_user_level_info($user_id){
    $badges = (array) get_user_meta($user_id, 'anima_user_badges', true);
    $total_points = 0;
    foreach($badges as $bid){
        $total_points += (int) get_post_meta($bid, 'anima_badge_points', true);
    }
    // Fórmula simple: Nivel 1 base, cada 100 puntos sube nivel
    $level = 1 + floor($total_points / 100);
    return ['level' => $level, 'xp' => $total_points, 'badges_count' => count($badges)];
}

/* ============================================================
 * 2. VINCULACIÓN CURSO -> BADGE
 * ============================================================ */
add_action('add_meta_boxes', function(){
  add_meta_box('anima_course_opts','Configuración del Curso','anima_course_opts_cb','curso','normal','high');
});

function anima_course_opts_cb($post){
    $prod_id = get_post_meta($post->ID,'_anima_product_id',true);
    $reward  = get_post_meta($post->ID,'_anima_reward_badge',true);
    $syll    = get_post_meta($post->ID,'_anima_syllabus_json',true);
    
    // Obtener todos los badges para el select
    $badges = get_posts(['post_type'=>'anima_badge','numberposts'=>-1]);

    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:15px">
        <p>
            <label><strong>ID Producto WooCommerce</strong></label><br>
            <input type="number" name="anima_product_id" value="<?php echo esc_attr($prod_id); ?>" style="width:100%">
        </p>
        <p>
            <label><strong>Badge Recompensa (Al completar/comprar)</strong></label><br>
            <select name="anima_reward_badge" style="width:100%">
                <option value="">-- Ninguno --</option>
                <?php foreach($badges as $b): ?>
                    <option value="<?php echo $b->ID; ?>" <?php selected($reward, $b->ID); ?>>
                        <?php echo esc_html($b->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>
    <p><strong>Temario (JSON)</strong></p>
    <textarea name="anima_syllabus_json" rows="10" style="width:100%;font-family:monospace;background:#f0f0f1"><?php echo esc_textarea($syll); ?></textarea>
    <?php
}

add_action('save_post_curso', function($post_id){
    if (isset($_POST['anima_product_id'])) update_post_meta($post_id,'_anima_product_id',(int)$_POST['anima_product_id']);
    if (isset($_POST['anima_reward_badge'])) update_post_meta($post_id,'_anima_reward_badge',(int)$_POST['anima_reward_badge']);
    if (isset($_POST['anima_syllabus_json'])) update_post_meta($post_id,'_anima_syllabus_json',wp_unslash($_POST['anima_syllabus_json']));
});

/* ============================================================
 * 3. LOGICA DE COMPRA Y ASIGNACIÓN
 * ============================================================ */
add_action('woocommerce_order_status_completed', 'anima_process_course_purchase');
function anima_process_course_purchase($order_id){
    $order = wc_get_order($order_id);
    if (!$order) return;
    $user_id = $order->get_user_id();
    if (!$user_id) return;

    foreach($order->get_items() as $item){
        $pid = $item->get_product_id();
        
        // Buscar curso vinculado a este producto
        $courses = get_posts([
            'post_type'=>'curso',
            'meta_key'=>'_anima_product_id',
            'meta_value'=>$pid,
            'numberposts'=>1
        ]);

        if ($courses) {
            $course = $courses[0];
            $course_id = $course->ID;

            // 1. Matricular
            $enrolled = (array) get_user_meta($user_id,'anima_courses',true);
            if (!in_array($course_id, $enrolled)){
                $enrolled[] = $course_id;
                update_user_meta($user_id,'anima_courses',$enrolled);
            }

            // 2. Asignar Badge
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
 * 4. REDES SOCIALES Y AMIGOS
 * ============================================================ */
// Campos sociales permitidos
function anima_get_social_fields(){
    return ['instagram','twitter','linkedin','youtube','artstation','tiktok'];
}

// Guardar perfil desde frontend
add_action('admin_post_anima_save_profile', function(){
    if (!is_user_logged_in()) return;
    check_admin_referer('anima_save_profile_nonce');
    
    $uid = get_current_user_id();
    foreach(anima_get_social_fields() as $net){
        if(isset($_POST[$net])){
            update_user_meta($uid, 'anima_social_'.$net, esc_url_raw($_POST[$net]));
        }
    }
    wp_safe_redirect(wp_get_referer());
    exit;
});

// Conectar/Seguir usuario (AJAX)
add_action('wp_ajax_anima_toggle_friend', function(){
    $target = (int) $_POST['id'];
    $me = get_current_user_id();
    if (!$me || !$target || $me === $target) wp_send_json_error();

    $my_friends = (array) get_user_meta($me, 'anima_friends', true);
    
    if (in_array($target, $my_friends)){
        // Dejar de seguir
        $my_friends = array_diff($my_friends, [$target]);
        $status = 'removed';
    } else {
        // Seguir
        $my_friends[] = $target;
        $status = 'added';
    }
    
    update_user_meta($me, 'anima_friends', array_values($my_friends));
    wp_send_json_success(['status' => $status]);
});

add_action('show_user_profile', 'anima_admin_user_fields', 1);
add_action('edit_user_profile', 'anima_admin_user_fields', 1);

function anima_admin_user_fields($user){
    ?>
    <h3>Información de Agente (Anima Hub)</h3>
    <table class="form-table">
        <tr>
            <th><label>Badges Asignados</label></th>
            <td>
                <?php 
                $all_badges = get_posts(['post_type'=>'anima_badge','numberposts'=>-1]);
                $user_badges = (array) get_user_meta($user->ID, 'anima_user_badges', true);
                
                if($all_badges): 
                    echo '<div style="display:flex; gap:10px; flex-wrap:wrap;">';
                    foreach($all_badges as $b): 
                        $checked = in_array($b->ID, $user_badges) ? 'checked' : '';
                        ?>
                        <label style="background:#fff; border:1px solid #ddd; padding:5px 10px; border-radius:4px;">
                            <input type="checkbox" name="anima_user_badges[]" value="<?php echo $b->ID; ?>" <?php echo $checked; ?>>
                            <?php echo esc_html($b->post_title); ?>
                        </label>
                    <?php endforeach; 
                    echo '</div>';
                else: 
                    echo 'No hay badges creados.'; 
                endif; 
                ?>
            </td>
        </tr>
    </table>
    <?php
}

// Guardar campos extra desde el ADMIN
add_action('personal_options_update', 'anima_save_admin_user_fields');
add_action('edit_user_profile_update', 'anima_save_admin_user_fields');

function anima_save_admin_user_fields($user_id){
    if (!current_user_can('edit_user', $user_id)) return;
    
    // Guardar Badges (si se envían vacío, borrar todos)
    if (isset($_POST['anima_user_badges'])) {
        $badges = array_map('intval', $_POST['anima_user_badges']);
        update_user_meta($user_id, 'anima_user_badges', $badges);
    } else {
        // Si no viene el campo en el POST pero estamos en la pantalla correcta, borrar
        // (Pequeño truco: verificar si se envió algún otro campo nativo para saber que estamos guardando)
        if(isset($_POST['email'])) delete_user_meta($user_id, 'anima_user_badges');
    }
}

/* ============================================================
 * 5. SISTEMA DE MENSAJERÍA INTERNA (Anima Inbox)
 * ============================================================ */
add_action('init', function(){
  register_post_type('anima_message', [
    'label' => 'Mensajes',
    'public' => false, // Privado, no accesible por URL pública
    'supports' => ['title','editor','author'],
    'show_ui' => true,
    'menu_icon' => 'dashicons-email-alt'
  ]);
});

// Enviar mensaje (Handler del formulario)
add_action('admin_post_anima_send_msg', function(){
    if (!is_user_logged_in()) return;
    check_admin_referer('anima_send_msg_nonce');

    $sender_id = get_current_user_id();
    $target_id = (int) $_POST['to_user'];
    $subject   = sanitize_text_field($_POST['subject']);
    $message   = wp_kses_post($_POST['message']);

    if (!$target_id || empty($subject) || empty($message)) {
        wp_safe_redirect(add_query_arg('err','empty',wp_get_referer()));
        exit;
    }

    // Crear el mensaje como un Post privado
    $msg_id = wp_insert_post([
        'post_type'   => 'anima_message',
        'post_title'  => $subject,
        'post_content'=> $message,
        'post_status' => 'publish',
        'post_author' => $sender_id
    ]);

    if ($msg_id) {
        // Guardamos quién es el destinatario
        update_post_meta($msg_id, '_anima_to_user', $target_id);
        update_post_meta($msg_id, '_anima_read_status', 'unread');
        
        // Redirigir con éxito
        wp_safe_redirect(add_query_arg('sent','ok',wp_get_referer()));
        exit;
    }
});

// Función auxiliar: Contar mensajes no leídos
function anima_count_unread_messages($user_id) {
    $msgs = get_posts([
        'post_type' => 'anima_message',
        'meta_query' => [
            ['key' => '_anima_to_user', 'value' => $user_id],
            ['key' => '_anima_read_status', 'value' => 'unread']
        ],
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    return count($msgs);
}

/* ============================================================
 * 6. TABLÓN DE COMUNIDAD Y EVENTOS
 * ============================================================ */
add_action('init', function(){
  register_post_type('anima_feed', [
    'label' => 'Nexus',
    'public' => true,
    'has_archive' => false, // <--- CAMBIO 1: Ponemos esto en false para que no genere el archivo automático que te está molestando
    'supports' => ['title','editor','author','comments'],
    'show_ui' => true,
    'menu_icon' => 'dashicons-megaphone',
    'rewrite' => ['slug' => 'transmision'], // <--- CAMBIO 2: Cambiamos 'nexus' por 'transmision' (o 'hilo')
  ]);
});

// Manejar envío de publicación desde el frontend
add_action('admin_post_anima_save_feed_post', function(){
    if (!is_user_logged_in()) return;
    check_admin_referer('anima_feed_nonce');

    $type    = sanitize_text_field($_POST['feed_type']); // general, duda, idea, evento
    $content = wp_kses_post($_POST['feed_content']);
    $title   = sanitize_text_field($_POST['feed_title']);
    
    // Datos de evento
    $event_date = sanitize_text_field($_POST['event_date']);
    $event_link = esc_url_raw($_POST['event_link']);

    if (empty($title)) $title = wp_trim_words($content, 5, '...'); // Título automático si falta

    $post_id = wp_insert_post([
        'post_type'    => 'anima_feed',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id()
    ]);

    if ($post_id) {
        update_post_meta($post_id, '_anima_feed_type', $type);
        if ($type === 'evento') {
            update_post_meta($post_id, '_anima_event_date', $event_date);
            update_post_meta($post_id, '_anima_event_link', $event_link);
        }
        wp_safe_redirect(add_query_arg('posted','ok',wp_get_referer()));
        exit;
    }
});

// Helper para obtener icono según tipo
function anima_get_feed_type_icon($type){
    switch($type){
        case 'evento': return '📅 Evento';
        case 'duda':   return '❓ Duda';
        case 'idea':   return '💡 Idea';
        default:       return '💬 Mensaje';
    }
}

/* ============================================================
 * 8. GESTOR DE MODELOS 3D (SHOWCASE)
 * ============================================================ */
add_action('init', function(){
  register_post_type('anima_model', [
    'label' => 'Modelos 3D',
    'public' => false, // No tienen página propia, solo salen en el showcase
    'show_ui' => true,
    'supports' => ['title', 'editor'], // El editor será la descripción
    'menu_icon' => 'dashicons-media-interactive',
  ]);
});

add_action('add_meta_boxes', function(){
  add_meta_box('anima_model_opts', 'Archivos del Modelo', 'anima_model_opts_cb', 'anima_model', 'normal', 'high');
});

function anima_model_opts_cb($post){
  $glb  = get_post_meta($post->ID, '_anima_model_glb', true);
  $poster = get_post_meta($post->ID, '_anima_model_poster', true);
  $link = get_post_meta($post->ID, '_anima_model_link', true);
  ?>
  <style>.anima-field{margin-bottom:15px;}.anima-field label{display:block;font-weight:bold;margin-bottom:5px;}.anima-field input{width:100%;}</style>
  
  <div class="anima-field">
      <label>URL del archivo .GLB (Modelo 3D)</label>
      <input type="url" name="anima_model_glb" value="<?php echo esc_url($glb); ?>" placeholder="https://.../archivo.glb">
      <p class="description">Sube el archivo a Medios, copia la URL y pégala aquí.</p>
  </div>

  <div class="anima-field">
      <label>URL del Poster (Imagen de carga .webp/.jpg)</label>
      <input type="url" name="anima_model_poster" value="<?php echo esc_url($poster); ?>" placeholder="https://.../imagen.webp">
  </div>

  <div class="anima-field">
      <label>Enlace de Compra/Descarga (Botón de acción)</label>
      <input type="url" name="anima_model_link" value="<?php echo esc_url($link); ?>" placeholder="https://tuweb.com/producto/...">
      <p class="description">Si se deja vacío, el botón llevará a la tienda general.</p>
  </div>
  <?php
}

add_action('save_post_anima_model', function($post_id){
    if (isset($_POST['anima_model_glb'])) update_post_meta($post_id, '_anima_model_glb', esc_url_raw($_POST['anima_model_glb']));
    if (isset($_POST['anima_model_poster'])) update_post_meta($post_id, '_anima_model_poster', esc_url_raw($_POST['anima_model_poster']));
    if (isset($_POST['anima_model_link'])) update_post_meta($post_id, '_anima_model_link', esc_url_raw($_POST['anima_model_link']));
});

/* ============================================================
 * 9. SISTEMA DE AVISOS POP-UP (CHANGELOG)
 * ============================================================ */
add_action('init', function(){
  register_post_type('anima_notice', [
    'label' => 'Avisos Pop-up',
    'public' => false,
    'show_ui' => true,
    'supports' => ['title', 'editor', 'thumbnail'], // Thumbnail para imagen destacada
    'menu_icon' => 'dashicons-bell',
  ]);
});

// Inyectar el Pop-up en el pie de página
add_action('wp_footer', 'anima_render_popup_notice');

function anima_render_popup_notice() {
    // 1. Buscamos el aviso más reciente PUBLICADO
    $notices = get_posts([
        'post_type'      => 'anima_notice',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ]);

    if ( empty($notices) ) return; // Si no hay avisos, no hacemos nada

    $notice = $notices[0];
    $notice_id = $notice->ID;
    $image = get_the_post_thumbnail_url($notice_id, 'large');
    ?>
    
    <div id="anima-popup-overlay" class="anima-popup-overlay" style="display:none;" data-id="<?php echo esc_attr($notice_id); ?>">
        <div class="anima-popup-card">
            <button class="anima-popup-close" onclick="animaClosePopup()">✕</button>
            
            <?php if($image): ?>
                <div class="anima-popup-media">
                    <img src="<?php echo esc_url($image); ?>" alt="">
                </div>
            <?php endif; ?>

            <div class="anima-popup-content">
                <span class="anima-badge-new">✨ NOVEDAD</span>
                <h3><?php echo esc_html($notice->post_title); ?></h3>
                <div class="anima-popup-body">
                    <?php echo wp_kses_post(wpautop($notice->post_content)); ?>
                </div>
                <button class="anima-btn full-width" onclick="animaClosePopup()">Entendido</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const popup = document.getElementById('anima-popup-overlay');
        const currentID = popup.getAttribute('data-id');
        const seenID = localStorage.getItem('anima_last_notice_seen');

        // Si el ID del aviso actual es diferente al último que vio el usuario, mostramos
        if ( currentID !== seenID ) {
            setTimeout(() => {
                popup.style.display = 'flex';
                // Pequeña animación de entrada
                setTimeout(() => popup.classList.add('is-visible'), 10);
            }, 1500); // Esperamos 1.5s para que no sea agresivo al cargar
        }
    });

    function animaClosePopup() {
        const popup = document.getElementById('anima-popup-overlay');
        const currentID = popup.getAttribute('data-id');
        
        // Ocultar visualmente
        popup.classList.remove('is-visible');
        setTimeout(() => popup.style.display = 'none', 300);
        
        // Guardar en el navegador que este aviso ya se vio
        localStorage.setItem('anima_last_notice_seen', currentID);
    }
    </script>
    <?php
}

/* ============================================================
 * 10. GENERADOR IA (Anima AI Bio)
 * ============================================================ */
add_action('wp_ajax_anima_generate_bio', 'anima_ajax_generate_bio');

function anima_ajax_generate_bio() {
    // 1. Seguridad y Permisos
    check_ajax_referer('anima_ai_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error('Debes iniciar sesión.');

    // 2. Recoger datos
    $name   = sanitize_text_field($_POST['name']);
    $style  = sanitize_text_field($_POST['style']); // Cyberpunk, Fantasía, etc.
    $traits = sanitize_text_field($_POST['traits']); // Rasgos (ej: rápido, sigiloso)

    // 3. Configuración de la API (¡PON TU CLAVE AQUÍ!)
    $api_key = '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'; 

    // --- MODO SIMULACIÓN (Si no tienes API Key puesta) ---
    if ($api_key === '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') {
        sleep(2); // Simular pensar
        $fake_bio = ":: MODO SIMULACIÓN ::\n\nNombre en clave: $name.\nClase: $style.\n\nEste agente surgió de los sectores olvidados del servidor central. Conocido por ser $traits, ha sobrevivido a tres reinicios del sistema y ahora busca su lugar en el Nexus. Su código genético contiene fragmentos de datos encriptados que nadie ha logrado descifrar.";
        wp_send_json_success(['bio' => $fake_bio]);
        exit;
    }

    // 4. Llamada Real a OpenAI
    $prompt = "Escribe una biografía breve, épica y futurista (máx 50 palabras) para un avatar del metaverso llamado '$name'. Estilo: $style. Rasgos clave: $traits. Tono: Serio, tecnológico, misterioso. Responde en español.";

    $body = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Eres una IA narrativa de una agencia de avatares futurista.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 100
    ];

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ],
        'body' => json_encode($body),
        'timeout' => 15
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error('Error de conexión con la IA.');
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $content = $data['choices'][0]['message']['content'] ?? 'La IA no ha podido procesar los datos.';

    wp_send_json_success(['bio' => trim($content)]);
}

/* ============================================================
 * 11. GUARDAR BIOGRAFÍA IA EN EL PERFIL
 * ============================================================ */
add_action('wp_ajax_anima_save_ai_bio', 'anima_ajax_save_ai_bio');

function anima_ajax_save_ai_bio() {
    check_ajax_referer('anima_ai_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error('Debes iniciar sesión.');

    $user_id = get_current_user_id();
    $bio_content = sanitize_textarea_field($_POST['bio_content']);

    if (update_user_meta($user_id, 'anima_ai_bio', $bio_content)) {
        wp_send_json_success('Biografía guardada.');
    } else {
        wp_send_json_error('No se pudo guardar la biografía.');
    }
}